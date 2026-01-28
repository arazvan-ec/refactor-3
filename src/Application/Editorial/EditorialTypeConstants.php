<?php

declare(strict_types=1);

namespace App\Application\Editorial;

use Ec\Editorial\Domain\Model\EditorialBlog;

/**
 * Constants for editorial type classifications.
 *
 * Centralizes editorial type constants to avoid duplication
 * across aggregators and orchestrators.
 */
final class EditorialTypeConstants
{
    /**
     * Editorial types that include Twitter information in signatures.
     *
     * For these types, the JournalistsDataTransformer will include
     * the journalist's Twitter handle in the signature output.
     *
     * @var array<string>
     */
    public const TWITTER_ENABLED_TYPES = [
        EditorialBlog::EDITORIAL_TYPE,
    ];

    /**
     * Check if an editorial type should include Twitter information.
     */
    public static function shouldIncludeTwitter(string $editorialType): bool
    {
        return \in_array($editorialType, self::TWITTER_ENABLED_TYPES, true);
    }
}
