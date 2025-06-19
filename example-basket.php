<?php

require_once 'vendor/autoload.php';

use Acme\Basket\Basket;
use Acme\Basket\Product;
use Acme\Basket\Offers\PercentageDiscountOffer;
use Acme\Basket\DeliveryRules\ThresholdDeliveryRule;

echo "=== ACME Basket Domain-Driven Example ===\n\n";

// Create a product catalogue
$catalogue = [
    new Product('APPLE', 'Apple', 1.50),
    new Product('BANANA', 'Banana', 0.75),
    new Product('ORANGE', 'Orange', 2.00),
    new Product('GRAPE', 'Grape', 3.50),
];

echo "Product Catalogue:\n";
foreach ($catalogue as $product) {
    echo "- {$product}: \${$product->getPrice()}\n";
}
echo "\n";

// Create delivery rules and offers (strategy pattern)
$deliveryRule = new ThresholdDeliveryRule(10.0, 2.0); // Free delivery over $10
$discountOffer = new PercentageDiscountOffer(15.0); // 15% discount

echo "Delivery Rule: Free delivery over \$10.00, otherwise \$2.00\n";
echo "Offer: 15% discount on all products\n\n";

// Create basket with dependency injection
$basket = new Basket($catalogue, [$deliveryRule], [$discountOffer]);

echo "=== Adding Products ===\n";

// Add products to basket
$basket->add('APPLE');
echo "Added 1 Apple\n";

$basket->add('BANANA');
echo "Added 1 Banana\n";

$basket->add('BANANA'); // Second banana
echo "Added 1 more Banana\n";

$basket->add('ORANGE');
echo "Added 1 Orange\n";

echo "\n=== Basket Summary ===\n";
echo "Unique products: {$basket->itemCount()}\n";
echo "Total quantity: {$basket->totalQuantity()}\n";
echo "Apple quantity: {$basket->getQuantity('APPLE')}\n";
echo "Banana quantity: {$basket->getQuantity('BANANA')}\n";
echo "Orange quantity: {$basket->getQuantity('ORANGE')}\n";

echo "\n=== Products in Basket ===\n";
$products = $basket->getProducts();
foreach ($products as $product) {
    echo "- {$product->getName()}: \${$product->getPrice()}\n";
}

echo "\n=== Total Calculation ===\n";

// Calculate totals step by step
$originalProducts = $basket->getProducts();
$originalTotal = array_reduce($originalProducts, fn($sum, $p) => $sum + $p->getPrice(), 0.0);

echo "Original total (before discount): \${$originalTotal}\n";

// Apply discount manually to show the process
$discountedProducts = $discountOffer->apply($originalProducts);
$discountedTotal = array_reduce($discountedProducts, fn($sum, $p) => $sum + $p->getPrice(), 0.0);

echo "After 15% discount: \${$discountedTotal}\n";
echo "Discount amount: \$" . ($originalTotal - $discountedTotal) . "\n";

// Calculate delivery
$deliveryCost = $deliveryRule->calculate($discountedProducts);
echo "Delivery cost: \${$deliveryCost}\n";

$finalTotal = $discountedTotal + $deliveryCost;
echo "Final total: \${$finalTotal}\n";

// Compare with basket's total method
$basketTotal = $basket->total();
echo "Basket total method: \${$basketTotal}\n";
echo "Match: " . ($finalTotal === $basketTotal ? 'Yes' : 'No') . "\n";

echo "\n=== Modifying Basket ===\n";

// Remove an item
$basket->remove('BANANA');
echo "Removed 1 Banana\n";
echo "Banana quantity: {$basket->getQuantity('BANANA')}\n";
echo "New total: \${$basket->total()}\n";

// Add more expensive item to trigger free delivery
$basket->add('GRAPE');
echo "Added 1 Grape\n";
echo "New total: \${$basket->total()}\n";

echo "\n=== Final State ===\n";
echo "Unique products: {$basket->itemCount()}\n";
echo "Total quantity: {$basket->totalQuantity()}\n";
echo "Final total: \${$basket->total()}\n";

echo "\n=== Clearing Basket ===\n";
$basket->clear();
echo "Basket cleared\n";
echo "Items: {$basket->itemCount()}\n";
echo "Total: \${$basket->total()}\n";

echo "\n=== Strategy Pattern Demo ===\n";

// Demonstrate different strategies
echo "Creating basket with different delivery rule...\n";
$expensiveDelivery = new ThresholdDeliveryRule(50.0, 10.0); // Expensive delivery
$basket2 = new Basket($catalogue, [$expensiveDelivery], [$discountOffer]);

$basket2->add('APPLE');
$basket2->add('BANANA');

echo "Basket with expensive delivery rule: \${$basket2->total()}\n";

echo "\nCreating basket with no offers...\n";
$basket3 = new Basket($catalogue); // No delivery, no offers

$basket3->add('APPLE');
$basket3->add('BANANA');

echo "Basket with no offers/delivery: \${$basket3->total()}\n"; 