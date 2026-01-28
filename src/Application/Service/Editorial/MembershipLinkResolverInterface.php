<?php

declare(strict_types=1);

namespace App\Application\Service\Editorial;

use Ec\Editorial\Domain\Model\Body\Body;
use Ec\Editorial\Domain\Model\Editorial;

/**
 * Interface for resolving membership links from editorial content.
 * Follows Interface Segregation Principle - focused on membership link resolution only.
 */
interface MembershipLinkResolverInterface
{
    /**
     * Resolve membership links for an editorial.
     *
     * @param Editorial $editorial The editorial containing membership links
     * @param string $siteId The site identifier for link resolution
     *
     * @return array<string, mixed> Combined membership links with their resolved URLs
     */
    public function resolve(Editorial $editorial, string $siteId): array;

    /**
     * Extract membership links from body content.
     *
     * @param Body $body The body containing membership cards
     *
     * @return array<int, string> List of membership link URLs
     */
    public function extractLinksFromBody(Body $body): array;
}
