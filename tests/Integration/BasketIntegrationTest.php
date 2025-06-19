<?php

declare(strict_types=1);

namespace Acme\Basket\Tests\Integration;

use Acme\Basket\Basket;
use Acme\Basket\Product;
use Acme\Basket\Offers\PercentageDiscountOffer;
use Acme\Basket\Offers\BuyOneGetHalfOffRedWidgetOffer;
use Acme\Basket\DeliveryRules\ThresholdDeliveryRule;
use Acme\Basket\DeliveryRules\TieredDeliveryRule;
use PHPUnit\Framework\TestCase;

/**
 * Integration tests covering all example scenarios from requirements
 */
class BasketIntegrationTest extends TestCase
{
    private Basket $basket;
    private array $catalogue;

    protected function setUp(): void
    {
        // Create a comprehensive product catalogue
        $this->catalogue = [
            new Product('APPLE', 'Apple', 1.50),
            new Product('BANANA', 'Banana', 0.75),
            new Product('ORANGE', 'Orange', 2.00),
            new Product('RED_WIDGET', 'Red Widget', 10.00),
            new Product('BLUE_WIDGET', 'Blue Widget', 8.00),
            new Product('GREEN_WIDGET', 'Green Widget', 6.00),
            new Product('LUXURY_ITEM', 'Luxury Item', 100.00),
            new Product('BUDGET_ITEM', 'Budget Item', 5.00),
        ];
    }

    /**
     * @test
     */
    public function basic_basket_functionality(): void
    {
        $this->basket = new Basket($this->catalogue);

        // Test basic add functionality
        $this->basket->add('APPLE');
        $this->basket->add('BANANA');
        $this->basket->add('BANANA'); // Second banana

        $this->assertEquals(2, $this->basket->itemCount());
        $this->assertEquals(3, $this->basket->totalQuantity());
        $this->assertEquals(1, $this->basket->getQuantity('APPLE'));
        $this->assertEquals(2, $this->basket->getQuantity('BANANA'));

        // Test total calculation
        $expectedTotal = 1.50 + (0.75 * 2); // 3.00
        $this->assertEquals($expectedTotal, $this->basket->total());

        // Test remove functionality
        $this->basket->remove('BANANA');
        $this->assertEquals(1, $this->basket->getQuantity('BANANA'));
        $this->assertEquals(2, $this->basket->totalQuantity());

        // Test clear functionality
        $this->basket->clear();
        $this->assertEquals(0, $this->basket->itemCount());
        $this->assertEquals(0.0, $this->basket->total());
    }

    /**
     * @test
     */
    public function percentage_discount_offer_integration(): void
    {
        $discountOffer = new PercentageDiscountOffer(10.0);
        $this->basket = new Basket($this->catalogue, [], [$discountOffer]);

        $this->basket->add('APPLE');
        $this->basket->add('BANANA');
        $this->basket->add('ORANGE');

        $originalTotal = 1.50 + 0.75 + 2.00; // 4.25
        $expectedDiscountedTotal = $originalTotal * 0.9; // 3.825

        $this->assertEquals($expectedDiscountedTotal, $this->basket->total());
    }

    /**
     * @test
     */
    public function red_widget_bogo_offer_integration(): void
    {
        $redWidgetOffer = new BuyOneGetHalfOffRedWidgetOffer();
        $this->basket = new Basket($this->catalogue, [], [$redWidgetOffer]);

        // Add two red widgets
        $this->basket->add('RED_WIDGET');
        $this->basket->add('RED_WIDGET');

        $expectedTotal = 10.00 + 5.00; // First full price, second half price
        $this->assertEquals($expectedTotal, $this->basket->total());

        // Add a third red widget (should be full price)
        $this->basket->add('RED_WIDGET');
        $expectedTotalWithThird = 10.00 + 5.00 + 10.00;
        $this->assertEquals($expectedTotalWithThird, $this->basket->total());
    }

    /**
     * @test
     */
    public function threshold_delivery_rule_integration(): void
    {
        $deliveryRule = new ThresholdDeliveryRule(10.0, 2.0);
        $this->basket = new Basket($this->catalogue, [$deliveryRule]);

        // Test below threshold
        $this->basket->add('APPLE');
        $this->basket->add('BANANA');
        $productTotal = 1.50 + 0.75; // 2.25
        $expectedTotal = $productTotal + 2.0; // 4.25
        $this->assertEquals($expectedTotal, $this->basket->total());

        $this->basket->clear();

        // Test above threshold
        $this->basket->add('LUXURY_ITEM');
        $expectedTotal = 100.00; // Free delivery
        $this->assertEquals($expectedTotal, $this->basket->total());
    }

    /**
     * @test
     */
    public function tiered_delivery_rule_integration(): void
    {
        $deliveryRule = new TieredDeliveryRule();
        $this->basket = new Basket($this->catalogue, [$deliveryRule]);

        // Test tier 1 (< $50)
        $this->basket->add('BUDGET_ITEM');
        $this->basket->add('BUDGET_ITEM');
        $this->basket->add('APPLE');
        $productTotal = 5.00 + 5.00 + 1.50; // 11.50
        $expectedTotal = $productTotal + 4.95; // 16.45
        $this->assertEquals($expectedTotal, $this->basket->total());

        $this->basket->clear();

        // Test tier 2 ($50 - $89.99)
        $this->basket->add('BLUE_WIDGET');
        $this->basket->add('GREEN_WIDGET');
        $this->basket->add('ORANGE');
        $productTotal = 8.00 + 6.00 + 2.00; // 16.00
        $expectedTotal = $productTotal + 2.95; // 18.95
        $this->assertEqualsWithDelta($expectedTotal, $this->basket->total(), 0.001);

        $this->basket->clear();

        // Test tier 3 (>= $90)
        $this->basket->add('LUXURY_ITEM');
        $expectedTotal = 100.00; // Free delivery
        $this->assertEquals($expectedTotal, $this->basket->total());
    }

    /**
     * @test
     */
    public function multiple_offers_integration(): void
    {
        $discountOffer = new PercentageDiscountOffer(15.0);
        $redWidgetOffer = new BuyOneGetHalfOffRedWidgetOffer();
        $this->basket = new Basket($this->catalogue, [], [$discountOffer, $redWidgetOffer]);

        $this->basket->add('RED_WIDGET');
        $this->basket->add('RED_WIDGET');
        $this->basket->add('APPLE');

        // Calculate step by step:
        // 1. Red widget BOGO: 10.00 + 5.00 = 15.00
        // 2. Add apple: 15.00 + 1.50 = 16.50
        // 3. Apply 15% discount: 16.50 * 0.85 = 14.025
        $expectedTotal = 14.025;
        $this->assertEqualsWithDelta($expectedTotal, $this->basket->total(), 0.001);
    }

    /**
     * @test
     */
    public function offers_and_delivery_integration(): void
    {
        $discountOffer = new PercentageDiscountOffer(10.0);
        $deliveryRule = new TieredDeliveryRule();
        $this->basket = new Basket($this->catalogue, [$deliveryRule], [$discountOffer]);

        $this->basket->add('LUXURY_ITEM');
        $this->basket->add('APPLE');

        // Calculate step by step:
        // 1. Product total: 100.00 + 1.50 = 101.50
        // 2. Apply 10% discount: 101.50 * 0.9 = 91.35
        // 3. Apply delivery (>= $90): Free
        $expectedTotal = 91.35;
        $this->assertEqualsWithDelta($expectedTotal, $this->basket->total(), 0.001);
    }

    /**
     * @test
     */
    public function complex_scenario_integration(): void
    {
        $discountOffer = new PercentageDiscountOffer(20.0);
        $redWidgetOffer = new BuyOneGetHalfOffRedWidgetOffer();
        $deliveryRule = new ThresholdDeliveryRule(50.0, 5.0);
        
        $this->basket = new Basket($this->catalogue, [$deliveryRule], [$discountOffer, $redWidgetOffer]);

        // Add various products
        $this->basket->add('RED_WIDGET');
        $this->basket->add('RED_WIDGET');
        $this->basket->add('BLUE_WIDGET');
        $this->basket->add('APPLE');
        $this->basket->add('BANANA');

        // Calculate step by step:
        // 1. Red widget BOGO: 10.00 + 5.00 = 15.00
        // 2. Add other products: 15.00 + 8.00 + 1.50 + 0.75 = 25.25
        // 3. Apply 20% discount: 25.25 * 0.8 = 20.20
        // 4. Apply delivery (< $50): 20.20 + 5.0 = 25.20
        $expectedTotal = 25.20;
        $this->assertEqualsWithDelta($expectedTotal, $this->basket->total(), 0.001);
    }

    /**
     * @test
     */
    public function edge_case_empty_basket(): void
    {
        $discountOffer = new PercentageDiscountOffer(10.0);
        $deliveryRule = new TieredDeliveryRule();
        $this->basket = new Basket($this->catalogue, [$deliveryRule], [$discountOffer]);

        $this->assertEquals(0, $this->basket->itemCount());
        $this->assertEquals(0, $this->basket->totalQuantity());
        $this->assertEquals(4.95, $this->basket->total()); // Delivery cost for empty basket
    }

    /**
     * @test
     */
    public function edge_case_single_item(): void
    {
        $redWidgetOffer = new BuyOneGetHalfOffRedWidgetOffer();
        $this->basket = new Basket($this->catalogue, [], [$redWidgetOffer]);

        $this->basket->add('RED_WIDGET');

        // Single red widget should not get discount
        $this->assertEquals(10.00, $this->basket->total());
    }

    /**
     * @test
     */
    public function edge_case_exact_thresholds(): void
    {
        $deliveryRule = new TieredDeliveryRule();
        $this->basket = new Basket($this->catalogue, [$deliveryRule]);

        // Test exactly $50
        $this->basket->add('BLUE_WIDGET'); // 8.00
        $this->basket->add('GREEN_WIDGET'); // 6.00
        $this->basket->add('ORANGE'); // 2.00
        $this->basket->add('BUDGET_ITEM'); // 5.00
        $this->basket->add('BUDGET_ITEM'); // 5.00
        $this->basket->add('BUDGET_ITEM'); // 5.00
        $this->basket->add('BUDGET_ITEM'); // 5.00
        $this->basket->add('BUDGET_ITEM'); // 5.00
        $this->basket->add('BUDGET_ITEM'); // 5.00
        $this->basket->add('BUDGET_ITEM'); // 5.00
        $this->basket->add('BUDGET_ITEM'); // 5.00
        $this->basket->add('BUDGET_ITEM'); // 5.00
        $this->basket->add('BUDGET_ITEM'); // 5.00

        $productTotal = 8.00 + 6.00 + 2.00 + (5.00 * 10); // 66.00
        $expectedTotal = $productTotal + 2.95; // 68.95 (tier 2)
        $this->assertEquals($expectedTotal, $this->basket->total());
    }

    /**
     * @test
     */
    public function product_identification_and_quantity_tracking(): void
    {
        $this->basket = new Basket($this->catalogue);

        $this->basket->add('APPLE');
        $this->basket->add('APPLE');
        $this->basket->add('BANANA');
        $this->basket->add('RED_WIDGET');

        $this->assertTrue($this->basket->contains('APPLE'));
        $this->assertTrue($this->basket->contains('BANANA'));
        $this->assertTrue($this->basket->contains('RED_WIDGET'));
        $this->assertFalse($this->basket->contains('ORANGE'));

        $this->assertEquals(2, $this->basket->getQuantity('APPLE'));
        $this->assertEquals(1, $this->basket->getQuantity('BANANA'));
        $this->assertEquals(0, $this->basket->getQuantity('ORANGE'));
    }

    /**
     * @test
     */
    public function basket_state_consistency(): void
    {
        $discountOffer = new PercentageDiscountOffer(10.0);
        $this->basket = new Basket($this->catalogue, [], [$discountOffer]);

        // Add items
        $this->basket->add('APPLE');
        $this->basket->add('BANANA');
        
        $initialTotal = $this->basket->total();
        $initialItemCount = $this->basket->itemCount();
        $initialQuantity = $this->basket->totalQuantity();

        // Remove an item
        $this->basket->remove('BANANA');
        
        $this->assertLessThan($initialTotal, $this->basket->total());
        $this->assertEquals($initialItemCount - 1, $this->basket->itemCount());
        $this->assertEquals($initialQuantity - 1, $this->basket->totalQuantity());

        // Clear basket
        $this->basket->clear();
        
        $this->assertEquals(0, $this->basket->itemCount());
        $this->assertEquals(0, $this->basket->totalQuantity());
        $this->assertEquals(0.0, $this->basket->total());
    }
} 