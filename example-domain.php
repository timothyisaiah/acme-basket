<?php

require_once 'vendor/autoload.php';

use Acme\Basket\Product;
use Acme\Basket\Offers\PercentageDiscountOffer;
use Acme\Basket\DeliveryRules\ThresholdDeliveryRule;

echo "=== ACME Basket Domain Types Example ===\n\n";

// Create some products
$apple = new Product('APPLE', 'Apple', 1.50);
$banana = new Product('BANANA', 'Banana', 0.75);
$orange = new Product('ORANGE', 'Orange', 2.00);

echo "Products created:\n";
echo "- {$apple}\n";
echo "- {$banana}\n";
echo "- {$orange}\n\n";

// Create an offer (10% discount)
$discountOffer = new PercentageDiscountOffer(10.0);
echo "Created 10% discount offer\n\n";

// Apply the offer to products
$products = [$apple, $banana, $orange];
$discountedProducts = $discountOffer->apply($products);

echo "After applying 10% discount:\n";
foreach ($discountedProducts as $product) {
    echo "- {$product->getName()}: \${$product->getPrice()}\n";
}

// Calculate total before discount
$totalBeforeDiscount = array_reduce($products, fn($sum, $p) => $sum + $p->getPrice(), 0.0);
echo "\nTotal before discount: \${$totalBeforeDiscount}\n";

// Calculate total after discount
$totalAfterDiscount = array_reduce($discountedProducts, fn($sum, $p) => $sum + $p->getPrice(), 0.0);
echo "Total after discount: \${$totalAfterDiscount}\n";
echo "Discount amount: \$" . ($totalBeforeDiscount - $totalAfterDiscount) . "\n\n";

// Create a delivery rule (free delivery over $5.00, otherwise $2.00)
$deliveryRule = new ThresholdDeliveryRule(5.00, 2.00);
echo "Created delivery rule: Free delivery over \$5.00, otherwise \$2.00\n\n";

// Calculate delivery cost for original products
$deliveryCost = $deliveryRule->calculate($products);
echo "Delivery cost for original products: \${$deliveryCost}\n";

// Calculate delivery cost for discounted products
$discountedDeliveryCost = $deliveryRule->calculate($discountedProducts);
echo "Delivery cost for discounted products: \${$discountedDeliveryCost}\n\n";

// Show final totals
echo "=== Final Calculation ===\n";
echo "Original total: \${$totalBeforeDiscount}\n";
echo "Discounted total: \${$totalAfterDiscount}\n";
echo "Delivery cost: \${$discountedDeliveryCost}\n";
echo "Final total: \$" . ($totalAfterDiscount + $discountedDeliveryCost) . "\n";

// Demonstrate immutability
echo "\n=== Immutability Demo ===\n";
echo "Original apple price: \${$apple->getPrice()}\n";
echo "Discounted apple price: \${$discountedProducts[0]->getPrice()}\n";
echo "Original apple unchanged: \${$apple->getPrice()}\n";

// Demonstrate equality
echo "\n=== Equality Demo ===\n";
$sameApple = new Product('APPLE', 'Different Apple', 2.00);
echo "Apple equals same code product: " . ($apple->equals($sameApple) ? 'Yes' : 'No') . "\n";
echo "Apple equals different code product: " . ($apple->equals($banana) ? 'Yes' : 'No') . "\n"; 