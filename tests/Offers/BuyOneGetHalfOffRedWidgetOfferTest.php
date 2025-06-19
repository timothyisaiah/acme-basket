<?php

declare(strict_types=1);

namespace Acme\Basket\Tests\Offers;

use Acme\Basket\Offers\BuyOneGetHalfOffRedWidgetOffer;
use Acme\Basket\Product;
use PHPUnit\Framework\TestCase;

class BuyOneGetHalfOffRedWidgetOfferTest extends TestCase
{
    private BuyOneGetHalfOffRedWidgetOffer $offer;
    private Product $redWidget;
    private Product $blueWidget;
    private Product $redApple;
    private Product $greenWidget;

    protected function setUp(): void
    {
        $this->offer = new BuyOneGetHalfOffRedWidgetOffer();
        $this->redWidget = new Product('RED_WIDGET', 'Red Widget', 10.00);
        $this->blueWidget = new Product('BLUE_WIDGET', 'Blue Widget', 8.00);
        $this->redApple = new Product('RED_APPLE', 'Red Apple', 2.00);
        $this->greenWidget = new Product('GREEN_WIDGET', 'Green Widget', 6.00);
    }

    /**
     * @test
     */
    public function applies_half_price_to_second_red_widget(): void
    {
        $products = [
            $this->redWidget,
            $this->redWidget,
        ];
        
        $discountedProducts = $this->offer->apply($products);
        
        $this->assertCount(2, $discountedProducts);
        $this->assertEquals(10.00, $discountedProducts[0]->getPrice()); // First - full price
        $this->assertEquals(5.00, $discountedProducts[1]->getPrice());  // Second - half price
    }

    /**
     * @test
     */
    public function does_not_apply_offer_with_only_one_red_widget(): void
    {
        $products = [
            $this->redWidget,
            $this->blueWidget,
        ];
        
        $discountedProducts = $this->offer->apply($products);
        
        $this->assertCount(2, $discountedProducts);
        $this->assertEquals(10.00, $discountedProducts[0]->getPrice()); // Unchanged
        $this->assertEquals(8.00, $discountedProducts[1]->getPrice());  // Unchanged
    }

    /**
     * @test
     */
    public function applies_offer_only_to_red_widgets(): void
    {
        $products = [
            $this->redWidget,
            $this->blueWidget,
            $this->redWidget,
            $this->greenWidget,
        ];
        
        $discountedProducts = $this->offer->apply($products);
        
        $this->assertCount(4, $discountedProducts);
        $this->assertEquals(10.00, $discountedProducts[0]->getPrice()); // First red widget - full price
        $this->assertEquals(8.00, $discountedProducts[1]->getPrice());  // Blue widget - unchanged
        $this->assertEquals(5.00, $discountedProducts[2]->getPrice());  // Second red widget - half price
        $this->assertEquals(6.00, $discountedProducts[3]->getPrice());  // Green widget - unchanged
    }

    /**
     * @test
     */
    public function third_red_widget_gets_full_price(): void
    {
        $products = [
            $this->redWidget,
            $this->redWidget,
            $this->redWidget,
        ];
        
        $discountedProducts = $this->offer->apply($products);
        
        $this->assertCount(3, $discountedProducts);
        $this->assertEquals(10.00, $discountedProducts[0]->getPrice()); // First - full price
        $this->assertEquals(5.00, $discountedProducts[1]->getPrice());  // Second - half price
        $this->assertEquals(10.00, $discountedProducts[2]->getPrice()); // Third - full price
    }

    /**
     * @test
     */
    public function handles_case_insensitive_matching(): void
    {
        $redWidgetMixedCase = new Product('RED_WIDGET_2', 'RED WIDGET', 12.00);
        $redWidgetLowercase = new Product('RED_WIDGET_3', 'red widget', 15.00);
        
        $products = [
            $redWidgetMixedCase,
            $redWidgetLowercase,
        ];
        
        $discountedProducts = $this->offer->apply($products);
        
        $this->assertCount(2, $discountedProducts);
        $this->assertEquals(12.00, $discountedProducts[0]->getPrice()); // First - full price
        $this->assertEquals(7.50, $discountedProducts[1]->getPrice());  // Second - half price
    }

    /**
     * @test
     */
    public function does_not_apply_to_products_with_only_red_or_only_widget(): void
    {
        $products = [
            $this->redApple,    // Has "red" but not "widget"
            $this->blueWidget,  // Has "widget" but not "red"
        ];
        
        $discountedProducts = $this->offer->apply($products);
        
        $this->assertCount(2, $discountedProducts);
        $this->assertEquals(2.00, $discountedProducts[0]->getPrice()); // Unchanged
        $this->assertEquals(8.00, $discountedProducts[1]->getPrice()); // Unchanged
    }

    /**
     * @test
     */
    public function handles_empty_product_array(): void
    {
        $discountedProducts = $this->offer->apply([]);
        
        $this->assertEmpty($discountedProducts);
    }

    /**
     * @test
     */
    public function handles_single_red_widget(): void
    {
        $products = [$this->redWidget];
        
        $discountedProducts = $this->offer->apply($products);
        
        $this->assertCount(1, $discountedProducts);
        $this->assertEquals(10.00, $discountedProducts[0]->getPrice()); // Unchanged
    }

    /**
     * @test
     */
    public function handles_multiple_red_widgets_with_other_products(): void
    {
        $products = [
            $this->blueWidget,
            $this->redWidget,
            $this->redApple,
            $this->redWidget,
            $this->greenWidget,
            $this->redWidget,
        ];
        
        $discountedProducts = $this->offer->apply($products);
        
        $this->assertCount(6, $discountedProducts);
        $this->assertEquals(8.00, $discountedProducts[0]->getPrice());  // Blue widget - unchanged
        $this->assertEquals(10.00, $discountedProducts[1]->getPrice()); // First red widget - full price
        $this->assertEquals(2.00, $discountedProducts[2]->getPrice());  // Red apple - unchanged
        $this->assertEquals(5.00, $discountedProducts[3]->getPrice());  // Second red widget - half price
        $this->assertEquals(6.00, $discountedProducts[4]->getPrice());  // Green widget - unchanged
        $this->assertEquals(10.00, $discountedProducts[5]->getPrice()); // Third red widget - full price
    }

    /**
     * @test
     */
    public function preserves_product_codes_and_names(): void
    {
        $products = [
            $this->redWidget,
            $this->redWidget,
        ];
        
        $discountedProducts = $this->offer->apply($products);
        
        $this->assertEquals('RED_WIDGET', $discountedProducts[0]->getCode());
        $this->assertEquals('Red Widget', $discountedProducts[0]->getName());
        $this->assertEquals('RED_WIDGET', $discountedProducts[1]->getCode());
        $this->assertEquals('Red Widget', $discountedProducts[1]->getName());
    }

    /**
     * @test
     */
    public function returns_new_product_instances(): void
    {
        $products = [
            $this->redWidget,
            $this->redWidget,
        ];
        
        $discountedProducts = $this->offer->apply($products);
        
        $this->assertNotEquals($this->redWidget->getPrice(), $discountedProducts[1]->getPrice()); // Different price
    }

    /**
     * @test
     */
    public function handles_high_precision_prices(): void
    {
        $redWidgetPrecise = new Product('RED_WIDGET_PRECISE', 'Red Widget', 10.333333);
        $products = [
            $redWidgetPrecise,
            $redWidgetPrecise,
        ];
        
        $discountedProducts = $this->offer->apply($products);
        
        $this->assertEquals(10.333333, $discountedProducts[0]->getPrice());
        $this->assertEquals(5.1666665, $discountedProducts[1]->getPrice(), '', 0.000001);
    }

    /**
     * @test
     */
    public function handles_zero_price_red_widgets(): void
    {
        $freeRedWidget = new Product('FREE_RED_WIDGET', 'Red Widget', 0.0);
        $products = [
            $freeRedWidget,
            $freeRedWidget,
        ];
        
        $discountedProducts = $this->offer->apply($products);
        
        $this->assertEquals(0.0, $discountedProducts[0]->getPrice());
        $this->assertEquals(0.0, $discountedProducts[1]->getPrice());
    }

    /**
     * @test
     */
    public function handles_products_with_red_and_widget_in_different_positions(): void
    {
        $redWidgetVariant1 = new Product('WIDGET_RED', 'Widget Red', 20.00);
        $redWidgetVariant2 = new Product('RED_WIDGET_SPECIAL', 'Special Red Widget', 25.00);
        
        $products = [
            $redWidgetVariant1,
            $redWidgetVariant2,
        ];
        
        $discountedProducts = $this->offer->apply($products);
        
        $this->assertCount(2, $discountedProducts);
        $this->assertEquals(20.00, $discountedProducts[0]->getPrice()); // First - full price
        $this->assertEquals(12.50, $discountedProducts[1]->getPrice()); // Second - half price
    }
} 