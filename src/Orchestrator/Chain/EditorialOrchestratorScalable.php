<?php

declare(strict_types=1);

namespace App\Orchestrator\Chain;

use App\Application\Aggregator\AggregationContext;
use App\Application\DataTransformer\Apps\AppsDataTransformer;
use App\Application\DataTransformer\Apps\Media\MediaDataTransformerHandler;
use App\Application\DataTransformer\Apps\MultimediaDataTransformer;
use App\Application\DataTransformer\Apps\RecommendedEditorialsDataTransformer;
use App\Application\DataTransformer\Apps\StandfirstDataTransformer;
use App\Application\DataTransformer\BodyDataTransformer;
use App\Application\Pipeline\AggregatorPipeline;
use App\Ec\Snaapi\Infrastructure\Client\Http\QueryLegacyClient;
use App\Exception\EditorialNotPublishedYetException;
use Ec\Editorial\Domain\Model\Editorial;
use Ec\Editorial\Domain\Model\Multimedia\Widget;
use Ec\Editorial\Domain\Model\NewsBase;
use Ec\Editorial\Domain\Model\QueryEditorialClient;
use Ec\Section\Domain\Model\QuerySectionClient;
use Ec\Section\Domain\Model\Section;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Scalable implementation of EditorialOrchestrator.
 *
 * Uses AggregatorPipeline for async data fetching and
 * keeps the transformation logic simple and maintainable.
 *
 * To add a new data aggregation:
 * 1. Create a class implementing AsyncAggregatorInterface
 * 2. Tag it with 'app.aggregator' in services.yaml
 * 3. No changes needed here!
 */
class EditorialOrchestratorScalable implements EditorialOrchestratorInterface
{
    public function __construct(
        private readonly QueryEditorialClient $queryEditorialClient,
        private readonly QuerySectionClient $querySectionClient,
        private readonly QueryLegacyClient $queryLegacyClient,
        private readonly AggregatorPipeline $aggregatorPipeline,
        private readonly AppsDataTransformer $detailsAppsDataTransformer,
        private readonly BodyDataTransformer $bodyDataTransformer,
        private readonly StandfirstDataTransformer $standFirstDataTransformer,
        private readonly RecommendedEditorialsDataTransformer $recommendedEditorialsDataTransformer,
        private readonly MultimediaDataTransformer $multimediaDataTransformer,
        private readonly MediaDataTransformerHandler $mediaDataTransformerHandler,
        private readonly LoggerInterface $logger,
    ) {
    }

    /**
     * @return array<string, mixed>
     *
     * @throws \Throwable
     */
    public function execute(Request $request): array
    {
        /** @var string $id */
        $id = $request->get('id');

        /** @var NewsBase $editorial */
        $editorial = $this->queryEditorialClient->findEditorialById($id);

        // Legacy fallback
        if (null === $editorial->sourceEditorial()) {
            return $this->queryLegacyClient->findEditorialById($id);
        }

        if (!$editorial->isVisible()) {
            throw new EditorialNotPublishedYetException();
        }

        /** @var Section $section */
        $section = $this->querySectionClient->findSectionById($editorial->sectionId());

        // Create aggregation context
        $context = new AggregationContext($editorial, $section);

        // Execute all aggregators (async where possible)
        $aggregatedData = $this->aggregatorPipeline->execute($context);

        // Transform to response
        return $this->buildResponse($editorial, $section, $aggregatedData, $context);
    }

    public function canOrchestrate(): string
    {
        return 'editorial-scalable';
    }

    /**
     * Build the final response from aggregated data.
     *
     * @param array<string, array<string, mixed>> $aggregatedData
     *
     * @return array<string, mixed>
     */
    private function buildResponse(
        NewsBase $editorial,
        Section $section,
        array $aggregatedData,
        AggregationContext $context
    ): array {
        // Build base editorial data
        $tags = $aggregatedData['tags']['tags'] ?? [];
        $result = $this->detailsAppsDataTransformer->write($editorial, $section, $tags)->read();

        // Add comments
        $result['countComments'] = $aggregatedData['comments']['countComments'] ?? 0;

        // Add signatures
        $result['signatures'] = $aggregatedData['signatures']['signatures'] ?? [];

        // Prepare resolve data for body transformer
        $resolveData = $this->prepareResolveData($aggregatedData, $editorial);

        // Add body
        $result['body'] = $this->bodyDataTransformer->execute($editorial->body(), $resolveData);

        // Add multimedia
        $result['multimedia'] = $this->transformMultimedia($editorial, $resolveData);

        // Add standfirst
        $result['standfirst'] = $this->standFirstDataTransformer
            ->write($editorial->standFirst())
            ->read();

        // Add recommended editorials
        $recommendedNews = $aggregatedData['recommendedEditorials']['recommendedNews'] ?? [];
        $result['recommendedEditorials'] = $this->recommendedEditorialsDataTransformer
            ->write($recommendedNews, $resolveData)
            ->read();

        return $result;
    }

    /**
     * Prepare resolve data structure for body and multimedia transformers.
     *
     * @param array<string, array<string, mixed>> $aggregatedData
     *
     * @return array<string, mixed>
     */
    private function prepareResolveData(array $aggregatedData, NewsBase $editorial): array
    {
        return [
            'multimedia' => $aggregatedData['multimedia']['multimedia'] ?? [],
            'multimediaOpening' => $aggregatedData['multimediaOpening']['multimediaOpening'] ?? [],
            'photoFromBodyTags' => $aggregatedData['photosFromBody']['photoFromBodyTags'] ?? [],
            'membershipLinkCombine' => $aggregatedData['membershipLinks']['membershipLinkCombine'] ?? [],
            'insertedNews' => $aggregatedData['insertedNews']['insertedNews'] ?? [],
            'recommendedEditorials' => $aggregatedData['recommendedEditorials']['recommendedEditorials'] ?? [],
        ];
    }

    /**
     * Transform multimedia data for response.
     *
     * @param array<string, mixed> $resolveData
     *
     * @return array<string, mixed>|null
     */
    private function transformMultimedia(NewsBase $editorial, array $resolveData): ?array
    {
        // Priority: opening > regular multimedia
        if (!empty($resolveData['multimediaOpening'])) {
            try {
                /** @var Editorial $editorial */
                return $this->mediaDataTransformerHandler->execute(
                    $resolveData['multimediaOpening'],
                    $editorial->opening()
                );
            } catch (\Throwable $e) {
                $this->logger->warning('Failed to transform opening multimedia', [
                    'error' => $e->getMessage(),
                ]);
            }
        }

        if (!empty($resolveData['multimedia'])) {
            // Don't transform widgets
            if ($editorial->multimedia() instanceof Widget) {
                return null;
            }

            return $this->multimediaDataTransformer
                ->write($resolveData['multimedia'], $editorial->multimedia())
                ->read();
        }

        return null;
    }
}
