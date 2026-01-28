<?php

declare(strict_types=1);

namespace App\Application\Service\Multimedia;

use App\Infrastructure\Service\Thumbor;
use Ec\Editorial\Domain\Model\Multimedia\Multimedia;
use Ec\Editorial\Domain\Model\Multimedia\MultimediaId;
use Ec\Editorial\Domain\Model\Multimedia\PhotoExist;
use Ec\Editorial\Domain\Model\Multimedia\Video;
use Ec\Editorial\Domain\Model\Multimedia\Widget;
use Ec\Multimedia\Domain\Model\ClippingTypes;
use Ec\Multimedia\Domain\Model\Multimedia as MultimediaModel;

/**
 * Service for processing multimedia content.
 * Replaces MultimediaTrait with proper dependency injection, following DIP.
 */
final class MultimediaProcessor implements MultimediaProcessorInterface
{
    private const DEFAULT_SIZES = [
        '202w' => [
            'width' => '202',
            'height' => '152',
        ],
        '144w' => [
            'width' => '144',
            'height' => '108',
        ],
        '128w' => [
            'width' => '128',
            'height' => '96',
        ],
    ];

    /**
     * @param array<string, array<string, string>> $sizes
     */
    public function __construct(
        private readonly Thumbor $thumbor,
        private readonly array $sizes = self::DEFAULT_SIZES,
    ) {
    }

    public function getMultimediaId(Multimedia $multimedia): ?MultimediaId
    {
        if ($multimedia instanceof PhotoExist) {
            return $multimedia->id();
        }

        if (($multimedia instanceof Video || $multimedia instanceof Widget)
            && ($multimedia->photo() instanceof PhotoExist)
        ) {
            return $multimedia->photo()->id();
        }

        return null;
    }

    public function getShotsLandscape(MultimediaModel $multimedia): array
    {
        $clippings = $multimedia->clippings();
        $clipping = $clippings->clippingByType(ClippingTypes::SIZE_ARTICLE_4_3);

        return $this->generateShots(
            $multimedia->file(),
            $clipping->topLeftX(),
            $clipping->topLeftY(),
            $clipping->bottomRightX(),
            $clipping->bottomRightY()
        );
    }

    public function getShotsLandscapeFromMedia(array $multimediaOpening): array
    {
        $clippings = $multimediaOpening['opening']->clippings();
        $clipping = $clippings->clippingByType(ClippingTypes::SIZE_ARTICLE_4_3);

        return $this->generateShots(
            $multimediaOpening['resource']->file(),
            $clipping->topLeftX(),
            $clipping->topLeftY(),
            $clipping->bottomRightX(),
            $clipping->bottomRightY()
        );
    }

    public function getSizes(): array
    {
        return $this->sizes;
    }

    /**
     * @return array<string, string>
     */
    private function generateShots(
        string $file,
        int $topLeftX,
        int $topLeftY,
        int $bottomRightX,
        int $bottomRightY
    ): array {
        $shots = [];

        foreach ($this->sizes as $type => $size) {
            $shots[$type] = $this->thumbor->retriveCropBodyTagPicture(
                $file,
                $size['width'],
                $size['height'],
                $topLeftX,
                $topLeftY,
                $bottomRightX,
                $bottomRightY
            );
        }

        return $shots;
    }
}
