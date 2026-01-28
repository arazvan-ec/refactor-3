<?php

declare(strict_types=1);

namespace App\Application\Builder;

use App\Application\DataTransformer\Apps\AppsDataTransformer;
use App\Application\DataTransformer\Apps\RecommendedEditorialsDataTransformer;
use App\Application\DataTransformer\Apps\StandfirstDataTransformer;
use App\Application\DataTransformer\BodyDataTransformer;
use Ec\Editorial\Domain\Model\Body\Body;
use Ec\Editorial\Domain\Model\Editorial;
use Ec\Editorial\Domain\Model\NewsBase;
use Ec\Editorial\Domain\Model\StandFirst;
use Ec\Section\Domain\Model\Section;
use Ec\Tag\Domain\Model\Tag;

/**
 * Builder for constructing editorial response.
 * Implements Builder Pattern for clean, step-by-step response construction.
 */
final class EditorialResponseBuilder
{
    /** @var array<string, mixed> */
    private array $response = [];

    /** @var array<string, mixed> */
    private array $resolveData = [];

    public function __construct(
        private readonly AppsDataTransformer $detailsAppsDataTransformer,
        private readonly BodyDataTransformer $bodyDataTransformer,
        private readonly StandfirstDataTransformer $standfirstDataTransformer,
        private readonly RecommendedEditorialsDataTransformer $recommendedEditorialsDataTransformer,
    ) {
    }

    /**
     * Start building a new response.
     */
    public function create(): self
    {
        $this->response = [];
        $this->resolveData = [];

        return $this;
    }

    /**
     * Add base editorial data.
     *
     * @param Editorial $editorial The editorial
     * @param Section $section The section
     * @param Tag[] $tags The tags
     */
    public function withEditorialData(Editorial $editorial, Section $section, array $tags): self
    {
        $this->response = $this->detailsAppsDataTransformer
            ->write($editorial, $section, $tags)
            ->read();

        return $this;
    }

    /**
     * Add comment count.
     */
    public function withCommentCount(int $count): self
    {
        $this->response['countComments'] = $count;

        return $this;
    }

    /**
     * Add signatures.
     *
     * @param array<int, array<string, mixed>> $signatures
     */
    public function withSignatures(array $signatures): self
    {
        $this->response['signatures'] = $signatures;

        return $this;
    }

    /**
     * Set resolve data for body transformation.
     *
     * @param array<string, mixed> $resolveData
     */
    public function withResolveData(array $resolveData): self
    {
        $this->resolveData = $resolveData;

        return $this;
    }

    /**
     * Add body content.
     */
    public function withBody(Body $body): self
    {
        $this->response['body'] = $this->bodyDataTransformer->execute(
            $body,
            $this->resolveData
        );

        return $this;
    }

    /**
     * Add multimedia.
     *
     * @param array<string, mixed>|null $multimedia
     */
    public function withMultimedia(?array $multimedia): self
    {
        $this->response['multimedia'] = $multimedia;

        return $this;
    }

    /**
     * Add standfirst.
     */
    public function withStandfirst(StandFirst $standfirst): self
    {
        $this->response['standfirst'] = $this->standfirstDataTransformer
            ->write($standfirst)
            ->read();

        return $this;
    }

    /**
     * Add recommended editorials.
     *
     * @param Editorial[] $editorials
     * @param array<string, array<string, mixed>> $resolveData
     */
    public function withRecommendedEditorials(array $editorials, array $resolveData): self
    {
        $this->response['recommendedEditorials'] = $this->recommendedEditorialsDataTransformer
            ->write($editorials, $resolveData)
            ->read();

        return $this;
    }

    /**
     * Add inserted news data to resolve data.
     *
     * @param array<string, array<string, mixed>> $insertedNews
     */
    public function addInsertedNewsToResolveData(array $insertedNews): self
    {
        $this->resolveData['insertedNews'] = $insertedNews;

        return $this;
    }

    /**
     * Add recommended editorials data to resolve data.
     *
     * @param array<string, array<string, mixed>> $recommendedEditorials
     */
    public function addRecommendedEditorialsToResolveData(array $recommendedEditorials): self
    {
        $this->resolveData['recommendedEditorials'] = $recommendedEditorials;

        return $this;
    }

    /**
     * Add membership links to resolve data.
     *
     * @param array<string, mixed> $membershipLinks
     */
    public function addMembershipLinksToResolveData(array $membershipLinks): self
    {
        $this->resolveData['membershipLinkCombine'] = $membershipLinks;

        return $this;
    }

    /**
     * Add photo data to resolve data.
     *
     * @param array<string, mixed> $photos
     */
    public function addPhotosToResolveData(array $photos): self
    {
        $this->resolveData['photoFromBodyTags'] = $photos;

        return $this;
    }

    /**
     * Add multimedia data to resolve data.
     *
     * @param array<string, mixed> $multimedia
     */
    public function addMultimediaToResolveData(array $multimedia): self
    {
        $this->resolveData['multimedia'] = $multimedia;

        return $this;
    }

    /**
     * Add multimedia opening data to resolve data.
     *
     * @param array<string, mixed> $multimediaOpening
     */
    public function addMultimediaOpeningToResolveData(array $multimediaOpening): self
    {
        $this->resolveData['multimediaOpening'] = $multimediaOpening;

        return $this;
    }

    /**
     * Build and return the final response.
     *
     * @return array<string, mixed>
     */
    public function build(): array
    {
        return $this->response;
    }

    /**
     * Get current resolve data.
     *
     * @return array<string, mixed>
     */
    public function getResolveData(): array
    {
        return $this->resolveData;
    }
}
