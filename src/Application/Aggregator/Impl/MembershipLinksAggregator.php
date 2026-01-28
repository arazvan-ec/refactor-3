<?php

declare(strict_types=1);

namespace App\Application\Aggregator\Impl;

use App\Application\Aggregator\AbstractAsyncAggregator;
use App\Application\Aggregator\AggregationContext;
use App\Infrastructure\Enum\SitesEnum;
use Ec\Editorial\Domain\Model\Body\BodyTagMembershipCard;
use Ec\Editorial\Domain\Model\Body\MembershipCardButton;
use Ec\Membership\Infrastructure\Client\Http\QueryMembershipClient;
use Http\Promise\Promise;
use Psr\Http\Message\UriFactoryInterface;
use Psr\Log\LoggerInterface;

/**
 * Aggregates membership links from body elements.
 * Resolves membership URLs using the membership client.
 */
final class MembershipLinksAggregator extends AbstractAsyncAggregator
{
    public function __construct(
        private readonly QueryMembershipClient $queryMembershipClient,
        private readonly UriFactoryInterface $uriFactory,
        LoggerInterface $logger,
    ) {
        parent::__construct($logger);
    }

    public function getKey(): string
    {
        return 'membershipLinks';
    }

    public function getPriority(): int
    {
        return 90;
    }

    public function aggregate(AggregationContext $context): array
    {
        $editorial = $context->getEditorialAsEditorial();
        $siteId = $context->getSiteId();
        $body = $editorial->body();

        $links = $this->extractMembershipLinks($body);

        if (empty($links)) {
            return ['promise' => null, 'links' => []];
        }

        $uris = array_map(
            fn (string $link) => $this->uriFactory->createUri($link),
            $links
        );

        /** @var Promise $promise */
        $promise = $this->queryMembershipClient->getMembershipUrl(
            $editorial->id()->id(),
            $uris,
            SitesEnum::getEncodenameById($siteId),
            true
        );

        return [
            'promise' => $promise,
            'links' => $links,
        ];
    }

    public function resolve(array $pendingData, AggregationContext $context): array
    {
        /** @var Promise|null $promise */
        $promise = $pendingData['promise'] ?? null;
        /** @var array<string> $links */
        $links = $pendingData['links'] ?? [];

        if (null === $promise || empty($links)) {
            return ['membershipLinkCombine' => []];
        }

        try {
            /** @var array<string, mixed> $resolvedUrls */
            $resolvedUrls = $promise->wait();

            if (empty($resolvedUrls)) {
                return ['membershipLinkCombine' => []];
            }

            return ['membershipLinkCombine' => array_combine($links, $resolvedUrls)];
        } catch (\Throwable $e) {
            $this->logger->warning('Failed to resolve membership links', [
                'error' => $e->getMessage(),
            ]);

            return ['membershipLinkCombine' => []];
        }
    }

    /**
     * Extract all membership links from body elements.
     *
     * @return array<string>
     */
    private function extractMembershipLinks(\Ec\Editorial\Domain\Model\Body\Body $body): array
    {
        $links = [];

        /** @var BodyTagMembershipCard[] $membershipCards */
        $membershipCards = $body->bodyElementsOf(BodyTagMembershipCard::class);

        foreach ($membershipCards as $membershipCard) {
            /** @var MembershipCardButton $button */
            foreach ($membershipCard->buttons()->buttons() as $button) {
                $membershipUrl = $button->urlMembership();
                $regularUrl = $button->url();

                if (!empty($membershipUrl)) {
                    $links[] = $membershipUrl;
                }
                if (!empty($regularUrl)) {
                    $links[] = $regularUrl;
                }
            }
        }

        return $links;
    }
}
