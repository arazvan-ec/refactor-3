<?php

declare(strict_types=1);

namespace App\Application\Strategy\RelatedContent;

use App\Application\Service\Editorial\SignatureFetcherInterface;
use App\Application\Service\Multimedia\MultimediaProcessorInterface;
use Ec\Editorial\Domain\Model\Editorial;
use Ec\Editorial\Domain\Model\QueryEditorialClient;
use Ec\Multimedia\Domain\Model\Multimedia\MultimediaPhoto;
use Ec\Multimedia\Infrastructure\Client\Http\Media\QueryMultimediaClient as QueryMultimediaOpeningClient;
use Ec\Multimedia\Infrastructure\Client\Http\QueryMultimediaClient;
use Ec\Section\Domain\Model\QuerySectionClient;
use Ec\Section\Domain\Model\Section;
use GuzzleHttp\Promise\Utils;
use Http\Promise\Promise;
use Psr\Log\LoggerInterface;

/**
 * Abstract base class for related content strategies.
 * Encapsulates common logic for fetching related editorials.
 * Follows Template Method pattern - subclasses define what to fetch, base handles how.
 */
abstract class AbstractRelatedContentStrategy implements RelatedContentStrategyInterface
{
    protected const ASYNC = true;
    protected const UNWRAPPED = true;

    /** @var array<string, mixed> */
    protected array $resolveData = [
        'multimedia' => [],
        'multimediaOpening' => [],
    ];

    public function __construct(
        protected readonly QueryEditorialClient $queryEditorialClient,
        protected readonly QuerySectionClient $querySectionClient,
        protected readonly QueryMultimediaClient $queryMultimediaClient,
        protected readonly QueryMultimediaOpeningClient $queryMultimediaOpeningClient,
        protected readonly SignatureFetcherInterface $signatureFetcher,
        protected readonly MultimediaProcessorInterface $multimediaProcessor,
        protected readonly LoggerInterface $logger,
    ) {
    }

    public function getResolveData(): array
    {
        return $this->resolveData;
    }

    /**
     * Process a related editorial and return its data.
     *
     * @param string $editorialId The editorial ID to process
     * @param Section $parentSection The parent section for context
     *
     * @return array<string, mixed>|null The editorial data or null if not visible/found
     */
    protected function processRelatedEditorial(string $editorialId, Section $parentSection): ?array
    {
        try {
            /** @var Editorial $editorial */
            $editorial = $this->queryEditorialClient->findEditorialById($editorialId);

            if (!$editorial->isVisible()) {
                return null;
            }

            /** @var Section $section */
            $section = $this->querySectionClient->findSectionById($editorial->sectionId());

            $signatures = $this->signatureFetcher->fetch($editorial->signatures(), $section);

            $multimediaId = $this->processEditorialMultimedia($editorial);

            return [
                'editorial' => $editorial,
                'section' => $section,
                'signatures' => $signatures,
                'multimediaId' => $multimediaId,
            ];
        } catch (\Throwable $throwable) {
            $this->logger->error('Failed to process related editorial', [
                'editorialId' => $editorialId,
                'error' => $throwable->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Process multimedia for an editorial and update resolve data.
     *
     * @param Editorial $editorial The editorial
     *
     * @return string|null The multimedia ID
     */
    protected function processEditorialMultimedia(Editorial $editorial): ?string
    {
        if (!empty($editorial->multimedia()->id()->id())) {
            $this->addAsyncMultimedia($editorial->multimedia());

            return $editorial->multimedia()->id()->id();
        }

        $this->addMetaImage($editorial);

        return $editorial->metaImage();
    }

    /**
     * Add async multimedia fetch to resolve data.
     */
    protected function addAsyncMultimedia(\Ec\Editorial\Domain\Model\Multimedia\Multimedia $multimedia): void
    {
        $multimediaId = $this->multimediaProcessor->getMultimediaId($multimedia);

        if (null !== $multimediaId) {
            $this->resolveData['multimedia'][] = $this->queryMultimediaClient->findMultimediaById(
                $multimediaId,
                self::ASYNC
            );
        }
    }

    /**
     * Add meta image to resolve data.
     */
    protected function addMetaImage(Editorial $editorial): void
    {
        if (empty($editorial->metaImage())) {
            return;
        }

        /** @var \Ec\Editorial\Domain\Model\Multimedia\Multimedia $multimedia */
        $multimedia = $this->queryMultimediaOpeningClient->findMultimediaById($editorial->metaImage());

        if (!$multimedia instanceof MultimediaPhoto) {
            return;
        }

        $resource = $this->queryMultimediaOpeningClient->findPhotoById($multimedia->resourceId());
        $this->resolveData['multimediaOpening'][$editorial->metaImage()] = [
            'resource' => $resource,
            'opening' => $multimedia,
        ];
    }

    /**
     * Resolve all pending multimedia promises.
     *
     * @return array<string, \Ec\Multimedia\Domain\Model\Multimedia>
     */
    protected function resolveMultimediaPromises(): array
    {
        if (empty($this->resolveData['multimedia'])) {
            return [];
        }

        return Utils::settle($this->resolveData['multimedia'])
            ->then(fn (array $promises) => $this->fulfilledMultimedia($promises))
            ->wait(self::UNWRAPPED);
    }

    /**
     * Process fulfilled multimedia promises.
     *
     * @param array<string, array{state: string, value?: mixed}> $promises
     *
     * @return array<string, \Ec\Multimedia\Domain\Model\Multimedia>
     */
    protected function fulfilledMultimedia(array $promises): array
    {
        $result = [];

        foreach ($promises as $promise) {
            if (Promise::FULFILLED === $promise['state']) {
                /** @var \Ec\Multimedia\Domain\Model\Multimedia $multimedia */
                $multimedia = $promise['value'];
                $result[$multimedia->id()] = $multimedia;
            }
        }

        return $result;
    }
}
