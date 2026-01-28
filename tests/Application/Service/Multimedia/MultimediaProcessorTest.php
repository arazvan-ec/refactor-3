<?php

declare(strict_types=1);

namespace App\Tests\Application\Service\Multimedia;

use App\Application\Service\Multimedia\MultimediaProcessor;
use App\Infrastructure\Service\Thumbor;
use Ec\Editorial\Domain\Model\Multimedia\Multimedia;
use Ec\Editorial\Domain\Model\Multimedia\MultimediaId;
use Ec\Editorial\Domain\Model\Multimedia\PhotoExist;
use Ec\Editorial\Domain\Model\Multimedia\Video;
use Ec\Editorial\Domain\Model\Multimedia\Widget;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

#[CoversClass(MultimediaProcessor::class)]
final class MultimediaProcessorTest extends TestCase
{
    private Thumbor&MockObject $thumbor;
    private MultimediaProcessor $processor;

    protected function setUp(): void
    {
        $this->thumbor = $this->createMock(Thumbor::class);
        $this->processor = new MultimediaProcessor($this->thumbor);
    }

    public function testGetMultimediaIdReturnsIdForPhotoExist(): void
    {
        $multimediaId = $this->createMock(MultimediaId::class);

        $multimedia = $this->createMock(PhotoExist::class);
        $multimedia->method('id')->willReturn($multimediaId);

        $result = $this->processor->getMultimediaId($multimedia);

        self::assertSame($multimediaId, $result);
    }

    public function testGetMultimediaIdReturnsIdForVideoWithPhoto(): void
    {
        $multimediaId = $this->createMock(MultimediaId::class);

        $photo = $this->createMock(PhotoExist::class);
        $photo->method('id')->willReturn($multimediaId);

        $video = $this->createMock(Video::class);
        $video->method('photo')->willReturn($photo);

        $result = $this->processor->getMultimediaId($video);

        self::assertSame($multimediaId, $result);
    }

    public function testGetMultimediaIdReturnsIdForWidgetWithPhoto(): void
    {
        $multimediaId = $this->createMock(MultimediaId::class);

        $photo = $this->createMock(PhotoExist::class);
        $photo->method('id')->willReturn($multimediaId);

        $widget = $this->createMock(Widget::class);
        $widget->method('photo')->willReturn($photo);

        $result = $this->processor->getMultimediaId($widget);

        self::assertSame($multimediaId, $result);
    }

    public function testGetMultimediaIdReturnsNullForNonPhotoMultimedia(): void
    {
        $multimedia = $this->createMock(Multimedia::class);

        $result = $this->processor->getMultimediaId($multimedia);

        self::assertNull($result);
    }

    public function testGetSizesReturnsDefaultSizes(): void
    {
        $sizes = $this->processor->getSizes();

        self::assertArrayHasKey('202w', $sizes);
        self::assertArrayHasKey('144w', $sizes);
        self::assertArrayHasKey('128w', $sizes);

        self::assertSame('202', $sizes['202w']['width']);
        self::assertSame('152', $sizes['202w']['height']);
    }

    public function testCustomSizesCanBeInjected(): void
    {
        $customSizes = [
            '100w' => ['width' => '100', 'height' => '75'],
        ];

        $processor = new MultimediaProcessor($this->thumbor, $customSizes);
        $sizes = $processor->getSizes();

        self::assertSame($customSizes, $sizes);
    }
}
