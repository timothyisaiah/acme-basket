<?php

declare(strict_types=1);

namespace Acme\Basket\Tests\Unit;

use Acme\Basket\Basket;
use Acme\Basket\Product;
use Acme\Basket\Offers\PercentageDiscountOffer;
use Acme\Basket\Offers\BuyOneGetHalfOffRedWidgetOffer;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for Offer application logic
 */
class OfferApplicationUnitTest extends TestCase
{
    private array $catalogue;

    protected function setUp(): void
    {
        $this->catalogue = [
            new Product('RED_WIDGET', 'Red Widget', 10.00),
            new Product('BLUE_WIDGET', 'Blue Widget', 8.00),
            new Product('GREEN_WIDGET', 'Green Widget', 6.00),
            new Product('APPLE', 'Apple', 1.50),
            new Product('BANANA', 'Banana', 0.75),
        ];
    }

    /**
     * @test
     */
    public function percentage_discount_offer_basic_application(): void
    {
        $discountOffer = new PercentageDiscountOffer(10.0);
        $basket = new Basket($this->catalogue, [], [$discountOffer]);

        $basket->add('APPLE');
        $basket->add('BANANA');

        $originalTotal = 1.50 + 0.75; // 2.25
        $expectedDiscountedTotal = $originalTotal * 0.9; // 2.025

        $this->assertEqualsWithDelta($expectedDiscountedTotal, $basket->total(), 0.001);
    }

    /**
     * @test
     */
    public function percentage_discount_offer_with_zero_discount(): void
    {
        $discountOffer = new PercentageDiscountOffer(0.0);
        $basket = new Basket($this->catalogue, [], [$discountOffer]);

        $basket->add('RED_WIDGET');
        $basket->add('BLUE_WIDGET');

        $expectedTotal = 10.00 + 8.00; // No discount applied
        $this->assertEquals($expectedTotal, $basket->total());
    }

    /**
     * @test
     */
    public function percentage_discount_offer_with_full_discount(): void
    {
        $discountOffer = new PercentageDiscountOffer(100.0);
        $basket = new Basket($this->catalogue, [], [$discountOffer]);

        $basket->add('RED_WIDGET');
        $basket->add('BLUE_WIDGET');

        $expectedTotal = 0.0; // 100% discount
        $this->assertEquals($expectedTotal, $basket->total());
    }

    /**
     * @test
     */
    public function red_widget_bogo_offer_single_item(): void
    {
        $redWidgetOffer = new BuyOneGetHalfOffRedWidgetOffer();
        $basket = new Basket($this->catalogue, [], [$redWidgetOffer]);

        $basket->add('RED_WIDGET');

        // Single red widget should not get discount
        $this->assertEquals(10.00, $basket->total());
    }

    /**
     * @test
     */
    public function red_widget_bogo_offer_two_items(): void
    {
        $redWidgetOffer = new BuyOneGetHalfOffRedWidgetOffer();
        $basket = new Basket($this->catalogue, [], [$redWidgetOffer]);

        $basket->add('RED_WIDGET');
        $basket->add('RED_WIDGET');

        // First full price, second half price
        $expectedTotal = 10.00 + 5.00; // 15.00
        $this->assertEquals($expectedTotal, $basket->total());
    }

    /**
     * @test
     */
    public function red_widget_bogo_offer_three_items(): void
    {
        $redWidgetOffer = new BuyOneGetHalfOffRedWidgetOffer();
        $basket = new Basket($this->catalogue, [], [$redWidgetOffer]);

        $basket->add('RED_WIDGET');
        $basket->add('RED_WIDGET');
        $basket->add('RED_WIDGET');

        // First full price, second half price, third full price
        $expectedTotal = 10.00 + 5.00 + 10.00; // 25.00
        $this->assertEquals($expectedTotal, $basket->total());
    }

    /**
     * @test
     */
    public function red_widget_bogo_offer_four_items(): void
    {
        $redWidgetOffer = new BuyOneGetHalfOffRedWidgetOffer();
        $basket = new Basket($this->catalogue, [], [$redWidgetOffer]);

        $basket->add('RED_WIDGET');
        $basket->add('RED_WIDGET');
        $basket->add('RED_WIDGET');
        $basket->add('RED_WIDGET');

        // First full price, second half price, third full price, fourth half price
        $expectedTotal = 10.00 + 5.00 + 10.00 + 5.00; // 30.00
        $this->assertEquals($expectedTotal, $basket->total());
    }

    /**
     * @test
     */
    public function red_widget_bogo_offer_five_items(): void
    {
        $redWidgetOffer = new BuyOneGetHalfOffRedWidgetOffer();
        $basket = new Basket($this->catalogue, [], [$redWidgetOffer]);

        $basket->add('RED_WIDGET');
        $basket->add('RED_WIDGET');
        $basket->add('RED_WIDGET');
        $basket->add('RED_WIDGET');
        $basket->add('RED_WIDGET');

        // Pattern: full, half, full, half, full
        $expectedTotal = 10.00 + 5.00 + 10.00 + 5.00 + 10.00; // 40.00
        $this->assertEquals($expectedTotal, $basket->total());
    }

    /**
     * @test
     */
    public function red_widget_bogo_offer_with_other_products(): void
    {
        $redWidgetOffer = new BuyOneGetHalfOffRedWidgetOffer();
        $basket = new Basket($this->catalogue, [], [$redWidgetOffer]);

        $basket->add('RED_WIDGET');
        $basket->add('BLUE_WIDGET');
        $basket->add('RED_WIDGET');
        $basket->add('GREEN_WIDGET');

        // Red widgets: first full price, second half price
        // Other products: full price
        $expectedTotal = 10.00 + 8.00 + 5.00 + 6.00; // 29.00
        $this->assertEquals($expectedTotal, $basket->total());
    }

    /**
     * @test
     */
    public function red_widget_bogo_offer_removing_items(): void
    {
        $redWidgetOffer = new BuyOneGetHalfOffRedWidgetOffer();
        $basket = new Basket($this->catalogue, [], [$redWidgetOffer]);

        $basket->add('RED_WIDGET');
        $basket->add('RED_WIDGET');
        $basket->add('RED_WIDGET');

        // Initial state: 10.00 + 5.00 + 10.00 = 25.00
        $this->assertEquals(25.00, $basket->total());

        // Remove one red widget
        $basket->remove('RED_WIDGET');

        // Should recalculate: 10.00 + 5.00 = 15.00
        $this->assertEquals(15.00, $basket->total());

        // Remove another red widget
        $basket->remove('RED_WIDGET');

        // Should recalculate: 10.00 (single item, no discount)
        $this->assertEquals(10.00, $basket->total());
    }

    /**
     * @test
     */
    public function red_widget_bogo_offer_clearing_basket(): void
    {
        $redWidgetOffer = new BuyOneGetHalfOffRedWidgetOffer();
        $basket = new Basket($this->catalogue, [], [$redWidgetOffer]);

        $basket->add('RED_WIDGET');
        $basket->add('RED_WIDGET');
        $basket->add('BLUE_WIDGET');

        $basket->clear();

        $this->assertEquals(0.0, $basket->total());
    }

    /**
     * @test
     */
    public function red_widget_bogo_offer_only_affects_red_widgets(): void
    {
        $redWidgetOffer = new BuyOneGetHalfOffRedWidgetOffer();
        $basket = new Basket($this->catalogue, [], [$redWidgetOffer]);

        $basket->add('BLUE_WIDGET');
        $basket->add('BLUE_WIDGET');
        $basket->add('GREEN_WIDGET');
        $basket->add('GREEN_WIDGET');

        // No red widgets, so no discount applied
        $expectedTotal = (8.00 * 2) + (6.00 * 2); // 28.00
        $this->assertEquals($expectedTotal, $basket->total());
    }

    /**
     * @test
     */
    public function multiple_percentage_discount_offers(): void
    {
        $discountOffer1 = new PercentageDiscountOffer(10.0);
        $discountOffer2 = new PercentageDiscountOffer(5.0);
        $basket = new Basket($this->catalogue, [], [$discountOffer1, $discountOffer2]);

        $basket->add('RED_WIDGET');
        $basket->add('BLUE_WIDGET');

        $originalTotal = 10.00 + 8.00; // 18.00
        // Apply 10% discount first: 18.00 * 0.9 = 16.20
        // Then apply 5% discount: 16.20 * 0.95 = 15.39
        $expectedTotal = 15.39;

        $this->assertEqualsWithDelta($expectedTotal, $basket->total(), 0.001);
    }

    /**
     * @test
     */
    public function percentage_discount_with_red_widget_bogo(): void
    {
        $discountOffer = new PercentageDiscountOffer(15.0);
        $redWidgetOffer = new BuyOneGetHalfOffRedWidgetOffer();
        $basket = new Basket($this->catalogue, [], [$discountOffer, $redWidgetOffer]);

        $basket->add('RED_WIDGET');
        $basket->add('RED_WIDGET');
        $basket->add('BLUE_WIDGET');

        // Step 1: Apply BOGO offer
        // Red widgets: 10.00 + 5.00 = 15.00
        // Blue widget: 8.00
        // Subtotal: 23.00

        // Step 2: Apply 15% discount
        // 23.00 * 0.85 = 19.55
        $expectedTotal = 19.55;

        $this->assertEquals($expectedTotal, $basket->total());
    }

    /**
     * @test
     */
    public function offer_order_of_application(): void
    {
        $discountOffer = new PercentageDiscountOffer(20.0);
        $redWidgetOffer = new BuyOneGetHalfOffRedWidgetOffer();
        $basket = new Basket($this->catalogue, [], [$redWidgetOffer, $discountOffer]);

        $basket->add('RED_WIDGET');
        $basket->add('RED_WIDGET');
        $basket->add('APPLE');

        // Step 1: Apply BOGO offer first (as it's first in array)
        // Red widgets: 10.00 + 5.00 = 15.00
        // Apple: 1.50
        // Subtotal: 16.50

        // Step 2: Apply 20% discount
        // 16.50 * 0.8 = 13.20
        $expectedTotal = 13.20;

        $this->assertEquals($expectedTotal, $basket->total());
    }

    /**
     * @test
     */
    public function offer_application_with_empty_basket(): void
    {
        $discountOffer = new PercentageDiscountOffer(10.0);
        $redWidgetOffer = new BuyOneGetHalfOffRedWidgetOffer();
        $basket = new Basket($this->catalogue, [], [$discountOffer, $redWidgetOffer]);

        // Empty basket should return 0 regardless of offers
        $this->assertEquals(0.0, $basket->total());
    }

    /**
     * @test
     */
    public function offer_application_with_single_item_removal(): void
    {
        $redWidgetOffer = new BuyOneGetHalfOffRedWidgetOffer();
        $basket = new Basket($this->catalogue, [], [$redWidgetOffer]);

        $basket->add('RED_WIDGET');
        $basket->add('RED_WIDGET');
        $basket->add('BLUE_WIDGET');

        // Initial: 10.00 + 5.00 + 8.00 = 23.00
        $this->assertEquals(23.00, $basket->total());

        // Remove blue widget
        $basket->remove('BLUE_WIDGET');

        // After removal: 10.00 + 5.00 = 15.00
        $this->assertEquals(15.00, $basket->total());
    }

    /**
     * @test
     */
    public function offer_application_with_zero_price_products(): void
    {
        $catalogueWithZeroPrice = [
            new Product('FREE_RED_WIDGET', 'Free Red Widget', 0.00),
            new Product('PAID_ITEM', 'Paid Item', 10.00),
        ];

        $redWidgetOffer = new BuyOneGetHalfOffRedWidgetOffer();
        $basket = new Basket($catalogueWithZeroPrice, [], [$redWidgetOffer]);

        $basket->add('FREE_RED_WIDGET');
        $basket->add('FREE_RED_WIDGET');
        $basket->add('PAID_ITEM');

        // Free red widgets: 0.00 + 0.00 = 0.00 (half of zero is still zero)
        // Paid item: 10.00
        $expectedTotal = 10.00;

        $this->assertEquals($expectedTotal, $basket->total());
    }

    /**
     * @test
     */
    public function offer_application_edge_case_large_quantities(): void
    {
        $redWidgetOffer = new BuyOneGetHalfOffRedWidgetOffer();
        $basket = new Basket($this->catalogue, [], [$redWidgetOffer]);

        // Add 10 red widgets
        for ($i = 0; $i < 10; $i++) {
            $basket->add('RED_WIDGET');
        }

        // Pattern: full, half, full, half, full, half, full, half, full, half
        $expectedTotal = (10.00 * 5) + (5.00 * 5); // 50.00 + 25.00 = 75.00

        $this->assertEquals($expectedTotal, $basket->total());
    }
} 