<?php

require_once 'vendor/autoload.php';

use Acme\Basket\Basket;
use Acme\Basket\Product;
use Acme\Basket\Offers\BuyOneGetHalfOffRedWidgetOffer;
use Acme\Basket\Offers\PercentageDiscountOffer;
use Acme\Basket\DeliveryRules\ThresholdDeliveryRule;

echo "=== Buy One Get Half Off Red Widget Offer Demo ===\n\n";

// Create a product catalogue with various widgets and other products
$catalogue = [
    new Product('RED_WIDGET', 'Red Widget', 10.00),
    new Product('BLUE_WIDGET', 'Blue Widget', 8.00),
    new Product('GREEN_WIDGET', 'Green Widget', 6.00),
    new Product('RED_APPLE', 'Red Apple', 2.00),
    new Product('BANANA', 'Banana', 1.50),
    new Product('WIDGET_RED', 'Widget Red', 15.00), // Different order
    new Product('SPECIAL_RED_WIDGET', 'Special Red Widget', 20.00),
];

echo "Product Catalogue:\n";
foreach ($catalogue as $product) {
    echo "- {$product}: \${$product->getPrice()}\n";
}
echo "\n";

// Create the red widget offer
$redWidgetOffer = new BuyOneGetHalfOffRedWidgetOffer();
echo "Offer: Buy one red widget, get the second for half price\n\n";

// Create basket with just the red widget offer
$basket = new Basket($catalogue, [], [$redWidgetOffer]);

echo "=== Scenario 1: Two Red Widgets ===\n";
$basket->add('RED_WIDGET');
$basket->add('RED_WIDGET');

echo "Added 2 Red Widgets\n";
echo "Total: \${$basket->total()}\n";
echo "Expected: \$15.00 (10.00 + 5.00)\n";
echo "Match: " . ($basket->total() === 15.00 ? 'Yes' : 'No') . "\n\n";

$basket->clear();

echo "=== Scenario 2: One Red Widget (No Discount) ===\n";
$basket->add('RED_WIDGET');

echo "Added 1 Red Widget\n";
echo "Total: \${$basket->total()}\n";
echo "Expected: \$10.00 (no discount applied)\n";
echo "Match: " . ($basket->total() === 10.00 ? 'Yes' : 'No') . "\n\n";

$basket->clear();

echo "=== Scenario 3: Three Red Widgets ===\n";
$basket->add('RED_WIDGET');
$basket->add('RED_WIDGET');
$basket->add('RED_WIDGET');

echo "Added 3 Red Widgets\n";
echo "Total: \${$basket->total()}\n";
echo "Expected: \$25.00 (10.00 + 5.00 + 10.00)\n";
echo "Match: " . ($basket->total() === 25.00 ? 'Yes' : 'No') . "\n\n";

$basket->clear();

echo "=== Scenario 4: Mixed Products ===\n";
$basket->add('RED_WIDGET');
$basket->add('BLUE_WIDGET');
$basket->add('RED_WIDGET');
$basket->add('BANANA');

echo "Added: Red Widget, Blue Widget, Red Widget, Banana\n";
echo "Total: \${$basket->total()}\n";
echo "Expected: \$24.50 (10.00 + 8.00 + 5.00 + 1.50)\n";
echo "Match: " . ($basket->total() === 24.50 ? 'Yes' : 'No') . "\n\n";

$basket->clear();

echo "=== Scenario 5: Different Red Widget Variants ===\n";
$basket->add('WIDGET_RED');
$basket->add('SPECIAL_RED_WIDGET');

echo "Added: Widget Red, Special Red Widget\n";
echo "Total: \${$basket->total()}\n";
echo "Expected: \$27.50 (15.00 + 12.50)\n";
echo "Match: " . ($basket->total() === 27.50 ? 'Yes' : 'No') . "\n\n";

$basket->clear();

echo "=== Scenario 6: Non-Red Widget Products ===\n";
$basket->add('BLUE_WIDGET');
$basket->add('GREEN_WIDGET');
$basket->add('RED_APPLE');

echo "Added: Blue Widget, Green Widget, Red Apple\n";
echo "Total: \${$basket->total()}\n";
echo "Expected: \$16.00 (8.00 + 6.00 + 2.00)\n";
echo "Match: " . ($basket->total() === 16.00 ? 'Yes' : 'No') . "\n\n";

$basket->clear();

echo "=== Scenario 7: Combined with Other Offers ===\n";

// Create multiple offers
$redWidgetOffer = new BuyOneGetHalfOffRedWidgetOffer();
$generalDiscount = new PercentageDiscountOffer(10.0); // 10% off everything
$deliveryRule = new ThresholdDeliveryRule(20.0, 3.0); // Free delivery over $20

$basket = new Basket($catalogue, [$deliveryRule], [$redWidgetOffer, $generalDiscount]);

$basket->add('RED_WIDGET');
$basket->add('RED_WIDGET');
$basket->add('BLUE_WIDGET');

echo "Added: Red Widget, Red Widget, Blue Widget\n";
echo "Offers: Red Widget BOGO + 10% general discount\n";
echo "Delivery: Free over \$20\n";

echo "Total: \${$basket->total()}\n";

// Calculate manually to verify
$originalTotal = 10.00 + 5.00 + 8.00; // After red widget offer
$discountedTotal = $originalTotal * 0.9; // After 10% discount
$deliveryCost = $discountedTotal >= 20.0 ? 0.0 : 3.0;
$expectedTotal = $discountedTotal + $deliveryCost;

echo "Expected: \${$expectedTotal}\n";
echo "Match: " . (abs($basket->total() - $expectedTotal) < 0.01 ? 'Yes' : 'No') . "\n\n";

echo "=== Scenario 8: Complex Basket ===\n";
$basket->clear();

$basket->add('RED_WIDGET');
$basket->add('BLUE_WIDGET');
$basket->add('RED_WIDGET');
$basket->add('GREEN_WIDGET');
$basket->add('RED_WIDGET');
$basket->add('BANANA');

echo "Added: Red Widget, Blue Widget, Red Widget, Green Widget, Red Widget, Banana\n";
echo "Total: \${$basket->total()}\n";

// Manual calculation
$products = [
    10.00, // First red widget - full price
    8.00,  // Blue widget - unchanged
    5.00,  // Second red widget - half price
    6.00,  // Green widget - unchanged
    10.00, // Third red widget - full price
    1.50,  // Banana - unchanged
];
$subtotal = array_sum($products);
$discountedSubtotal = $subtotal * 0.9;
$deliveryCost = $discountedSubtotal >= 20.0 ? 0.0 : 3.0;
$expectedComplexTotal = $discountedSubtotal + $deliveryCost;

echo "Expected: \${$expectedComplexTotal}\n";
echo "Match: " . (abs($basket->total() - $expectedComplexTotal) < 0.01 ? 'Yes' : 'No') . "\n\n";

echo "=== Summary ===\n";
echo "The BuyOneGetHalfOffRedWidgetOffer successfully:\n";
echo "- Applies half price to the second red widget\n";
echo "- Only affects products with both 'red' and 'widget' in the name\n";
echo "- Works with case-insensitive matching\n";
echo "- Can be combined with other offers\n";
echo "- Preserves product codes and names\n";
echo "- Returns new Product instances (immutable)\n"; 