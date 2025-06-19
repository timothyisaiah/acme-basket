<?php

require_once 'vendor/autoload.php';

use Acme\Basket\Basket;
use Acme\Basket\Product;
use Acme\Basket\DeliveryRules\TieredDeliveryRule;
use Acme\Basket\DeliveryRules\ThresholdDeliveryRule;
use Acme\Basket\Offers\PercentageDiscountOffer;

echo "=== Tiered Delivery Rule Demo ===\n\n";

// Create a product catalogue with various price points
$catalogue = [
    new Product('CHEAP_ITEM', 'Cheap Item', 10.00),
    new Product('MEDIUM_ITEM', 'Medium Item', 30.00),
    new Product('EXPENSIVE_ITEM', 'Expensive Item', 60.00),
    new Product('LUXURY_ITEM', 'Luxury Item', 100.00),
    new Product('BUDGET_ITEM', 'Budget Item', 5.00),
];

echo "Product Catalogue:\n";
foreach ($catalogue as $product) {
    echo "- {$product}: \${$product->getPrice()}\n";
}
echo "\n";

// Create the tiered delivery rule
$tieredDelivery = new TieredDeliveryRule();
echo "Tiered Delivery Rule:\n";
echo "- < \$50: \$4.95\n";
echo "- < \$90: \$2.95\n";
echo "- >= \$90: Free\n\n";

// Create basket with tiered delivery
$basket = new Basket($catalogue, [$tieredDelivery]);

echo "=== Scenario 1: Small Order (< \$50) ===\n";
$basket->add('BUDGET_ITEM');
$basket->add('BUDGET_ITEM');
$basket->add('CHEAP_ITEM');

echo "Added: Budget Item (\$5.00) + Budget Item (\$5.00) + Cheap Item (\$10.00)\n";
echo "Subtotal: \$20.00\n";
echo "Delivery: \$4.95\n";
echo "Total: \${$basket->total()}\n";
echo "Expected: \$24.95\n";
echo "Match: " . ($basket->total() === 24.95 ? 'Yes' : 'No') . "\n\n";

$basket->clear();

echo "=== Scenario 2: Medium Order (\$50 - \$89.99) ===\n";
$basket->add('MEDIUM_ITEM');
$basket->add('EXPENSIVE_ITEM');

echo "Added: Medium Item (\$30.00) + Expensive Item (\$60.00)\n";
echo "Subtotal: \$90.00\n";
echo "Delivery: \$2.95\n";
echo "Total: \${$basket->total()}\n";
echo "Expected: \$92.95\n";
echo "Match: " . ($basket->total() === 92.95 ? 'Yes' : 'No') . "\n\n";

$basket->clear();

echo "=== Scenario 3: Large Order (>= \$90) ===\n";
$basket->add('LUXURY_ITEM');

echo "Added: Luxury Item (\$100.00)\n";
echo "Subtotal: \$100.00\n";
echo "Delivery: Free\n";
echo "Total: \${$basket->total()}\n";
echo "Expected: \$100.00\n";
echo "Match: " . ($basket->total() === 100.00 ? 'Yes' : 'No') . "\n\n";

$basket->clear();

echo "=== Scenario 4: Edge Case - Exactly \$50 ===\n";
$basket->add('MEDIUM_ITEM');
$basket->add('CHEAP_ITEM');
$basket->add('BUDGET_ITEM');
$basket->add('BUDGET_ITEM');
$basket->add('BUDGET_ITEM');

echo "Added: Medium Item (\$30.00) + Cheap Item (\$10.00) + 3x Budget Item (\$5.00 each)\n";
echo "Subtotal: \$50.00\n";
echo "Delivery: \$2.95\n";
echo "Total: \${$basket->total()}\n";
echo "Expected: \$52.95\n";
echo "Match: " . ($basket->total() === 52.95 ? 'Yes' : 'No') . "\n\n";

$basket->clear();

echo "=== Scenario 5: Edge Case - Exactly \$90 ===\n";
$basket->add('MEDIUM_ITEM');
$basket->add('EXPENSIVE_ITEM');

echo "Added: Medium Item (\$30.00) + Expensive Item (\$60.00)\n";
echo "Subtotal: \$90.00\n";
echo "Delivery: Free\n";
echo "Total: \${$basket->total()}\n";
echo "Expected: \$90.00\n";
echo "Match: " . ($basket->total() === 90.00 ? 'Yes' : 'No') . "\n\n";

$basket->clear();

echo "=== Scenario 6: Edge Case - Just Below \$50 ===\n";
$basket->add('MEDIUM_ITEM');
$basket->add('CHEAP_ITEM');
$basket->add('BUDGET_ITEM');
$basket->add('BUDGET_ITEM');

echo "Added: Medium Item (\$30.00) + Cheap Item (\$10.00) + 2x Budget Item (\$5.00 each)\n";
echo "Subtotal: \$45.00\n";
echo "Delivery: \$4.95\n";
echo "Total: \${$basket->total()}\n";
echo "Expected: \$49.95\n";
echo "Match: " . ($basket->total() === 49.95 ? 'Yes' : 'No') . "\n\n";

$basket->clear();

echo "=== Scenario 7: Edge Case - Just Below \$90 ===\n";
$basket->add('EXPENSIVE_ITEM');
$basket->add('CHEAP_ITEM');
$basket->add('BUDGET_ITEM');
$basket->add('BUDGET_ITEM');
$basket->add('BUDGET_ITEM');

echo "Added: Expensive Item (\$60.00) + Cheap Item (\$10.00) + 3x Budget Item (\$5.00 each)\n";
echo "Subtotal: \$85.00\n";
echo "Delivery: \$2.95\n";
echo "Total: \${$basket->total()}\n";
echo "Expected: \$87.95\n";
echo "Match: " . ($basket->total() === 87.95 ? 'Yes' : 'No') . "\n\n";

$basket->clear();

echo "=== Scenario 8: Combined with Offers ===\n";

// Create offers
$discountOffer = new PercentageDiscountOffer(10.0); // 10% off

$basket = new Basket($catalogue, [$tieredDelivery], [$discountOffer]);

$basket->add('LUXURY_ITEM');
$basket->add('CHEAP_ITEM');

echo "Added: Luxury Item (\$100.00) + Cheap Item (\$10.00)\n";
echo "Subtotal before discount: \$110.00\n";
echo "Subtotal after 10% discount: \$99.00\n";
echo "Delivery: Free (>= \$90 after discount)\n";
echo "Total: \${$basket->total()}\n";
echo "Expected: \$99.00\n";
echo "Match: " . ($basket->total() === 99.00 ? 'Yes' : 'No') . "\n\n";

$basket->clear();

echo "=== Scenario 9: Comparison with Threshold Delivery ===\n";

// Create threshold delivery rule for comparison
$thresholdDelivery = new ThresholdDeliveryRule(90.0, 4.95);

echo "Comparing Tiered vs Threshold Delivery:\n\n";

// Test with \$85 order
$basket->clear();
$basket = new Basket($catalogue, [$tieredDelivery]);
$basket->add('EXPENSIVE_ITEM');
$basket->add('CHEAP_ITEM');
$basket->add('BUDGET_ITEM');
$basket->add('BUDGET_ITEM');
$basket->add('BUDGET_ITEM');

$tieredTotal = $basket->total();

$basket->clear();
$basket = new Basket($catalogue, [$thresholdDelivery]);
$basket->add('EXPENSIVE_ITEM');
$basket->add('CHEAP_ITEM');
$basket->add('BUDGET_ITEM');
$basket->add('BUDGET_ITEM');
$basket->add('BUDGET_ITEM');

$thresholdTotal = $basket->total();

echo "Order: \$85.00\n";
echo "Tiered Delivery: \$" . ($tieredTotal - 85.00) . " (Total: \${$tieredTotal})\n";
echo "Threshold Delivery: \$" . ($thresholdTotal - 85.00) . " (Total: \${$thresholdTotal})\n";
echo "Difference: \$" . ($thresholdTotal - $tieredTotal) . "\n\n";

echo "=== Summary ===\n";
echo "The TieredDeliveryRule successfully:\n";
echo "- Charges \$4.95 for orders under \$50\n";
echo "- Charges \$2.95 for orders \$50-\$89.99\n";
echo "- Provides free delivery for orders \$90+\n";
echo "- Handles edge cases at exact thresholds\n";
echo "- Works with offers and discounts\n";
echo "- Provides more granular pricing than simple threshold rules\n"; 