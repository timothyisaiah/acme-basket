<?php

require_once 'vendor/autoload.php';

use Acme\Basket\Basket;

echo "=== Acme Basket Example ===\n\n";

// Create a new basket
$basket = new Basket();

echo "1. Adding items to the basket:\n";
$basket->addItem('Apple', 1.50);
$basket->addItem('Banana', 0.75, 3);
$basket->addItem('Orange', 2.00);

echo "   - Added Apple: \$1.50\n";
echo "   - Added Banana: \$0.75 x 3\n";
echo "   - Added Orange: \$2.00\n\n";

echo "2. Basket contents:\n";
$items = $basket->getItems();
foreach ($items as $name => $item) {
    echo "   - {$name}: \$" . number_format($item['price'], 2) . " x {$item['quantity']}\n";
}

echo "\n3. Basket summary:\n";
echo "   - Total items: " . $basket->getItemCount() . "\n";
echo "   - Total price: \$" . number_format($basket->getTotal(), 2) . "\n\n";

echo "4. Removing an item:\n";
$basket->removeItem('Apple');
echo "   - Removed Apple\n";
echo "   - New total: \$" . number_format($basket->getTotal(), 2) . "\n\n";

echo "5. Clearing the basket:\n";
$basket->clear();
echo "   - Basket cleared\n";
echo "   - Items: " . $basket->getItemCount() . "\n";
echo "   - Total: \$" . number_format($basket->getTotal(), 2) . "\n\n";

echo "=== Example completed ===\n"; 