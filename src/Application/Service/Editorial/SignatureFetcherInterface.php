<?php

declare(strict_types=1);

namespace App\Application\Service\Editorial;

use Ec\Editorial\Domain\Model\Signatures;
use Ec\Section\Domain\Model\Section;

/**
 * Interface for fetching journalist signatures.
 * Follows Interface Segregation Principle - focused on signature retrieval only.
 */
interface SignatureFetcherInterface
{
    /**
     * Fetch and transform signatures for an editorial.
     *
     * @param Signatures $signatures The signatures collection from the editorial
     * @param Section $section The section context for URL generation
     * @param bool $hasTwitter Whether to include Twitter information
     *
     * @return array<int, array<string, mixed>> Transformed signature data
     */
    public function fetch(Signatures $signatures, Section $section, bool $hasTwitter = false): array;

    /**
     * Fetch a single signature by alias ID.
     *
     * @param string $aliasId The journalist alias ID
     * @param Section $section The section context
     * @param bool $hasTwitter Whether to include Twitter information
     *
     * @return array<string, mixed> Transformed signature data or empty array if not found
     */
    public function fetchByAliasId(string $aliasId, Section $section, bool $hasTwitter = false): array;
}
