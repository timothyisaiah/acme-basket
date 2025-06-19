<?php

declare(strict_types=1);

namespace Acme\Basket\Tests;

use Acme\Basket\Product;
use PHPUnit\Framework\TestCase;

class ProductTest extends TestCase
{
    /**
     * @test
     */
    public function can_create_product_with_valid_data(): void
    {
        $product = new Product('APPLE', 'Apple', 1.50);
        
        $this->assertEquals('APPLE', $product->getCode());
        $this->assertEquals('Apple', $product->getName());
        $this->assertEquals(1.50, $product->getPrice());
    }

    /**
     * @test
     */
    public function throws_exception_for_negative_price(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Product price cannot be negative');
        
        new Product('APPLE', 'Apple', -1.50);
    }

    /**
     * @test
     */
    public function can_handle_zero_price(): void
    {
        $product = new Product('FREE', 'Free Item', 0.0);
        
        $this->assertEquals(0.0, $product->getPrice());
    }

    /**
     * @test
     */
    public function equals_returns_true_for_same_code(): void
    {
        $product1 = new Product('APPLE', 'Apple', 1.50);
        $product2 = new Product('APPLE', 'Different Apple', 2.00);
        
        $this->assertTrue($product1->equals($product2));
    }

    /**
     * @test
     */
    public function equals_returns_false_for_different_code(): void
    {
        $product1 = new Product('APPLE', 'Apple', 1.50);
        $product2 = new Product('BANANA', 'Banana', 1.50);
        
        $this->assertFalse($product1->equals($product2));
    }

    /**
     * @test
     */
    public function to_string_returns_formatted_string(): void
    {
        $product = new Product('APPLE', 'Apple', 1.50);
        
        $this->assertEquals('Apple (APPLE)', (string) $product);
    }

    /**
     * @test
     */
    public function product_is_immutable(): void
    {
        $product = new Product('APPLE', 'Apple', 1.50);
        
        // Verify that properties are readonly by checking reflection
        $reflection = new \ReflectionClass($product);
        
        foreach ($reflection->getProperties() as $property) {
            $this->assertTrue($property->isReadOnly());
        }
    }

    /**
     * @test
     */
    public function can_handle_high_precision_prices(): void
    {
        $product = new Product('PRECISE', 'Precise Item', 0.333333);
        
        $this->assertEquals(0.333333, $product->getPrice());
    }

    /**
     * @test
     */
    public function can_handle_large_prices(): void
    {
        $product = new Product('EXPENSIVE', 'Expensive Item', 999999.99);
        
        $this->assertEquals(999999.99, $product->getPrice());
    }
} 