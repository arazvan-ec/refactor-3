<?php

declare(strict_types=1);

namespace App\Application\Strategy\RelatedContent;

use Ec\Editorial\Domain\Model\Body\BodyTagInsertedNews;
use Ec\Editorial\Domain\Model\Editorial;
use Ec\Section\Domain\Model\Section;

/**
 * Strategy for fetching inserted news from editorial body.
 * Implements Strategy Pattern for Open/Closed Principle compliance.
 */
final class InsertedNewsStrategy extends AbstractRelatedContentStrategy
{
    private const TYPE = 'insertedNews';

    public function supports(string $type): bool
    {
        return self::TYPE === $type;
    }

    public function getType(): string
    {
        return self::TYPE;
    }

    public function fetch(Editorial $editorial, Section $section): array
    {
        $result = [];

        /** @var BodyTagInsertedNews[] $insertedNews */
        $insertedNews = $editorial->body()->bodyElementsOf(BodyTagInsertedNews::class);

        foreach ($insertedNews as $insertedNew) {
            $editorialId = $insertedNew->editorialId()->id();
            $data = $this->processRelatedEditorial($editorialId, $section);

            if (null !== $data) {
                $result[$editorialId] = $data;
            }
        }

        // Resolve multimedia promises after all editorials processed
        $this->resolveData['multimedia'] = $this->resolveMultimediaPromises();

        return $result;
    }
}
