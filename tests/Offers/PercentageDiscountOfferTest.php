<?php

declare(strict_types=1);

namespace Acme\Basket\Tests\Offers;

use Acme\Basket\Offers\PercentageDiscountOffer;
use Acme\Basket\Product;
use PHPUnit\Framework\TestCase;

class PercentageDiscountOfferTest extends TestCase
{
    /**
     * @test
     */
    public function applies_percentage_discount_to_products(): void
    {
        $offer = new PercentageDiscountOffer(10.0);
        
        $products = [
            new Product('APPLE', 'Apple', 1.00),
            new Product('BANANA', 'Banana', 2.00),
        ];
        
        $discountedProducts = $offer->apply($products);
        
        $this->assertCount(2, $discountedProducts);
        $this->assertEquals(0.90, $discountedProducts[0]->getPrice());
        $this->assertEquals(1.80, $discountedProducts[1]->getPrice());
    }

    /**
     * @test
     */
    public function zero_percentage_discount_returns_original_prices(): void
    {
        $offer = new PercentageDiscountOffer(0.0);
        
        $products = [
            new Product('APPLE', 'Apple', 1.00),
        ];
        
        $discountedProducts = $offer->apply($products);
        
        $this->assertEquals(1.00, $discountedProducts[0]->getPrice());
    }

    /**
     * @test
     */
    public function hundred_percentage_discount_returns_zero_prices(): void
    {
        $offer = new PercentageDiscountOffer(100.0);
        
        $products = [
            new Product('APPLE', 'Apple', 1.00),
        ];
        
        $discountedProducts = $offer->apply($products);
        
        $this->assertEquals(0.0, $discountedProducts[0]->getPrice());
    }

    /**
     * @test
     */
    public function throws_exception_for_negative_percentage(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Percentage must be between 0 and 100');
        
        new PercentageDiscountOffer(-10.0);
    }

    /**
     * @test
     */
    public function throws_exception_for_percentage_over_100(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Percentage must be between 0 and 100');
        
        new PercentageDiscountOffer(150.0);
    }

    /**
     * @test
     */
    public function preserves_product_codes_and_names(): void
    {
        $offer = new PercentageDiscountOffer(20.0);
        
        $products = [
            new Product('APPLE', 'Apple', 1.00),
        ];
        
        $discountedProducts = $offer->apply($products);
        
        $this->assertEquals('APPLE', $discountedProducts[0]->getCode());
        $this->assertEquals('Apple', $discountedProducts[0]->getName());
    }

    /**
     * @test
     */
    public function handles_empty_product_array(): void
    {
        $offer = new PercentageDiscountOffer(10.0);
        
        $discountedProducts = $offer->apply([]);
        
        $this->assertEmpty($discountedProducts);
    }

    /**
     * @test
     */
    public function handles_high_precision_discounts(): void
    {
        $offer = new PercentageDiscountOffer(33.333);
        
        $products = [
            new Product('PRECISE', 'Precise Item', 1.00),
        ];
        
        $discountedProducts = $offer->apply($products);
        
        $this->assertEquals(0.666667, $discountedProducts[0]->getPrice(), '', 0.000001);
    }

    /**
     * @test
     */
    public function returns_new_product_instances(): void
    {
        $offer = new PercentageDiscountOffer(10.0);
        
        $originalProduct = new Product('APPLE', 'Apple', 1.00);
        $products = [$originalProduct];
        
        $discountedProducts = $offer->apply($products);
        
        $this->assertNotSame($originalProduct, $discountedProducts[0]);
        $this->assertFalse($originalProduct->equals($discountedProducts[0]));
    }
} 