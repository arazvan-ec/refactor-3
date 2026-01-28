<?php

declare(strict_types=1);

namespace App\Application\Aggregator\Trait;

use App\Application\DataTransformer\Apps\JournalistsDataTransformer;
use Ec\Editorial\Domain\Model\Editorial;
use Ec\Editorial\Domain\Model\Signature;
use Ec\Journalist\Domain\Model\JournalistFactory;
use Ec\Journalist\Domain\Model\QueryJournalistClient;
use Ec\Section\Domain\Model\Section;
use Psr\Log\LoggerInterface;

/**
 * Shared logic for fetching and transforming signatures.
 *
 * Used by aggregators that need to fetch signatures for related editorials:
 * - InsertedNewsAggregator
 * - RecommendedEditorialsAggregator
 *
 * This trait eliminates code duplication and ensures consistent
 * signature handling across aggregators.
 */
trait SignatureFetcherTrait
{
    abstract protected function getQueryJournalistClient(): QueryJournalistClient;
    abstract protected function getJournalistFactory(): JournalistFactory;
    abstract protected function getJournalistsDataTransformer(): JournalistsDataTransformer;
    abstract protected function getLogger(): LoggerInterface;
    abstract protected function getTraitLogContext(): string;

    /**
     * Fetch all signatures for an editorial.
     *
     * @return array<array<string, mixed>>
     */
    protected function fetchSignaturesForEditorial(Editorial $editorial, Section $section): array
    {
        $result = [];

        /** @var Signature $signature */
        foreach ($editorial->signatures()->getArrayCopy() as $signature) {
            $transformed = $this->fetchSingleSignature($signature->id()->id(), $section);
            if (!empty($transformed)) {
                $result[] = $transformed;
            }
        }

        return $result;
    }

    /**
     * Fetch and transform a single signature by alias ID.
     *
     * @return array<string, mixed>
     */
    protected function fetchSingleSignature(string $aliasId, Section $section, bool $hasTwitter = false): array
    {
        try {
            $aliasIdModel = $this->getJournalistFactory()->buildAliasId($aliasId);
            $journalist = $this->getQueryJournalistClient()->findJournalistByAliasId($aliasIdModel);

            return $this->getJournalistsDataTransformer()
                ->write($aliasId, $journalist, $section, $hasTwitter)
                ->read();
        } catch (\Throwable $e) {
            $this->getLogger()->warning('Failed to fetch signature', [
                'context' => $this->getTraitLogContext(),
                'aliasId' => $aliasId,
                'error' => $e->getMessage(),
            ]);

            return [];
        }
    }
}
