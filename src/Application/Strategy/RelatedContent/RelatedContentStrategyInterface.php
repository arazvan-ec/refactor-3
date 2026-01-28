<?php

declare(strict_types=1);

namespace App\Application\Strategy\RelatedContent;

use Ec\Editorial\Domain\Model\Editorial;
use Ec\Section\Domain\Model\Section;

/**
 * Strategy interface for fetching related content.
 * Implements Strategy Pattern for extensible related content types.
 * New content types (galleries, polls, etc.) can be added by implementing this interface.
 */
interface RelatedContentStrategyInterface
{
    /**
     * Check if this strategy supports the given type.
     *
     * @param string $type The content type identifier
     *
     * @return bool True if this strategy handles the type
     */
    public function supports(string $type): bool;

    /**
     * Fetch related content for an editorial.
     *
     * @param Editorial $editorial The source editorial
     * @param Section $section The section context
     *
     * @return array<string, array<string, mixed>> Map of content ID to content data
     */
    public function fetch(Editorial $editorial, Section $section): array;

    /**
     * Get the type identifier for this strategy.
     *
     * @return string The type identifier (e.g., 'insertedNews', 'recommendedEditorials')
     */
    public function getType(): string;

    /**
     * Get resolve data collected during fetch (for body transformation).
     *
     * @return array<string, mixed> Resolve data for transformers
     */
    public function getResolveData(): array;
}
