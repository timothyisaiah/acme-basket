<?php

declare(strict_types=1);

namespace Acme\Basket\DeliveryRules;

use Acme\Basket\DeliveryRule;
use Acme\Basket\Product;

/**
 * Example implementation of DeliveryRule that provides free delivery above a threshold
 */
final class ThresholdDeliveryRule implements DeliveryRule
{
    public function __construct(
        private readonly float $threshold,
        private readonly float $deliveryCost
    ) {
        $this->validateThreshold($threshold);
        $this->validateDeliveryCost($deliveryCost);
    }

    public function calculate(array $products): float
    {
        $totalValue = $this->calculateTotalValue($products);
        
        return $totalValue >= $this->threshold ? 0.0 : $this->deliveryCost;
    }

    private function calculateTotalValue(array $products): float
    {
        return array_reduce(
            $products,
            fn(float $total, Product $product) => $total + $product->getPrice(),
            0.0
        );
    }

    private function validateThreshold(float $threshold): void
    {
        if ($threshold < 0) {
            throw new \InvalidArgumentException('Threshold cannot be negative');
        }
    }

    private function validateDeliveryCost(float $deliveryCost): void
    {
        if ($deliveryCost < 0) {
            throw new \InvalidArgumentException('Delivery cost cannot be negative');
        }
    }
} 