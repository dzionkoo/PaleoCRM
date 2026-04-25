<?php

declare(strict_types=1);

namespace PaleoCRM\Attribute;

use Attribute;

/**
 * ExcavationSite Attribute - Mark excavation site information
 * 
 * Usage:
 * #[ExcavationSite(location: 'Paleocene Valley', difficulty: 'expert')]
 */
#[Attribute(Attribute::TARGET_CLASS)]
final class ExcavationSite
{
    public function __construct(
        public readonly string $location,
        public readonly string $difficulty = 'intermediate',
    ) {}
}
