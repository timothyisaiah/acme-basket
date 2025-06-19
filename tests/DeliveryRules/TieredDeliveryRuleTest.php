<?php

declare(strict_types=1);

namespace Acme\Basket\Tests\DeliveryRules;

use Acme\Basket\DeliveryRules\TieredDeliveryRule;
use Acme\Basket\Product;
use PHPUnit\Framework\TestCase;

class TieredDeliveryRuleTest extends TestCase
{
    private TieredDeliveryRule $rule;

    protected function setUp(): void
    {
        $this->rule = new TieredDeliveryRule();
    }

    /**
     * @test
     */
    public function charges_4_95_for_total_below_50(): void
    {
        $products = [
            new Product('ITEM1', 'Item 1', 25.00),
            new Product('ITEM2', 'Item 2', 15.00),
        ];
        
        $deliveryCost = $this->rule->calculate($products);
        
        $this->assertEquals(4.95, $deliveryCost);
    }

    /**
     * @test
     */
    public function charges_4_95_for_total_exactly_49_99(): void
    {
        $products = [
            new Product('ITEM1', 'Item 1', 49.99),
        ];
        
        $deliveryCost = $this->rule->calculate($products);
        
        $this->assertEquals(4.95, $deliveryCost);
    }

    /**
     * @test
     */
    public function charges_2_95_for_total_at_50(): void
    {
        $products = [
            new Product('ITEM1', 'Item 1', 50.00),
        ];
        
        $deliveryCost = $this->rule->calculate($products);
        
        $this->assertEquals(2.95, $deliveryCost);
    }

    /**
     * @test
     */
    public function charges_2_95_for_total_between_50_and_90(): void
    {
        $products = [
            new Product('ITEM1', 'Item 1', 50.00),
            new Product('ITEM2', 'Item 2', 25.00),
        ];
        
        $deliveryCost = $this->rule->calculate($products);
        
        $this->assertEquals(2.95, $deliveryCost);
    }

    /**
     * @test
     */
    public function charges_2_95_for_total_exactly_89_99(): void
    {
        $products = [
            new Product('ITEM1', 'Item 1', 89.99),
        ];
        
        $deliveryCost = $this->rule->calculate($products);
        
        $this->assertEquals(2.95, $deliveryCost);
    }

    /**
     * @test
     */
    public function provides_free_delivery_at_90(): void
    {
        $products = [
            new Product('ITEM1', 'Item 1', 90.00),
        ];
        
        $deliveryCost = $this->rule->calculate($products);
        
        $this->assertEquals(0.0, $deliveryCost);
    }

    /**
     * @test
     */
    public function provides_free_delivery_above_90(): void
    {
        $products = [
            new Product('ITEM1', 'Item 1', 100.00),
            new Product('ITEM2', 'Item 2', 25.00),
        ];
        
        $deliveryCost = $this->rule->calculate($products);
        
        $this->assertEquals(0.0, $deliveryCost);
    }

    /**
     * @test
     */
    public function handles_empty_product_array(): void
    {
        $deliveryCost = $this->rule->calculate([]);
        
        $this->assertEquals(4.95, $deliveryCost);
    }

    /**
     * @test
     */
    public function handles_zero_price_products(): void
    {
        $products = [
            new Product('FREE1', 'Free Item 1', 0.0),
            new Product('FREE2', 'Free Item 2', 0.0),
        ];
        
        $deliveryCost = $this->rule->calculate($products);
        
        $this->assertEquals(4.95, $deliveryCost);
    }

    /**
     * @test
     */
    public function handles_high_precision_prices(): void
    {
        $products = [
            new Product('PRECISE1', 'Precise Item 1', 49.999999),
            new Product('PRECISE2', 'Precise Item 2', 0.000001),
        ];
        
        $deliveryCost = $this->rule->calculate($products);
        
        $this->assertEquals(4.95, $deliveryCost);
    }

    /**
     * @test
     */
    public function handles_large_values(): void
    {
        $products = [
            new Product('EXPENSIVE', 'Expensive Item', 999999.99),
        ];
        
        $deliveryCost = $this->rule->calculate($products);
        
        $this->assertEquals(0.0, $deliveryCost);
    }

    /**
     * @test
     */
    public function handles_exact_threshold_boundaries(): void
    {
        // Test exactly at $50 threshold
        $productsAt50 = [new Product('ITEM1', 'Item 1', 50.00)];
        $deliveryCostAt50 = $this->rule->calculate($productsAt50);
        $this->assertEquals(2.95, $deliveryCostAt50);
        
        // Test exactly at $90 threshold
        $productsAt90 = [new Product('ITEM1', 'Item 1', 90.00)];
        $deliveryCostAt90 = $this->rule->calculate($productsAt90);
        $this->assertEquals(0.0, $deliveryCostAt90);
    }

    /**
     * @test
     */
    public function handles_multiple_products_across_thresholds(): void
    {
        // Test with products that span multiple tiers
        $products = [
            new Product('CHEAP', 'Cheap Item', 10.00),
            new Product('MEDIUM', 'Medium Item', 30.00),
            new Product('EXPENSIVE', 'Expensive Item', 60.00),
        ];
        
        $deliveryCost = $this->rule->calculate($products);
        
        $this->assertEquals(0.0, $deliveryCost); // Total: 100.00, should be free
    }

    /**
     * @test
     */
    public function handles_edge_case_just_below_thresholds(): void
    {
        // Just below $50
        $productsJustBelow50 = [new Product('ITEM1', 'Item 1', 49.99)];
        $deliveryCostJustBelow50 = $this->rule->calculate($productsJustBelow50);
        $this->assertEquals(4.95, $deliveryCostJustBelow50);
        
        // Just below $90
        $productsJustBelow90 = [new Product('ITEM1', 'Item 1', 89.99)];
        $deliveryCostJustBelow90 = $this->rule->calculate($productsJustBelow90);
        $this->assertEquals(2.95, $deliveryCostJustBelow90);
    }

    /**
     * @test
     */
    public function handles_edge_case_just_above_thresholds(): void
    {
        // Just above $50
        $productsJustAbove50 = [new Product('ITEM1', 'Item 1', 50.01)];
        $deliveryCostJustAbove50 = $this->rule->calculate($productsJustAbove50);
        $this->assertEquals(2.95, $deliveryCostJustAbove50);
        
        // Just above $90
        $productsJustAbove90 = [new Product('ITEM1', 'Item 1', 90.01)];
        $deliveryCostJustAbove90 = $this->rule->calculate($productsJustAbove90);
        $this->assertEquals(0.0, $deliveryCostJustAbove90);
    }

    /**
     * @test
     */
    public function handles_very_small_decimal_values(): void
    {
        $products = [
            new Product('TINY1', 'Tiny Item 1', 0.01),
            new Product('TINY2', 'Tiny Item 2', 0.01),
        ];
        
        $deliveryCost = $this->rule->calculate($products);
        
        $this->assertEquals(4.95, $deliveryCost);
    }

    /**
     * @test
     */
    public function handles_very_large_decimal_values(): void
    {
        $products = [
            new Product('HUGE', 'Huge Item', 999999.999999),
        ];
        
        $deliveryCost = $this->rule->calculate($products);
        
        $this->assertEquals(0.0, $deliveryCost);
    }
} 