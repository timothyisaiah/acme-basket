<?php

declare(strict_types=1);

namespace Acme\Basket\Offers;

use Acme\Basket\Offer;
use Acme\Basket\Product;

/**
 * Buy One Get Half Off Red Widget Offer
 * 
 * Applies "Buy one red widget, get the second for half price" offer.
 * This offer only applies to products with "red" in the name and "widget" in the name.
 */
final class BuyOneGetHalfOffRedWidgetOffer implements Offer
{
    public function apply(array $products): array
    {
        $redWidgets = $this->filterRedWidgets($products);
        
        if (count($redWidgets) < 2) {
            // Not enough red widgets for the offer
            return $products;
        }
        
        $discountedProducts = [];
        $redWidgetCount = 0;
        
        foreach ($products as $product) {
            if ($this->isRedWidget($product)) {
                $redWidgetCount++;
                
                if ($redWidgetCount === 1) {
                    // First red widget - full price
                    $discountedProducts[] = $product;
                } elseif ($redWidgetCount === 2) {
                    // Second red widget - half price
                    $discountedProducts[] = $this->createDiscountedProduct($product, 0.5);
                } else {
                    // Third and subsequent red widgets - full price
                    $discountedProducts[] = $product;
                }
            } else {
                // Non-red widget products - unchanged
                $discountedProducts[] = $product;
            }
        }
        
        return $discountedProducts;
    }
    
    /**
     * Filter products to only include red widgets
     *
     * @param array<Product> $products
     * @return array<Product>
     */
    private function filterRedWidgets(array $products): array
    {
        return array_filter($products, fn(Product $product) => $this->isRedWidget($product));
    }
    
    /**
     * Check if a product is a red widget
     */
    private function isRedWidget(Product $product): bool
    {
        $name = strtolower($product->getName());
        return str_contains($name, 'red') && str_contains($name, 'widget');
    }
    
    /**
     * Create a discounted version of a product
     */
    private function createDiscountedProduct(Product $product, float $discountMultiplier): Product
    {
        return new Product(
            $product->getCode(),
            $product->getName(),
            $product->getPrice() * $discountMultiplier
        );
    }
} 