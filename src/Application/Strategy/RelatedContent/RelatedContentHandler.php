<?php

declare(strict_types=1);

namespace App\Application\Strategy\RelatedContent;

use Ec\Editorial\Domain\Model\Editorial;
use Ec\Section\Domain\Model\Section;

/**
 * Handler that coordinates related content strategies.
 * Implements Open/Closed Principle - new content types are added via new strategies.
 */
final class RelatedContentHandler
{
    /** @var RelatedContentStrategyInterface[] */
    private array $strategies = [];

    /**
     * @param iterable<RelatedContentStrategyInterface> $strategies
     */
    public function __construct(iterable $strategies = [])
    {
        foreach ($strategies as $strategy) {
            $this->addStrategy($strategy);
        }
    }

    /**
     * Add a strategy to the handler.
     */
    public function addStrategy(RelatedContentStrategyInterface $strategy): self
    {
        $this->strategies[$strategy->getType()] = $strategy;

        return $this;
    }

    /**
     * Fetch all related content for an editorial.
     *
     * @param Editorial $editorial The source editorial
     * @param Section $section The section context
     *
     * @return array<string, array<string, mixed>> Map of type to content data
     */
    public function fetchAll(Editorial $editorial, Section $section): array
    {
        $result = [];

        foreach ($this->strategies as $type => $strategy) {
            $result[$type] = $strategy->fetch($editorial, $section);
        }

        return $result;
    }

    /**
     * Fetch content for a specific type.
     *
     * @param string $type The content type
     * @param Editorial $editorial The source editorial
     * @param Section $section The section context
     *
     * @return array<string, mixed> Content data
     */
    public function fetchByType(string $type, Editorial $editorial, Section $section): array
    {
        if (!isset($this->strategies[$type])) {
            return [];
        }

        return $this->strategies[$type]->fetch($editorial, $section);
    }

    /**
     * Get combined resolve data from all strategies.
     *
     * @return array<string, mixed> Combined resolve data
     */
    public function getCombinedResolveData(): array
    {
        $resolveData = [
            'multimedia' => [],
            'multimediaOpening' => [],
            'insertedNews' => [],
            'recommendedEditorials' => [],
        ];

        foreach ($this->strategies as $type => $strategy) {
            $strategyResolveData = $strategy->getResolveData();

            // Merge multimedia arrays
            if (!empty($strategyResolveData['multimedia'])) {
                $resolveData['multimedia'] = array_merge(
                    $resolveData['multimedia'],
                    $strategyResolveData['multimedia']
                );
            }

            // Merge multimedia opening arrays
            if (!empty($strategyResolveData['multimediaOpening'])) {
                $resolveData['multimediaOpening'] = array_merge(
                    $resolveData['multimediaOpening'],
                    $strategyResolveData['multimediaOpening']
                );
            }
        }

        return $resolveData;
    }

    /**
     * Get a specific strategy by type.
     *
     * @param string $type The strategy type
     *
     * @return RelatedContentStrategyInterface|null The strategy or null
     */
    public function getStrategy(string $type): ?RelatedContentStrategyInterface
    {
        return $this->strategies[$type] ?? null;
    }

    /**
     * Check if a strategy exists for a type.
     *
     * @param string $type The content type
     *
     * @return bool True if strategy exists
     */
    public function hasStrategy(string $type): bool
    {
        return isset($this->strategies[$type]);
    }
}
