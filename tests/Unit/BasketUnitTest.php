<?php

declare(strict_types=1);

namespace Acme\Basket\Tests\Unit;

use Acme\Basket\Basket;
use Acme\Basket\Product;
use Acme\Basket\Offers\PercentageDiscountOffer;
use Acme\Basket\DeliveryRules\TieredDeliveryRule;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for Basket core functionality
 */
class BasketUnitTest extends TestCase
{
    private Basket $basket;
    private array $catalogue;

    protected function setUp(): void
    {
        $this->catalogue = [
            new Product('APPLE', 'Apple', 1.50),
            new Product('BANANA', 'Banana', 0.75),
            new Product('ORANGE', 'Orange', 2.00),
            new Product('RED_WIDGET', 'Red Widget', 10.00),
            new Product('BLUE_WIDGET', 'Blue Widget', 8.00),
        ];
    }

    /**
     * @test
     */
    public function basket_initialization(): void
    {
        $this->basket = new Basket($this->catalogue);

        $this->assertEquals(0, $this->basket->itemCount());
        $this->assertEquals(0, $this->basket->totalQuantity());
        $this->assertEquals(0.0, $this->basket->total());
        $this->assertTrue($this->basket->isEmpty());
    }

    /**
     * @test
     */
    public function adding_single_item(): void
    {
        $this->basket = new Basket($this->catalogue);

        $this->basket->add('APPLE');

        $this->assertEquals(1, $this->basket->itemCount());
        $this->assertEquals(1, $this->basket->totalQuantity());
        $this->assertEquals(1.50, $this->basket->total());
        $this->assertTrue($this->basket->contains('APPLE'));
        $this->assertEquals(1, $this->basket->getQuantity('APPLE'));
    }

    /**
     * @test
     */
    public function adding_multiple_identical_items(): void
    {
        $this->basket = new Basket($this->catalogue);

        $this->basket->add('BANANA');
        $this->basket->add('BANANA');
        $this->basket->add('BANANA');

        $this->assertEquals(1, $this->basket->itemCount());
        $this->assertEquals(3, $this->basket->totalQuantity());
        $this->assertEquals(2.25, $this->basket->total()); // 0.75 * 3
        $this->assertEquals(3, $this->basket->getQuantity('BANANA'));
    }

    /**
     * @test
     */
    public function adding_different_items(): void
    {
        $this->basket = new Basket($this->catalogue);

        $this->basket->add('APPLE');
        $this->basket->add('BANANA');
        $this->basket->add('ORANGE');

        $this->assertEquals(3, $this->basket->itemCount());
        $this->assertEquals(3, $this->basket->totalQuantity());
        $this->assertEquals(4.25, $this->basket->total()); // 1.50 + 0.75 + 2.00
        $this->assertTrue($this->basket->contains('APPLE'));
        $this->assertTrue($this->basket->contains('BANANA'));
        $this->assertTrue($this->basket->contains('ORANGE'));
    }

    /**
     * @test
     */
    public function removing_single_item(): void
    {
        $this->basket = new Basket($this->catalogue);

        $this->basket->add('APPLE');
        $this->basket->add('BANANA');
        $this->basket->remove('APPLE');

        $this->assertEquals(1, $this->basket->itemCount());
        $this->assertEquals(1, $this->basket->totalQuantity());
        $this->assertEquals(0.75, $this->basket->total());
        $this->assertFalse($this->basket->contains('APPLE'));
        $this->assertTrue($this->basket->contains('BANANA'));
    }

    /**
     * @test
     */
    public function removing_item_with_multiple_quantities(): void
    {
        $this->basket = new Basket($this->catalogue);

        $this->basket->add('BANANA');
        $this->basket->add('BANANA');
        $this->basket->add('BANANA');
        $this->basket->remove('BANANA');

        $this->assertEquals(1, $this->basket->itemCount());
        $this->assertEquals(2, $this->basket->totalQuantity());
        $this->assertEquals(1.50, $this->basket->total()); // 0.75 * 2
        $this->assertEquals(2, $this->basket->getQuantity('BANANA'));
    }

    /**
     * @test
     */
    public function removing_nonexistent_item(): void
    {
        $this->basket = new Basket($this->catalogue);

        $this->basket->add('APPLE');
        $this->basket->remove('BANANA'); // Not in basket

        $this->assertEquals(1, $this->basket->itemCount());
        $this->assertEquals(1, $this->basket->totalQuantity());
        $this->assertEquals(1.50, $this->basket->total());
    }

    /**
     * @test
     */
    public function clearing_basket(): void
    {
        $this->basket = new Basket($this->catalogue);

        $this->basket->add('APPLE');
        $this->basket->add('BANANA');
        $this->basket->clear();

        $this->assertEquals(0, $this->basket->itemCount());
        $this->assertEquals(0, $this->basket->totalQuantity());
        $this->assertEquals(0.0, $this->basket->total());
        $this->assertTrue($this->basket->isEmpty());
        $this->assertFalse($this->basket->contains('APPLE'));
        $this->assertFalse($this->basket->contains('BANANA'));
    }

    /**
     * @test
     */
    public function total_calculation_with_multiple_items(): void
    {
        $this->basket = new Basket($this->catalogue);

        $this->basket->add('APPLE');     // 1.50
        $this->basket->add('BANANA');    // 0.75
        $this->basket->add('BANANA');    // 0.75
        $this->basket->add('ORANGE');    // 2.00
        $this->basket->add('RED_WIDGET'); // 10.00

        $expectedTotal = 1.50 + (0.75 * 2) + 2.00 + 10.00; // 15.00
        $this->assertEquals($expectedTotal, $this->basket->total());
    }

    /**
     * @test
     */
    public function quantity_tracking(): void
    {
        $this->basket = new Basket($this->catalogue);

        $this->basket->add('APPLE');
        $this->basket->add('APPLE');
        $this->basket->add('BANANA');
        $this->basket->add('RED_WIDGET');

        $this->assertEquals(2, $this->basket->getQuantity('APPLE'));
        $this->assertEquals(1, $this->basket->getQuantity('BANANA'));
        $this->assertEquals(1, $this->basket->getQuantity('RED_WIDGET'));
        $this->assertEquals(0, $this->basket->getQuantity('ORANGE')); // Not added
    }

    /**
     * @test
     */
    public function item_count_vs_total_quantity(): void
    {
        $this->basket = new Basket($this->catalogue);

        $this->basket->add('APPLE');
        $this->basket->add('APPLE');
        $this->basket->add('BANANA');

        $this->assertEquals(2, $this->basket->itemCount()); // 2 different products
        $this->assertEquals(3, $this->basket->totalQuantity()); // 3 total items
    }

    /**
     * @test
     */
    public function contains_method(): void
    {
        $this->basket = new Basket($this->catalogue);

        $this->assertFalse($this->basket->contains('APPLE'));
        $this->assertFalse($this->basket->contains('NONEXISTENT'));

        $this->basket->add('APPLE');
        $this->assertTrue($this->basket->contains('APPLE'));
        $this->assertFalse($this->basket->contains('BANANA'));
    }

    /**
     * @test
     */
    public function empty_basket_behavior(): void
    {
        $this->basket = new Basket($this->catalogue);

        $this->assertTrue($this->basket->isEmpty());
        $this->assertEquals(0, $this->basket->itemCount());
        $this->assertEquals(0, $this->basket->totalQuantity());
        $this->assertEquals(0.0, $this->basket->total());
        $this->assertFalse($this->basket->contains('APPLE'));
        $this->assertEquals(0, $this->basket->getQuantity('APPLE'));
    }

    /**
     * @test
     */
    public function adding_nonexistent_product(): void
    {
        $this->basket = new Basket($this->catalogue);

        $this->expectException(\InvalidArgumentException::class);
        $this->basket->add('NONEXISTENT_PRODUCT');
    }

    /**
     * @test
     */
    public function basket_state_consistency_after_operations(): void
    {
        $this->basket = new Basket($this->catalogue);

        // Initial state
        $this->assertTrue($this->basket->isEmpty());
        $this->assertEquals(0.0, $this->basket->total());

        // Add items
        $this->basket->add('APPLE');
        $this->basket->add('BANANA');
        $this->assertFalse($this->basket->isEmpty());
        $this->assertEquals(2.25, $this->basket->total());

        // Remove item
        $this->basket->remove('APPLE');
        $this->assertFalse($this->basket->isEmpty());
        $this->assertEquals(0.75, $this->basket->total());

        // Remove last item
        $this->basket->remove('BANANA');
        $this->assertTrue($this->basket->isEmpty());
        $this->assertEquals(0.0, $this->basket->total());
    }

    /**
     * @test
     */
    public function basket_with_offers_only(): void
    {
        $discountOffer = new PercentageDiscountOffer(10.0);
        $this->basket = new Basket($this->catalogue, [], [$discountOffer]);

        $this->basket->add('APPLE');
        $this->basket->add('BANANA');

        $originalTotal = 1.50 + 0.75; // 2.25
        $expectedDiscountedTotal = $originalTotal * 0.9; // 2.025

        $this->assertEqualsWithDelta($expectedDiscountedTotal, $this->basket->total(), 0.001);
    }

    /**
     * @test
     */
    public function basket_with_delivery_only(): void
    {
        $deliveryRule = new TieredDeliveryRule();
        $this->basket = new Basket($this->catalogue, [$deliveryRule]);

        $this->basket->add('APPLE');
        $this->basket->add('BANANA');

        $productTotal = 1.50 + 0.75; // 2.25
        $expectedTotal = $productTotal + 4.95; // 7.20 (tier 1 delivery)

        $this->assertEquals($expectedTotal, $this->basket->total());
    }

    /**
     * @test
     */
    public function basket_with_offers_and_delivery(): void
    {
        $discountOffer = new PercentageDiscountOffer(20.0);
        $deliveryRule = new TieredDeliveryRule();
        $this->basket = new Basket($this->catalogue, [$deliveryRule], [$discountOffer]);

        $this->basket->add('RED_WIDGET');
        $this->basket->add('BLUE_WIDGET');

        // Calculate step by step:
        // 1. Product total: 10.00 + 8.00 = 18.00
        // 2. Apply 20% discount: 18.00 * 0.8 = 14.40
        // 3. Apply delivery (< $50): 14.40 + 4.95 = 19.35
        $expectedTotal = 19.35;

        $this->assertEquals($expectedTotal, $this->basket->total());
    }

    /**
     * @test
     */
    public function basket_total_without_offers_or_delivery(): void
    {
        $this->basket = new Basket($this->catalogue);

        $this->basket->add('APPLE');
        $this->basket->add('BANANA');
        $this->basket->add('ORANGE');

        $expectedTotal = 1.50 + 0.75 + 2.00; // 4.25
        $this->assertEquals($expectedTotal, $this->basket->total());
    }

    /**
     * @test
     */
    public function basket_immutability_of_catalogue(): void
    {
        $this->basket = new Basket($this->catalogue);

        // Modify the original catalogue
        $this->catalogue[] = new Product('NEW_ITEM', 'New Item', 5.00);

        // Try to add the new item - should fail as it's not in the original catalogue
        $this->expectException(\InvalidArgumentException::class);
        $this->basket->add('NEW_ITEM');
    }

    /**
     * @test
     */
    public function basket_handles_zero_price_products(): void
    {
        $catalogueWithZeroPrice = [
            new Product('FREE_ITEM', 'Free Item', 0.00),
            new Product('PAID_ITEM', 'Paid Item', 5.00),
        ];

        $this->basket = new Basket($catalogueWithZeroPrice);

        $this->basket->add('FREE_ITEM');
        $this->basket->add('PAID_ITEM');

        $this->assertEquals(2, $this->basket->itemCount());
        $this->assertEquals(5.00, $this->basket->total());
    }
} 