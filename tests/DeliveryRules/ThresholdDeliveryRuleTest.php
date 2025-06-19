<?php

declare(strict_types=1);

namespace Acme\Basket\Tests\DeliveryRules;

use Acme\Basket\DeliveryRules\ThresholdDeliveryRule;
use Acme\Basket\Product;
use PHPUnit\Framework\TestCase;

class ThresholdDeliveryRuleTest extends TestCase
{
    /**
     * @test
     */
    public function charges_delivery_cost_below_threshold(): void
    {
        $rule = new ThresholdDeliveryRule(50.0, 5.0);
        
        $products = [
            new Product('APPLE', 'Apple', 10.0),
            new Product('BANANA', 'Banana', 15.0),
        ];
        
        $deliveryCost = $rule->calculate($products);
        
        $this->assertEquals(5.0, $deliveryCost);
    }

    /**
     * @test
     */
    public function provides_free_delivery_at_threshold(): void
    {
        $rule = new ThresholdDeliveryRule(50.0, 5.0);
        
        $products = [
            new Product('APPLE', 'Apple', 25.0),
            new Product('BANANA', 'Banana', 25.0),
        ];
        
        $deliveryCost = $rule->calculate($products);
        
        $this->assertEquals(0.0, $deliveryCost);
    }

    /**
     * @test
     */
    public function provides_free_delivery_above_threshold(): void
    {
        $rule = new ThresholdDeliveryRule(50.0, 5.0);
        
        $products = [
            new Product('APPLE', 'Apple', 30.0),
            new Product('BANANA', 'Banana', 30.0),
        ];
        
        $deliveryCost = $rule->calculate($products);
        
        $this->assertEquals(0.0, $deliveryCost);
    }

    /**
     * @test
     */
    public function handles_empty_product_array(): void
    {
        $rule = new ThresholdDeliveryRule(50.0, 5.0);
        
        $deliveryCost = $rule->calculate([]);
        
        $this->assertEquals(5.0, $deliveryCost);
    }

    /**
     * @test
     */
    public function handles_zero_threshold(): void
    {
        $rule = new ThresholdDeliveryRule(0.0, 5.0);
        
        $products = [
            new Product('APPLE', 'Apple', 1.0),
        ];
        
        $deliveryCost = $rule->calculate($products);
        
        $this->assertEquals(0.0, $deliveryCost);
    }

    /**
     * @test
     */
    public function handles_zero_delivery_cost(): void
    {
        $rule = new ThresholdDeliveryRule(50.0, 0.0);
        
        $products = [
            new Product('APPLE', 'Apple', 10.0),
        ];
        
        $deliveryCost = $rule->calculate($products);
        
        $this->assertEquals(0.0, $deliveryCost);
    }

    /**
     * @test
     */
    public function throws_exception_for_negative_threshold(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Threshold cannot be negative');
        
        new ThresholdDeliveryRule(-10.0, 5.0);
    }

    /**
     * @test
     */
    public function throws_exception_for_negative_delivery_cost(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Delivery cost cannot be negative');
        
        new ThresholdDeliveryRule(50.0, -5.0);
    }

    /**
     * @test
     */
    public function handles_high_precision_calculations(): void
    {
        $rule = new ThresholdDeliveryRule(1.0, 0.50);
        
        $products = [
            new Product('PRECISE', 'Precise Item', 0.333333),
            new Product('PRECISE2', 'Precise Item 2', 0.333333),
            new Product('PRECISE3', 'Precise Item 3', 0.333333),
        ];
        
        $deliveryCost = $rule->calculate($products);
        
        $this->assertEquals(0.0, $deliveryCost);
    }

    /**
     * @test
     */
    public function handles_large_values(): void
    {
        $rule = new ThresholdDeliveryRule(1000.0, 10.0);
        
        $products = [
            new Product('EXPENSIVE', 'Expensive Item', 999.99),
        ];
        
        $deliveryCost = $rule->calculate($products);
        
        $this->assertEquals(10.0, $deliveryCost);
    }

    /**
     * @test
     */
    public function handles_exact_threshold_match(): void
    {
        $rule = new ThresholdDeliveryRule(25.0, 5.0);
        
        $products = [
            new Product('ITEM1', 'Item 1', 12.5),
            new Product('ITEM2', 'Item 2', 12.5),
        ];
        
        $deliveryCost = $rule->calculate($products);
        
        $this->assertEquals(0.0, $deliveryCost);
    }
} 