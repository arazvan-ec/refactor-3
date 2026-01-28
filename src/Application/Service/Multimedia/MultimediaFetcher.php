<?php

declare(strict_types=1);

namespace App\Application\Service\Multimedia;

use App\Orchestrator\Chain\Multimedia\MultimediaOrchestratorHandler;
use App\Orchestrator\Exceptions\OrchestratorTypeNotExistException;
use Ec\Editorial\Domain\Model\Body\Body;
use Ec\Editorial\Domain\Model\Body\BodyTagMembershipCard;
use Ec\Editorial\Domain\Model\Body\BodyTagPicture;
use Ec\Editorial\Domain\Model\Multimedia\Multimedia;
use Ec\Editorial\Domain\Model\Multimedia\Widget;
use Ec\Editorial\Domain\Model\NewsBase;
use Ec\Infrastructure\Client\Exceptions\InvalidBodyException;
use Ec\Multimedia\Domain\Model\Multimedia as AbstractMultimedia;
use Ec\Multimedia\Infrastructure\Client\Http\Media\QueryMultimediaClient as QueryMultimediaOpeningClient;
use Ec\Multimedia\Infrastructure\Client\Http\QueryMultimediaClient;
use GuzzleHttp\Promise\Utils;
use Http\Promise\Promise;
use Psr\Log\LoggerInterface;

/**
 * Service for fetching multimedia content.
 * Encapsulates async logic previously in EditorialOrchestrator.
 */
final class MultimediaFetcher implements MultimediaFetcherInterface
{
    private const ASYNC = true;
    private const UNWRAPPED = true;

    public function __construct(
        private readonly QueryMultimediaClient $queryMultimediaClient,
        private readonly QueryMultimediaOpeningClient $queryMultimediaOpeningClient,
        private readonly MultimediaOrchestratorHandler $multimediaOrchestratorHandler,
        private readonly MultimediaProcessorInterface $multimediaProcessor,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function fetchAsync(Multimedia $multimedia): array
    {
        $promises = [];
        $multimediaId = $this->multimediaProcessor->getMultimediaId($multimedia);

        if (null !== $multimediaId) {
            $promises[] = $this->queryMultimediaClient->findMultimediaById($multimediaId, self::ASYNC);
        }

        return $promises;
    }

    public function fetchOpening(NewsBase $editorial): array
    {
        $opening = $editorial->opening();

        if (empty($opening->multimediaId())) {
            return [];
        }

        try {
            /** @var AbstractMultimedia $multimedia */
            $multimedia = $this->queryMultimediaOpeningClient->findMultimediaById($opening->multimediaId());

            return $this->multimediaOrchestratorHandler->handler($multimedia);
        } catch (OrchestratorTypeNotExistException|InvalidBodyException $e) {
            $this->logger->warning('Failed to fetch opening multimedia', [
                'multimediaId' => $opening->multimediaId(),
                'error' => $e->getMessage(),
            ]);

            return [];
        }
    }

    public function fetchPhotosFromBodyTags(Body $body): array
    {
        $result = [];

        // Fetch photos from BodyTagPicture
        /** @var BodyTagPicture[] $bodyTagPictures */
        $bodyTagPictures = $body->bodyElementsOf(BodyTagPicture::class);
        foreach ($bodyTagPictures as $bodyTagPicture) {
            $id = $bodyTagPicture->id()->id();
            $result = $this->addPhotoToArray($id, $result);
        }

        // Fetch photos from BodyTagMembershipCard
        /** @var BodyTagMembershipCard[] $membershipCards */
        $membershipCards = $body->bodyElementsOf(BodyTagMembershipCard::class);
        foreach ($membershipCards as $membershipCard) {
            $id = $membershipCard->bodyTagPictureMembership()->id()->id();
            $result = $this->addPhotoToArray($id, $result);
        }

        return $result;
    }

    public function resolvePromises(array $promises): array
    {
        if (empty($promises)) {
            return [];
        }

        return Utils::settle($promises)
            ->then(fn (array $settled) => $this->processSettledPromises($settled))
            ->wait(self::UNWRAPPED);
    }

    /**
     * @param array<string, mixed> $result
     *
     * @return array<string, mixed>
     */
    private function addPhotoToArray(string $id, array $result): array
    {
        try {
            $photo = $this->queryMultimediaClient->findPhotoById($id);
            $result[$id] = $photo;
        } catch (\Throwable $throwable) {
            $this->logger->error('Failed to fetch photo', [
                'photoId' => $id,
                'error' => $throwable->getMessage(),
            ]);
        }

        return $result;
    }

    /**
     * @param array<string, array{state: string, value?: mixed}> $settled
     *
     * @return array<string, AbstractMultimedia>
     */
    private function processSettledPromises(array $settled): array
    {
        $result = [];

        foreach ($settled as $promise) {
            if (Promise::FULFILLED === $promise['state']) {
                /** @var AbstractMultimedia $multimedia */
                $multimedia = $promise['value'];
                $result[$multimedia->id()] = $multimedia;
            }
        }

        return $result;
    }
}
