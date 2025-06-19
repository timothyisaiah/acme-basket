<?php

declare(strict_types=1);

namespace Acme\Basket;

/**
 * Interface for applying offers to products
 */
interface Offer
{
    /**
     * Apply the offer to the given products
     *
     * @param array<Product> $products The products to apply the offer to
     * @return array<Product> The products after applying the offer
     */
    public function apply(array $products): array;
} 