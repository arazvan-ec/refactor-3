<?php

declare(strict_types=1);

namespace App\Application\Aggregator;

/**
 * Aggregator priority constants.
 *
 * Higher priority = runs first in the aggregation phase.
 * Priority determines execution order; dependencies override priority for resolution.
 *
 * ## Priority Levels Explained
 *
 * - **CRITICAL (100)**: Core data with no dependencies that others may depend on.
 *   Examples: Tags, Comments
 *
 * - **HIGH (90)**: Important data that depends only on the editorial/section.
 *   Examples: Signatures, MembershipLinks
 *
 * - **NORMAL (80)**: Standard aggregators, typically multimedia-related.
 *   Examples: Multimedia, MultimediaOpening, PhotosFromBody
 *
 * - **LOW (70)**: Aggregators that depend on other aggregators' data.
 *   Examples: InsertedNews, RecommendedEditorials (depend on signatures logic)
 *
 * ## Why These Values?
 *
 * - Gaps of 10 allow inserting new aggregators between existing ones.
 * - Higher values run first, enabling data to be ready for dependencies.
 * - Dependencies in getDependencies() override priority for the resolve phase.
 */
final class AggregatorPriority
{
    /** Core data, no dependencies, others depend on this */
    public const CRITICAL = 100;

    /** Important data, depends only on editorial/section */
    public const HIGH = 90;

    /** Standard aggregators */
    public const NORMAL = 80;

    /** Aggregators with dependencies on other aggregators */
    public const LOW = 70;

    /** Background or optional aggregators */
    public const BACKGROUND = 50;
}
