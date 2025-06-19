<?php

declare(strict_types=1);

namespace Acme\Basket\Tests;

use Acme\Basket\Basket;
use Acme\Basket\Product;
use Acme\Basket\Offers\PercentageDiscountOffer;
use Acme\Basket\DeliveryRules\ThresholdDeliveryRule;
use PHPUnit\Framework\TestCase;

class BasketTest extends TestCase
{
    private Basket $basket;
    private Product $apple;
    private Product $banana;
    private Product $orange;

    protected function setUp(): void
    {
        $this->apple = new Product('APPLE', 'Apple', 1.50);
        $this->banana = new Product('BANANA', 'Banana', 0.75);
        $this->orange = new Product('ORANGE', 'Orange', 2.00);
        
        $catalogue = [$this->apple, $this->banana, $this->orange];
        $this->basket = new Basket($catalogue);
    }

    /**
     * @test
     */
    public function basket_starts_empty(): void
    {
        $this->assertEquals(0, $this->basket->itemCount());
        $this->assertEquals(0, $this->basket->totalQuantity());
        $this->assertEquals(0.0, $this->basket->total());
        $this->assertEmpty($this->basket->getProducts());
    }

    /**
     * @test
     */
    public function can_add_product_by_code(): void
    {
        $this->basket->add('APPLE');
        
        $this->assertEquals(1, $this->basket->itemCount());
        $this->assertEquals(1, $this->basket->totalQuantity());
        $this->assertTrue($this->basket->contains('APPLE'));
        $this->assertEquals(1, $this->basket->getQuantity('APPLE'));
    }

    /**
     * @test
     */
    public function can_add_multiple_products(): void
    {
        $this->basket->add('APPLE');
        $this->basket->add('BANANA');
        
        $this->assertEquals(2, $this->basket->itemCount());
        $this->assertEquals(2, $this->basket->totalQuantity());
        $this->assertTrue($this->basket->contains('APPLE'));
        $this->assertTrue($this->basket->contains('BANANA'));
    }

    /**
     * @test
     */
    public function adding_same_product_increases_quantity(): void
    {
        $this->basket->add('APPLE');
        $this->basket->add('APPLE');
        
        $this->assertEquals(1, $this->basket->itemCount());
        $this->assertEquals(2, $this->basket->totalQuantity());
        $this->assertEquals(2, $this->basket->getQuantity('APPLE'));
    }

    /**
     * @test
     */
    public function throws_exception_for_invalid_product_code(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Product with code 'INVALID' not found in catalogue");
        
        $this->basket->add('INVALID');
    }

    /**
     * @test
     */
    public function can_remove_product(): void
    {
        $this->basket->add('APPLE');
        $this->basket->add('APPLE');
        
        $result = $this->basket->remove('APPLE');
        
        $this->assertTrue($result);
        $this->assertEquals(1, $this->basket->itemCount());
        $this->assertEquals(1, $this->basket->totalQuantity());
        $this->assertEquals(1, $this->basket->getQuantity('APPLE'));
    }

    /**
     * @test
     */
    public function removing_last_item_removes_product(): void
    {
        $this->basket->add('APPLE');
        
        $result = $this->basket->remove('APPLE');
        
        $this->assertTrue($result);
        $this->assertEquals(0, $this->basket->itemCount());
        $this->assertEquals(0, $this->basket->totalQuantity());
        $this->assertFalse($this->basket->contains('APPLE'));
    }

    /**
     * @test
     */
    public function removing_non_existent_product_returns_false(): void
    {
        $result = $this->basket->remove('APPLE');
        
        $this->assertFalse($result);
    }

    /**
     * @test
     */
    public function can_clear_basket(): void
    {
        $this->basket->add('APPLE');
        $this->basket->add('BANANA');
        
        $this->basket->clear();
        
        $this->assertEquals(0, $this->basket->itemCount());
        $this->assertEquals(0, $this->basket->totalQuantity());
        $this->assertEquals(0.0, $this->basket->total());
    }

    /**
     * @test
     */
    public function calculates_total_correctly(): void
    {
        $this->basket->add('APPLE');
        $this->basket->add('BANANA');
        $this->basket->add('BANANA'); // 2 bananas
        
        $expectedTotal = 1.50 + (0.75 * 2);
        $this->assertEquals($expectedTotal, $this->basket->total());
    }

    /**
     * @test
     */
    public function total_returns_zero_for_empty_basket(): void
    {
        $this->assertEquals(0.0, $this->basket->total());
    }

    /**
     * @test
     */
    public function can_get_catalogue(): void
    {
        $catalogue = $this->basket->getCatalogue();
        
        $this->assertCount(3, $catalogue);
        $this->assertContains($this->apple, $catalogue);
        $this->assertContains($this->banana, $catalogue);
        $this->assertContains($this->orange, $catalogue);
    }

    /**
     * @test
     */
    public function can_get_products_in_basket(): void
    {
        $this->basket->add('APPLE');
        $this->basket->add('BANANA');
        $this->basket->add('BANANA'); // 2 bananas
        
        $products = $this->basket->getProducts();
        
        $this->assertCount(3, $products);
        $this->assertEquals($this->apple, $products[0]);
        $this->assertEquals($this->banana, $products[1]);
        $this->assertEquals($this->banana, $products[2]);
    }

    /**
     * @test
     */
    public function applies_offers_to_total(): void
    {
        $offer = new PercentageDiscountOffer(10.0);
        $basket = new Basket([$this->apple, $this->banana], [], [$offer]);
        
        $basket->add('APPLE');
        $basket->add('BANANA');
        
        $originalTotal = 1.50 + 0.75;
        $expectedDiscountedTotal = $originalTotal * 0.9;
        
        $this->assertEqualsWithDelta($expectedDiscountedTotal, $basket->total(), 0.001);
    }

    /**
     * @test
     */
    public function applies_delivery_rules_to_total(): void
    {
        $deliveryRule = new ThresholdDeliveryRule(5.0, 2.0);
        $basket = new Basket([$this->apple, $this->banana], [$deliveryRule]);
        
        $basket->add('APPLE');
        $basket->add('BANANA');
        
        $productTotal = 1.50 + 0.75; // 2.25
        $deliveryCost = 2.0; // Below threshold
        $expectedTotal = $productTotal + $deliveryCost;
        
        $this->assertEquals($expectedTotal, $basket->total());
    }

    /**
     * @test
     */
    public function applies_both_offers_and_delivery_rules(): void
    {
        $offer = new PercentageDiscountOffer(20.0);
        $deliveryRule = new ThresholdDeliveryRule(3.0, 1.0);
        $basket = new Basket([$this->apple, $this->banana], [$deliveryRule], [$offer]);
        
        $basket->add('APPLE');
        $basket->add('BANANA');
        
        $originalTotal = 1.50 + 0.75; // 2.25
        $discountedTotal = $originalTotal * 0.8; // 1.80
        $deliveryCost = 1.0; // Below threshold
        $expectedTotal = $discountedTotal + $deliveryCost;
        
        $this->assertEqualsWithDelta($expectedTotal, $basket->total(), 0.001);
    }

    /**
     * @test
     */
    public function free_delivery_above_threshold(): void
    {
        $deliveryRule = new ThresholdDeliveryRule(2.0, 1.0);
        $basket = new Basket([$this->apple, $this->banana], [$deliveryRule]);
        
        $basket->add('APPLE');
        $basket->add('BANANA');
        
        $productTotal = 1.50 + 0.75; // 2.25
        $deliveryCost = 0.0; // Above threshold
        $expectedTotal = $productTotal + $deliveryCost;
        
        $this->assertEquals($expectedTotal, $basket->total());
    }

    /**
     * @test
     */
    public function throws_exception_for_invalid_catalogue(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Catalogue must contain only Product objects');
        
        new Basket(['invalid']);
    }

    /**
     * @test
     */
    public function throws_exception_for_invalid_delivery_rules(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Delivery rules must contain only DeliveryRule objects');
        
        new Basket([$this->apple], ['invalid']);
    }

    /**
     * @test
     */
    public function throws_exception_for_invalid_offers(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Offers must contain only Offer objects');
        
        new Basket([$this->apple], [], ['invalid']);
    }

    /**
     * @test
     */
    public function handles_multiple_offers(): void
    {
        $offer1 = new PercentageDiscountOffer(10.0);
        $offer2 = new PercentageDiscountOffer(5.0);
        $basket = new Basket([$this->apple], [], [$offer1, $offer2]);
        
        $basket->add('APPLE');
        
        $originalPrice = 1.50;
        $afterFirstDiscount = $originalPrice * 0.9; // 1.35
        $afterSecondDiscount = $afterFirstDiscount * 0.95; // 1.2825
        
        $this->assertEquals($afterSecondDiscount, $basket->total());
    }

    /**
     * @test
     */
    public function integration_test_complex_scenario(): void
    {
        $offer = new PercentageDiscountOffer(15.0);
        $deliveryRule = new ThresholdDeliveryRule(4.0, 2.0);
        $basket = new Basket([$this->apple, $this->banana, $this->orange], [$deliveryRule], [$offer]);
        
        // Add items
        $basket->add('APPLE');
        $basket->add('BANANA');
        $basket->add('BANANA');
        $basket->add('ORANGE');
        
        // Verify quantities
        $this->assertEquals(3, $basket->itemCount());
        $this->assertEquals(4, $basket->totalQuantity());
        $this->assertEquals(1, $basket->getQuantity('APPLE'));
        $this->assertEquals(2, $basket->getQuantity('BANANA'));
        $this->assertEquals(1, $basket->getQuantity('ORANGE'));
        
        // Calculate expected total
        $originalTotal = 1.50 + (0.75 * 2) + 2.00; // 5.00
        $discountedTotal = $originalTotal * 0.85; // 4.25
        $deliveryCost = 0.0; // Above threshold
        $expectedTotal = $discountedTotal + $deliveryCost;
        
        $this->assertEqualsWithDelta($expectedTotal, $basket->total(), 0.001);
        
        // Remove an item
        $basket->remove('BANANA');
        $this->assertEquals(1, $basket->getQuantity('BANANA'));
        
        // Recalculate total
        $newOriginalTotal = 1.50 + 0.75 + 2.00; // 4.25
        $newDiscountedTotal = $newOriginalTotal * 0.85; // 3.6125
        $newDeliveryCost = 2.0; // Below threshold
        $newExpectedTotal = $newDiscountedTotal + $newDeliveryCost;
        
        $this->assertEqualsWithDelta($newExpectedTotal, $basket->total(), 0.001);
    }
} 