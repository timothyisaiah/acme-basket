<?php

declare(strict_types=1);

namespace Acme\Basket;

/**
 * Immutable Product value object
 */
final class Product
{
    public function __construct(
        private readonly string $code,
        private readonly string $name,
        private readonly float $price
    ) {
        $this->validatePrice($price);
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getPrice(): float
    {
        return $this->price;
    }

    public function equals(Product $other): bool
    {
        return $this->code === $other->code;
    }

    public function __toString(): string
    {
        return sprintf('%s (%s)', $this->name, $this->code);
    }

    private function validatePrice(float $price): void
    {
        if ($price < 0) {
            throw new \InvalidArgumentException('Product price cannot be negative');
        }
    }
} 