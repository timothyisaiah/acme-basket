<?php

declare(strict_types=1);

namespace Acme\Basket\DeliveryRules;

use Acme\Basket\DeliveryRule;
use Acme\Basket\Product;

/**
 * Tiered Delivery Rule
 * 
 * Calculates delivery cost based on tiered pricing:
 * - < $50: $4.95
 * - < $90: $2.95
 * - >= $90: Free
 */
final class TieredDeliveryRule implements DeliveryRule
{
    private const TIER_1_THRESHOLD = 50.0;
    private const TIER_1_COST = 4.95;
    private const TIER_2_THRESHOLD = 90.0;
    private const TIER_2_COST = 2.95;
    private const TIER_3_COST = 0.0; // Free

    public function calculate(array $products): float
    {
        $totalValue = $this->calculateTotalValue($products);
        
        return $this->getDeliveryCostForTotal($totalValue);
    }

    /**
     * Calculate the total value of all products
     *
     * @param array<Product> $products
     */
    private function calculateTotalValue(array $products): float
    {
        return array_reduce(
            $products,
            fn(float $total, Product $product) => $total + $product->getPrice(),
            0.0
        );
    }

    /**
     * Get delivery cost based on total value
     */
    private function getDeliveryCostForTotal(float $totalValue): float
    {
        if ($totalValue >= self::TIER_2_THRESHOLD) {
            return self::TIER_3_COST; // Free delivery
        }
        
        if ($totalValue >= self::TIER_1_THRESHOLD) {
            return self::TIER_2_COST; // $2.95
        }
        
        return self::TIER_1_COST; // $4.95
    }
} 