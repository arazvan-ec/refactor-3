<?php

declare(strict_types=1);

namespace App\Application\Service\Editorial;

use App\Infrastructure\Enum\SitesEnum;
use Ec\Editorial\Domain\Model\Body\Body;
use Ec\Editorial\Domain\Model\Body\BodyTagMembershipCard;
use Ec\Editorial\Domain\Model\Body\MembershipCardButton;
use Ec\Editorial\Domain\Model\Editorial;
use Ec\Membership\Infrastructure\Client\Http\QueryMembershipClient;
use Http\Promise\Promise;
use Psr\Http\Message\UriFactoryInterface;

/**
 * Service responsible for resolving membership links.
 * Extracted from EditorialOrchestrator to comply with Single Responsibility Principle.
 */
final class MembershipLinkResolver implements MembershipLinkResolverInterface
{
    public function __construct(
        private readonly QueryMembershipClient $queryMembershipClient,
        private readonly UriFactoryInterface $uriFactory,
    ) {
    }

    public function resolve(Editorial $editorial, string $siteId): array
    {
        $links = $this->extractLinksFromBody($editorial->body());

        if (empty($links)) {
            return [];
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

        return $this->resolvePromise($promise, $links);
    }

    public function extractLinksFromBody(Body $body): array
    {
        $linksData = [];

        /** @var BodyTagMembershipCard[] $membershipCards */
        $membershipCards = $body->bodyElementsOf(BodyTagMembershipCard::class);

        foreach ($membershipCards as $membershipCard) {
            /** @var MembershipCardButton $button */
            foreach ($membershipCard->buttons()->buttons() as $button) {
                $linksData[] = $button->urlMembership();
                $linksData[] = $button->url();
            }
        }

        return $linksData;
    }

    /**
     * @param array<int, string> $links
     *
     * @return array<string, mixed>
     */
    private function resolvePromise(Promise $promise, array $links): array
    {
        try {
            /** @var array<string, mixed> $membershipLinkResult */
            $membershipLinkResult = $promise->wait();
        } catch (\Throwable) {
            return [];
        }

        if (empty($membershipLinkResult)) {
            return [];
        }

        return array_combine($links, $membershipLinkResult);
    }
}
