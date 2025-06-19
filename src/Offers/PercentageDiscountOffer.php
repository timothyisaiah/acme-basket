<?php

declare(strict_types=1);

namespace Acme\Basket\Offers;

use Acme\Basket\Offer;
use Acme\Basket\Product;

/**
 * Example implementation of Offer interface that applies a percentage discount
 */
final class PercentageDiscountOffer implements Offer
{
    public function __construct(
        private readonly float $percentage
    ) {
        $this->validatePercentage($percentage);
    }

    public function apply(array $products): array
    {
        $discountedProducts = [];
        
        foreach ($products as $product) {
            $discountedPrice = $product->getPrice() * (1 - $this->percentage / 100);
            
            $discountedProducts[] = new Product(
                $product->getCode(),
                $product->getName(),
                $discountedPrice
            );
        }
        
        return $discountedProducts;
    }

    private function validatePercentage(float $percentage): void
    {
        if ($percentage < 0 || $percentage > 100) {
            throw new \InvalidArgumentException('Percentage must be between 0 and 100');
        }
    }
} 