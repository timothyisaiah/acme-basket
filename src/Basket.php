<?php

declare(strict_types=1);

namespace Acme\Basket;

/**
 * Domain-driven Basket implementation using dependency injection and strategy patterns
 */
final class Basket
{
    /**
     * @var array<string, Product> Catalogue of available products indexed by code
     */
    private readonly array $catalogue;

    /**
     * @var array<string, int> Product codes and their quantities in the basket
     */
    private array $items = [];

    /**
     * @var array<DeliveryRule> Delivery rules to apply
     */
    private readonly array $deliveryRules;

    /**
     * @var array<Offer> Offers to apply
     */
    private readonly array $offers;

    public function __construct(
        array $catalogue,
        array $deliveryRules = [],
        array $offers = []
    ) {
        $this->validateCatalogue($catalogue);
        $this->validateDeliveryRules($deliveryRules);
        $this->validateOffers($offers);
        
        $this->catalogue = $this->indexCatalogueByCode($catalogue);
        $this->deliveryRules = $deliveryRules;
        $this->offers = $offers;
    }

    /**
     * Add a product to the basket by its code
     */
    public function add(string $productCode): void
    {
        if (!isset($this->catalogue[$productCode])) {
            throw new \InvalidArgumentException("Product with code '{$productCode}' not found in catalogue");
        }

        if (isset($this->items[$productCode])) {
            $this->items[$productCode]++;
        } else {
            $this->items[$productCode] = 1;
        }
    }

    /**
     * Remove a product from the basket by its code
     */
    public function remove(string $productCode): bool
    {
        if (!isset($this->items[$productCode])) {
            return false;
        }

        if ($this->items[$productCode] > 1) {
            $this->items[$productCode]--;
        } else {
            unset($this->items[$productCode]);
        }

        return true;
    }

    /**
     * Clear all items from the basket
     */
    public function clear(): void
    {
        $this->items = [];
    }

    /**
     * Get the total cost including products, offers, and delivery
     */
    public function total(): float
    {
        $products = $this->getProductsInBasket();
        
        // Apply offers to get discounted products
        $discountedProducts = $this->applyOffers($products);
        
        // Calculate product total
        $productTotal = $this->calculateProductTotal($discountedProducts);
        
        // Calculate delivery cost
        $deliveryCost = $this->calculateDeliveryCost($discountedProducts);
        
        return $productTotal + $deliveryCost;
    }

    /**
     * Get the number of unique products in the basket
     */
    public function itemCount(): int
    {
        return count($this->items);
    }

    /**
     * Get the total quantity of all items in the basket
     */
    public function totalQuantity(): int
    {
        return array_sum($this->items);
    }

    /**
     * Get all products currently in the basket
     *
     * @return array<Product>
     */
    public function getProducts(): array
    {
        return $this->getProductsInBasket();
    }

    /**
     * Get the catalogue of available products
     *
     * @return array<Product>
     */
    public function getCatalogue(): array
    {
        return array_values($this->catalogue);
    }

    /**
     * Check if a product is in the basket
     */
    public function contains(string $productCode): bool
    {
        return isset($this->items[$productCode]);
    }

    /**
     * Get the quantity of a specific product in the basket
     */
    public function getQuantity(string $productCode): int
    {
        return $this->items[$productCode] ?? 0;
    }

    /**
     * Check if the basket is empty
     */
    public function isEmpty(): bool
    {
        return empty($this->items);
    }

    /**
     * Get products in basket with their quantities
     *
     * @return array<Product>
     */
    private function getProductsInBasket(): array
    {
        $products = [];
        
        foreach ($this->items as $productCode => $quantity) {
            $product = $this->catalogue[$productCode];
            
            // Add the product multiple times based on quantity
            for ($i = 0; $i < $quantity; $i++) {
                $products[] = $product;
            }
        }
        
        return $products;
    }

    /**
     * Apply all offers to the products
     *
     * @param array<Product> $products
     * @return array<Product>
     */
    private function applyOffers(array $products): array
    {
        $discountedProducts = $products;
        
        foreach ($this->offers as $offer) {
            $discountedProducts = $offer->apply($discountedProducts);
        }
        
        return $discountedProducts;
    }

    /**
     * Calculate the total cost of products
     *
     * @param array<Product> $products
     */
    private function calculateProductTotal(array $products): float
    {
        return array_reduce(
            $products,
            fn(float $total, Product $product) => $total + $product->getPrice(),
            0.0
        );
    }

    /**
     * Calculate delivery cost using all delivery rules
     *
     * @param array<Product> $products
     */
    private function calculateDeliveryCost(array $products): float
    {
        if (empty($this->deliveryRules)) {
            return 0.0;
        }

        // Use the first delivery rule (could be extended to use multiple rules)
        $deliveryRule = $this->deliveryRules[0];
        return $deliveryRule->calculate($products);
    }

    /**
     * Index catalogue by product code for efficient lookup
     *
     * @param array<Product> $catalogue
     * @return array<string, Product>
     */
    private function indexCatalogueByCode(array $catalogue): array
    {
        $indexed = [];
        
        foreach ($catalogue as $product) {
            $indexed[$product->getCode()] = $product;
        }
        
        return $indexed;
    }

    /**
     * Validate catalogue contains only Product objects
     *
     * @param array $catalogue
     */
    private function validateCatalogue(array $catalogue): void
    {
        foreach ($catalogue as $product) {
            if (!$product instanceof Product) {
                throw new \InvalidArgumentException('Catalogue must contain only Product objects');
            }
        }
    }

    /**
     * Validate delivery rules contain only DeliveryRule objects
     *
     * @param array $deliveryRules
     */
    private function validateDeliveryRules(array $deliveryRules): void
    {
        foreach ($deliveryRules as $rule) {
            if (!$rule instanceof DeliveryRule) {
                throw new \InvalidArgumentException('Delivery rules must contain only DeliveryRule objects');
            }
        }
    }

    /**
     * Validate offers contain only Offer objects
     *
     * @param array $offers
     */
    private function validateOffers(array $offers): void
    {
        foreach ($offers as $offer) {
            if (!$offer instanceof Offer) {
                throw new \InvalidArgumentException('Offers must contain only Offer objects');
            }
        }
    }
} 