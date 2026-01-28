<?php

declare(strict_types=1);

namespace App\Orchestrator\Chain;

use App\Application\Builder\EditorialResponseBuilder;
use App\Application\DataTransformer\Apps\Media\MediaDataTransformerHandler;
use App\Application\DataTransformer\Apps\MultimediaDataTransformer;
use App\Application\Service\Editorial\MembershipLinkResolverInterface;
use App\Application\Service\Editorial\SignatureFetcherInterface;
use App\Application\Service\Multimedia\MultimediaFetcherInterface;
use App\Application\Strategy\RelatedContent\RecommendedEditorialsStrategy;
use App\Application\Strategy\RelatedContent\RelatedContentHandler;
use App\Ec\Snaapi\Infrastructure\Client\Http\QueryLegacyClient;
use App\Exception\EditorialNotPublishedYetException;
use App\Infrastructure\Trait\UrlGeneratorTrait;
use Ec\Editorial\Domain\Model\Editorial;
use Ec\Editorial\Domain\Model\Multimedia\Widget;
use Ec\Editorial\Domain\Model\NewsBase;
use Ec\Editorial\Domain\Model\QueryEditorialClient;
use Ec\Editorial\Exceptions\MultimediaDataTransformerNotFoundException;
use Ec\Section\Domain\Model\QuerySectionClient;
use Ec\Section\Domain\Model\Section;
use Ec\Tag\Domain\Model\QueryTagClient;
use Ec\Tag\Domain\Model\Tag;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Refactored Editorial Orchestrator following SOLID principles.
 *
 * Changes from original:
 * - Single Responsibility: Delegates to specialized services
 * - Open/Closed: New content types added via RelatedContentHandler strategies
 * - Dependency Inversion: Depends on abstractions (interfaces)
 * - Clean Code: Small methods, clear naming, no code duplication
 *
 * @see \App\Orchestrator\Chain\EditorialOrchestrator Original implementation
 */
final class EditorialOrchestratorRefactored implements EditorialOrchestratorInterface
{
    use UrlGeneratorTrait;

    private const TWITTER_EDITORIAL_TYPES = ['blog'];

    public function __construct(
        private readonly QueryEditorialClient $queryEditorialClient,
        private readonly QuerySectionClient $querySectionClient,
        private readonly QueryTagClient $queryTagClient,
        private readonly QueryLegacyClient $queryLegacyClient,
        private readonly SignatureFetcherInterface $signatureFetcher,
        private readonly MembershipLinkResolverInterface $membershipLinkResolver,
        private readonly MultimediaFetcherInterface $multimediaFetcher,
        private readonly RelatedContentHandler $relatedContentHandler,
        private readonly EditorialResponseBuilder $responseBuilder,
        private readonly MultimediaDataTransformer $multimediaDataTransformer,
        private readonly MediaDataTransformerHandler $mediaDataTransformerHandler,
        private readonly LoggerInterface $logger,
        string $extension,
    ) {
        $this->setExtension($extension);
    }

    /**
     * @return array<string, mixed>
     *
     * @throws EditorialNotPublishedYetException
     * @throws MultimediaDataTransformerNotFoundException
     * @throws \Throwable
     */
    public function execute(Request $request): array
    {
        $editorial = $this->fetchEditorial($request);

        if (null === $editorial->sourceEditorial()) {
            return $this->queryLegacyClient->findEditorialById((string) $request->get('id'));
        }

        $this->validateEditorialVisibility($editorial);

        $section = $this->fetchSection($editorial);
        $tags = $this->fetchTags($editorial);

        // Fetch related content using Strategy pattern
        $relatedContent = $this->relatedContentHandler->fetchAll($editorial, $section);

        // Build resolve data for body transformation
        $resolveData = $this->buildResolveData($editorial, $section, $relatedContent);

        // Build response
        return $this->buildResponse($editorial, $section, $tags, $resolveData, $relatedContent);
    }

    public function canOrchestrate(): string
    {
        return 'editorial';
    }

    /**
     * @throws \Throwable
     */
    private function fetchEditorial(Request $request): NewsBase
    {
        /** @var string $id */
        $id = $request->get('id');

        /** @var NewsBase $editorial */
        return $this->queryEditorialClient->findEditorialById($id);
    }

    /**
     * @throws EditorialNotPublishedYetException
     */
    private function validateEditorialVisibility(NewsBase $editorial): void
    {
        if (!$editorial->isVisible()) {
            throw new EditorialNotPublishedYetException();
        }
    }

    private function fetchSection(NewsBase $editorial): Section
    {
        /** @var Section $section */
        return $this->querySectionClient->findSectionById($editorial->sectionId());
    }

    /**
     * @return Tag[]
     */
    private function fetchTags(NewsBase $editorial): array
    {
        $tags = [];

        foreach ($editorial->tags()->getArrayCopy() as $tag) {
            try {
                $tags[] = $this->queryTagClient->findTagById($tag->id());
            } catch (\Throwable) {
                continue;
            }
        }

        return $tags;
    }

    /**
     * @param array<string, array<string, mixed>> $relatedContent
     *
     * @return array<string, mixed>
     */
    private function buildResolveData(Editorial $editorial, Section $section, array $relatedContent): array
    {
        $resolveData = [
            'multimedia' => [],
            'multimediaOpening' => [],
            'insertedNews' => $relatedContent['insertedNews'] ?? [],
            'recommendedEditorials' => $relatedContent['recommendedEditorials'] ?? [],
        ];

        // Fetch opening multimedia
        $resolveData['multimediaOpening'] = $this->multimediaFetcher->fetchOpening($editorial);

        // Fetch main multimedia
        $multimediaPromises = $this->multimediaFetcher->fetchAsync($editorial->multimedia());
        if (!empty($multimediaPromises) && !($editorial->multimedia() instanceof Widget)) {
            $resolveData['multimedia'] = $this->multimediaFetcher->resolvePromises($multimediaPromises);
        }

        // Fetch photos from body tags
        $resolveData['photoFromBodyTags'] = $this->multimediaFetcher->fetchPhotosFromBodyTags($editorial->body());

        // Resolve membership links
        $resolveData['membershipLinkCombine'] = $this->membershipLinkResolver->resolve($editorial, $section->siteId());

        // Merge multimedia from related content strategies
        $strategyResolveData = $this->relatedContentHandler->getCombinedResolveData();
        if (!empty($strategyResolveData['multimedia'])) {
            $resolveData['multimedia'] = array_merge(
                $resolveData['multimedia'],
                $strategyResolveData['multimedia']
            );
        }

        return $resolveData;
    }

    /**
     * @param Tag[] $tags
     * @param array<string, mixed> $resolveData
     * @param array<string, array<string, mixed>> $relatedContent
     *
     * @return array<string, mixed>
     *
     * @throws MultimediaDataTransformerNotFoundException
     */
    private function buildResponse(
        Editorial $editorial,
        Section $section,
        array $tags,
        array $resolveData,
        array $relatedContent
    ): array {
        $hasTwitter = \in_array($editorial->editorialType(), self::TWITTER_EDITORIAL_TYPES, true);

        return $this->responseBuilder
            ->create()
            ->withEditorialData($editorial, $section, $tags)
            ->withCommentCount($this->fetchCommentCount((string) $editorial->id()->id()))
            ->withSignatures($this->signatureFetcher->fetch($editorial->signatures(), $section, $hasTwitter))
            ->withResolveData($resolveData)
            ->withBody($editorial->body())
            ->withMultimedia($this->transformMultimedia($editorial, $resolveData))
            ->withStandfirst($editorial->standFirst())
            ->withRecommendedEditorials(
                $this->getRecommendedEditorials(),
                $relatedContent['recommendedEditorials'] ?? []
            )
            ->build();
    }

    private function fetchCommentCount(string $editorialId): int
    {
        /** @var array{options: array{totalrecords?: int}} $comments */
        $comments = $this->queryLegacyClient->findCommentsByEditorialId($editorialId);

        return $comments['options']['totalrecords'] ?? 0;
    }

    /**
     * @param array<string, array<string, mixed>> $resolveData
     *
     * @return array<string, mixed>|null
     *
     * @throws MultimediaDataTransformerNotFoundException
     */
    private function transformMultimedia(Editorial $editorial, array $resolveData): ?array
    {
        /** @var NewsBase $editorial */
        if (!empty($resolveData['multimediaOpening'])) {
            return $this->mediaDataTransformerHandler->execute(
                $resolveData['multimediaOpening'],
                $editorial->opening()
            );
        }

        if (!empty($resolveData['multimedia'])) {
            return $this->multimediaDataTransformer
                ->write($resolveData['multimedia'], $editorial->multimedia())
                ->read();
        }

        return null;
    }

    /**
     * @return Editorial[]
     */
    private function getRecommendedEditorials(): array
    {
        $strategy = $this->relatedContentHandler->getStrategy('recommendedEditorials');

        if ($strategy instanceof RecommendedEditorialsStrategy) {
            return $strategy->getFetchedEditorials();
        }

        return [];
    }
}
