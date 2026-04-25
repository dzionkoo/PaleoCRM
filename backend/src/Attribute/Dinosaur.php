<?php

declare(strict_types=1);

namespace PaleoCRM\Attribute;

use Attribute;

/**
 * Dinosaur Attribute - Metadata about fossil characteristics
 * 
 * Demonstrates PHP 8.0+ Attributes (formerly known as Annotations)
 * 
 * Usage:
 * #[Dinosaur(period: 'Cretaceous', velocity: 'raptor-speed')]
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD)]
final class Dinosaur
{
    public function __construct(
        public readonly string $period,
        public readonly string $velocity,
        public readonly ?string $diet = null,
    ) {}
}
