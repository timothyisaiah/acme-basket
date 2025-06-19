<?php

declare(strict_types=1);

namespace Acme\Basket\Tests\Unit;

use Acme\Basket\Basket;
use Acme\Basket\Product;
use Acme\Basket\DeliveryRules\ThresholdDeliveryRule;
use Acme\Basket\DeliveryRules\TieredDeliveryRule;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for Delivery Rules application
 */
class DeliveryRulesUnitTest extends TestCase
{
    private array $catalogue;

    protected function setUp(): void
    {
        $this->catalogue = [
            new Product('BUDGET_ITEM', 'Budget Item', 5.00),
            new Product('MID_RANGE_ITEM', 'Mid Range Item', 25.00),
            new Product('EXPENSIVE_ITEM', 'Expensive Item', 75.00),
            new Product('LUXURY_ITEM', 'Luxury Item', 100.00),
            new Product('APPLE', 'Apple', 1.50),
            new Product('BANANA', 'Banana', 0.75),
        ];
    }

    /**
     * @test
     */
    public function threshold_delivery_rule_below_threshold(): void
    {
        $deliveryRule = new ThresholdDeliveryRule(10.0, 2.0);
        $basket = new Basket($this->catalogue, [$deliveryRule]);

        $basket->add('APPLE');
        $basket->add('BANANA');

        $productTotal = 1.50 + 0.75; // 2.25
        $expectedTotal = $productTotal + 2.0; // 4.25

        $this->assertEquals($expectedTotal, $basket->total());
    }

    /**
     * @test
     */
    public function threshold_delivery_rule_at_threshold(): void
    {
        $deliveryRule = new ThresholdDeliveryRule(10.0, 2.0);
        $basket = new Basket($this->catalogue, [$deliveryRule]);

        $basket->add('BUDGET_ITEM');
        $basket->add('BUDGET_ITEM');

        $productTotal = 5.00 + 5.00; // 10.00 (exactly at threshold)
        $expectedTotal = $productTotal; // Free delivery

        $this->assertEquals($expectedTotal, $basket->total());
    }

    /**
     * @test
     */
    public function threshold_delivery_rule_above_threshold(): void
    {
        $deliveryRule = new ThresholdDeliveryRule(10.0, 2.0);
        $basket = new Basket($this->catalogue, [$deliveryRule]);

        $basket->add('LUXURY_ITEM');

        $productTotal = 100.00;
        $expectedTotal = $productTotal; // Free delivery

        $this->assertEquals($expectedTotal, $basket->total());
    }

    /**
     * @test
     */
    public function threshold_delivery_rule_with_zero_delivery_cost(): void
    {
        $deliveryRule = new ThresholdDeliveryRule(10.0, 0.0);
        $basket = new Basket($this->catalogue, [$deliveryRule]);

        $basket->add('APPLE');
        $basket->add('BANANA');

        $productTotal = 1.50 + 0.75; // 2.25
        $expectedTotal = $productTotal; // No delivery cost

        $this->assertEquals($expectedTotal, $basket->total());
    }

    /**
     * @test
     */
    public function threshold_delivery_rule_empty_basket(): void
    {
        $deliveryRule = new ThresholdDeliveryRule(10.0, 2.0);
        $basket = new Basket($this->catalogue, [$deliveryRule]);

        $expectedTotal = 2.0; // Delivery cost for empty basket

        $this->assertEquals($expectedTotal, $basket->total());
    }

    /**
     * @test
     */
    public function tiered_delivery_rule_tier_1_below_50(): void
    {
        $deliveryRule = new TieredDeliveryRule();
        $basket = new Basket($this->catalogue, [$deliveryRule]);

        $basket->add('BUDGET_ITEM');
        $basket->add('APPLE');

        $productTotal = 5.00 + 1.50; // 6.50
        $expectedTotal = $productTotal + 4.95; // 11.45

        $this->assertEquals($expectedTotal, $basket->total());
    }

    /**
     * @test
     */
    public function tiered_delivery_rule_tier_1_exactly_50(): void
    {
        $deliveryRule = new TieredDeliveryRule();
        $basket = new Basket($this->catalogue, [$deliveryRule]);

        // Add items to get exactly $50
        $basket->add('BUDGET_ITEM'); // 5.00
        $basket->add('BUDGET_ITEM'); // 5.00
        $basket->add('BUDGET_ITEM'); // 5.00
        $basket->add('BUDGET_ITEM'); // 5.00
        $basket->add('BUDGET_ITEM'); // 5.00
        $basket->add('BUDGET_ITEM'); // 5.00
        $basket->add('BUDGET_ITEM'); // 5.00
        $basket->add('BUDGET_ITEM'); // 5.00
        $basket->add('BUDGET_ITEM'); // 5.00
        $basket->add('BUDGET_ITEM'); // 5.00

        $productTotal = 5.00 * 10; // 50.00
        $expectedTotal = $productTotal + 2.95; // 52.95 (tier 2)

        $this->assertEquals($expectedTotal, $basket->total());
    }

    /**
     * @test
     */
    public function tiered_delivery_rule_tier_2_50_to_89_99(): void
    {
        $deliveryRule = new TieredDeliveryRule();
        $basket = new Basket($this->catalogue, [$deliveryRule]);

        $basket->add('MID_RANGE_ITEM');
        $basket->add('MID_RANGE_ITEM');
        $basket->add('BUDGET_ITEM');

        $productTotal = 25.00 + 25.00 + 5.00; // 55.00
        $expectedTotal = $productTotal + 2.95; // 57.95

        $this->assertEquals($expectedTotal, $basket->total());
    }

    /**
     * @test
     */
    public function tiered_delivery_rule_tier_2_exactly_90(): void
    {
        $deliveryRule = new TieredDeliveryRule();
        $basket = new Basket($this->catalogue, [$deliveryRule]);

        $basket->add('EXPENSIVE_ITEM');
        $basket->add('BUDGET_ITEM');
        $basket->add('BUDGET_ITEM');
        $basket->add('BUDGET_ITEM');

        $productTotal = 75.00 + (5.00 * 3); // 90.00
        $expectedTotal = $productTotal; // Free delivery (tier 3)

        $this->assertEquals($expectedTotal, $basket->total());
    }

    /**
     * @test
     */
    public function tiered_delivery_rule_tier_3_90_and_above(): void
    {
        $deliveryRule = new TieredDeliveryRule();
        $basket = new Basket($this->catalogue, [$deliveryRule]);

        $basket->add('LUXURY_ITEM');

        $productTotal = 100.00;
        $expectedTotal = $productTotal; // Free delivery

        $this->assertEquals($expectedTotal, $basket->total());
    }

    /**
     * @test
     */
    public function tiered_delivery_rule_empty_basket(): void
    {
        $deliveryRule = new TieredDeliveryRule();
        $basket = new Basket($this->catalogue, [$deliveryRule]);

        $expectedTotal = 4.95; // Tier 1 delivery cost for empty basket

        $this->assertEquals($expectedTotal, $basket->total());
    }

    /**
     * @test
     */
    public function tiered_delivery_rule_edge_case_49_99(): void
    {
        $deliveryRule = new TieredDeliveryRule();
        $basket = new Basket($this->catalogue, [$deliveryRule]);

        // Add items to get $49.99
        $basket->add('BUDGET_ITEM'); // 5.00
        $basket->add('BUDGET_ITEM'); // 5.00
        $basket->add('BUDGET_ITEM'); // 5.00
        $basket->add('BUDGET_ITEM'); // 5.00
        $basket->add('BUDGET_ITEM'); // 5.00
        $basket->add('BUDGET_ITEM'); // 5.00
        $basket->add('BUDGET_ITEM'); // 5.00
        $basket->add('BUDGET_ITEM'); // 5.00
        $basket->add('BUDGET_ITEM'); // 5.00
        $basket->add('APPLE'); // 1.50
        $basket->add('BANANA'); // 0.75
        $basket->add('BANANA'); // 0.75
        $basket->add('BANANA'); // 0.75
        $basket->add('BANANA'); // 0.75
        $basket->add('BANANA'); // 0.75
        $basket->add('BANANA'); // 0.75
        $basket->add('BANANA'); // 0.75
        $basket->add('BANANA'); // 0.75
        $basket->add('BANANA'); // 0.75
        $basket->add('BANANA'); // 0.75

        $productTotal = (5.00 * 9) + 1.50 + (0.75 * 10); // 45.00 + 1.50 + 7.50 = 54.00
        $expectedTotal = $productTotal + 2.95; // 56.95 (tier 2)

        $this->assertEquals($expectedTotal, $basket->total());
    }

    /**
     * @test
     */
    public function tiered_delivery_rule_edge_case_89_99(): void
    {
        $deliveryRule = new TieredDeliveryRule();
        $basket = new Basket($this->catalogue, [$deliveryRule]);

        $basket->add('EXPENSIVE_ITEM'); // 75.00
        $basket->add('BUDGET_ITEM'); // 5.00
        $basket->add('BUDGET_ITEM'); // 5.00
        $basket->add('APPLE'); // 1.50
        $basket->add('BANANA'); // 0.75
        $basket->add('BANANA'); // 0.75
        $basket->add('BANANA'); // 0.75
        $basket->add('BANANA'); // 0.75
        $basket->add('BANANA'); // 0.75
        $basket->add('BANANA'); // 0.75
        $basket->add('BANANA'); // 0.75
        $basket->add('BANANA'); // 0.75
        $basket->add('BANANA'); // 0.75
        $basket->add('BANANA'); // 0.75
        $basket->add('BANANA'); // 0.75

        $productTotal = 75.00 + (5.00 * 2) + 1.50 + (0.75 * 10); // 75.00 + 10.00 + 1.50 + 7.50 = 94.00
        $expectedTotal = $productTotal + 2.95; // 96.95 (tier 2)

        $this->assertEqualsWithDelta($expectedTotal, $basket->total(), 0.001);
    }

    /**
     * @test
     */
    public function multiple_delivery_rules(): void
    {
        $thresholdRule = new ThresholdDeliveryRule(50.0, 5.0);
        $tieredRule = new TieredDeliveryRule();
        $basket = new Basket($this->catalogue, [$thresholdRule, $tieredRule]);

        $basket->add('BUDGET_ITEM');
        $basket->add('APPLE');

        // Both rules should apply, but only the first one (threshold) should be used
        $productTotal = 5.00 + 1.50; // 6.50
        $expectedTotal = $productTotal + 5.0; // 11.50 (threshold rule applies)

        $this->assertEquals($expectedTotal, $basket->total());
    }

    /**
     * @test
     */
    public function delivery_rule_with_offers(): void
    {
        $deliveryRule = new TieredDeliveryRule();
        $basket = new Basket($this->catalogue, [$deliveryRule]);

        $basket->add('LUXURY_ITEM');
        $basket->add('APPLE');

        // Product total: 100.00 + 1.50 = 101.50
        // Delivery: Free (>= $90)
        $expectedTotal = 101.50;

        $this->assertEquals($expectedTotal, $basket->total());
    }

    /**
     * @test
     */
    public function delivery_rule_removing_items(): void
    {
        $deliveryRule = new TieredDeliveryRule();
        $basket = new Basket($this->catalogue, [$deliveryRule]);

        $basket->add('LUXURY_ITEM');
        $basket->add('APPLE');

        // Initial: 100.00 + 1.50 = 101.50 (free delivery)
        $this->assertEquals(101.50, $basket->total());

        // Remove luxury item
        $basket->remove('LUXURY_ITEM');

        // After removal: 1.50 + 4.95 = 6.45 (tier 1 delivery)
        $this->assertEquals(6.45, $basket->total());
    }

    /**
     * @test
     */
    public function delivery_rule_clearing_basket(): void
    {
        $deliveryRule = new TieredDeliveryRule();
        $basket = new Basket($this->catalogue, [$deliveryRule]);

        $basket->add('LUXURY_ITEM');
        $basket->add('APPLE');

        $basket->clear();

        // Empty basket should have tier 1 delivery cost
        $this->assertEquals(4.95, $basket->total());
    }

    /**
     * @test
     */
    public function delivery_rule_with_zero_price_products(): void
    {
        $catalogueWithZeroPrice = [
            new Product('FREE_ITEM', 'Free Item', 0.00),
            new Product('PAID_ITEM', 'Paid Item', 10.00),
        ];

        $deliveryRule = new TieredDeliveryRule();
        $basket = new Basket($catalogueWithZeroPrice, [$deliveryRule]);

        $basket->add('FREE_ITEM');
        $basket->add('PAID_ITEM');

        $productTotal = 0.00 + 10.00; // 10.00
        $expectedTotal = $productTotal + 4.95; // 14.95 (tier 1)

        $this->assertEquals($expectedTotal, $basket->total());
    }

    /**
     * @test
     */
    public function delivery_rule_large_quantities(): void
    {
        $deliveryRule = new TieredDeliveryRule();
        $basket = new Basket($this->catalogue, [$deliveryRule]);

        // Add 20 budget items
        for ($i = 0; $i < 20; $i++) {
            $basket->add('BUDGET_ITEM');
        }

        $productTotal = 5.00 * 20; // 100.00
        $expectedTotal = $productTotal; // Free delivery (>= $90)

        $this->assertEquals($expectedTotal, $basket->total());
    }

    /**
     * @test
     */
    public function delivery_rule_state_consistency(): void
    {
        $deliveryRule = new TieredDeliveryRule();
        $basket = new Basket($this->catalogue, [$deliveryRule]);

        // Start with empty basket
        $this->assertEquals(4.95, $basket->total());

        // Add items to reach tier 2
        $basket->add('MID_RANGE_ITEM');
        $basket->add('MID_RANGE_ITEM');
        $this->assertEquals(52.95, $basket->total()); // 50.00 + 2.95

        // Add more to reach tier 3
        $basket->add('EXPENSIVE_ITEM');
        $this->assertEquals(125.00, $basket->total()); // 125.00 (free delivery >= $90)

        // Add more to reach free delivery
        $basket->add('BUDGET_ITEM');
        $basket->add('BUDGET_ITEM');
        $basket->add('BUDGET_ITEM');
        $this->assertEquals(140.00, $basket->total()); // 140.00 (free delivery >= $90)

        // Add luxury item to get free delivery
        $basket->add('LUXURY_ITEM');
        $this->assertEquals(240.00, $basket->total()); // 240.00 (free delivery >= $90)

        // Remove luxury item
        $basket->remove('LUXURY_ITEM');
        $this->assertEquals(140.00, $basket->total()); // Back to 140.00 (free delivery >= $90)
    }
} 