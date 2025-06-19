<?php

declare(strict_types=1);

namespace Acme\Basket;

/**
 * Interface for calculating delivery costs
 */
interface DeliveryRule
{
    /**
     * Calculate the delivery cost for the given products
     *
     * @param array<Product> $products The products to calculate delivery for
     * @return float The delivery cost
     */
    public function calculate(array $products): float;
} 