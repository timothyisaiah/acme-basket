# Acme Basket

A PHP basket implementation with PSR-4 autoloading using Composer.

## Features

- PSR-4 autoloading with Composer
- Domain-driven design with immutable value objects
- Dependency injection and strategy patterns
- Basket class with product catalogue, delivery rules, and offers
- Comprehensive test suite with PHPUnit
- Static analysis with PHPStan
- Add/remove items with quantities
- Calculate totals with discounts and delivery
- Clear basket functionality
- Docker support with PHP 8.3 CLI and xdebug

## Core Domain Types

The application implements a domain-driven design with the following core types:

### Product (Value Object)
Immutable value object representing a product with code, name, and price.

```php
use Acme\Basket\Product;

$apple = new Product('APPLE', 'Apple', 1.50);
echo $apple->getCode(); // 'APPLE'
echo $apple->getName(); // 'Apple'
echo $apple->getPrice(); // 1.50
echo $apple; // 'Apple (APPLE)'
```

### Basket (Domain Service)
Domain-driven basket implementation using dependency injection and strategy patterns.

```php
use Acme\Basket\Basket;
use Acme\Basket\Product;
use Acme\Basket\Offers\PercentageDiscountOffer;
use Acme\Basket\DeliveryRules\ThresholdDeliveryRule;

// Create catalogue
$catalogue = [
    new Product('APPLE', 'Apple', 1.50),
    new Product('BANANA', 'Banana', 0.75),
];

// Create strategies
$deliveryRule = new ThresholdDeliveryRule(10.0, 2.0);
$offer = new PercentageDiscountOffer(15.0);

// Create basket with dependency injection
$basket = new Basket($catalogue, [$deliveryRule], [$offer]);

// Use basket
$basket->add('APPLE');
$basket->add('BANANA');
$total = $basket->total(); // Includes discounts and delivery
```

### Offer (Interface)
Interface for applying offers to products.

```php
use Acme\Basket\Offer;

interface Offer
{
    public function apply(array $products): array;
}
```

### DeliveryRule (Interface)
Interface for calculating delivery costs.

```php
use Acme\Basket\DeliveryRule;

interface DeliveryRule
{
    public function calculate(array $products): float;
}
```

### Example Implementations

#### PercentageDiscountOffer
Applies a percentage discount to all products.

```php
use Acme\Basket\Offers\PercentageDiscountOffer;

$offer = new PercentageDiscountOffer(10.0); // 10% discount
$discountedProducts = $offer->apply($products);
```

#### BuyOneGetHalfOffRedWidgetOffer
Applies "Buy one red widget, get the second for half price" offer.

```php
use Acme\Basket\Offers\BuyOneGetHalfOffRedWidgetOffer;

$offer = new BuyOneGetHalfOffRedWidgetOffer();
$discountedProducts = $offer->apply($products);

// Only affects products with both "red" and "widget" in the name
// First red widget: full price
// Second red widget: half price
// Third and subsequent: full price
```

#### ThresholdDeliveryRule
Provides free delivery above a certain threshold.

```php
use Acme\Basket\DeliveryRules\ThresholdDeliveryRule;

$rule = new ThresholdDeliveryRule(50.0, 5.0); // Free delivery over $50
$deliveryCost = $rule->calculate($products);
```

## Installation

### Local Installation

1. Clone the repository
2. Install dependencies:
   ```bash
   composer install
   ```

### Docker Installation

The project includes Docker support with PHP 8.3 CLI, Composer, and xdebug enabled.

1. Build the Docker images:
   ```bash
   docker-compose build
   ```

2. Run the application:
   ```bash
   docker-compose up app
   ```
   The application will be available at `http://localhost:8000`

3. Run tests:
   ```bash
   docker-compose run --rm test
   ```

4. Run static analysis:
   ```bash
   docker-compose run --rm phpstan
   ```

5. Run quality checks (tests + static analysis):
   ```bash
   docker-compose run --rm quality
   ```

#### Using the Docker Script

For convenience, you can use the provided script:

```bash
# Make the script executable (Linux/Mac)
chmod +x docker-run.sh

# Build images
./docker-run.sh build

# Start application
./docker-run.sh start

# Run tests
./docker-run.sh test

# Run static analysis
./docker-run.sh phpstan

# Run quality checks
./docker-run.sh quality

# Open shell in container
./docker-run.sh shell

# Stop containers
./docker-run.sh stop

# Clean up everything
./docker-run.sh clean
```

## Usage

### Domain-Driven Basket Usage

```php
<?php

require_once 'vendor/autoload.php';

use Acme\Basket\Basket;
use Acme\Basket\Product;
use Acme\Basket\Offers\PercentageDiscountOffer;
use Acme\Basket\Offers\BuyOneGetHalfOffRedWidgetOffer;
use Acme\Basket\DeliveryRules\ThresholdDeliveryRule;

// Create product catalogue
$catalogue = [
    new Product('APPLE', 'Apple', 1.50),
    new Product('BANANA', 'Banana', 0.75),
    new Product('RED_WIDGET', 'Red Widget', 10.00),
    new Product('BLUE_WIDGET', 'Blue Widget', 8.00),
];

// Create delivery and offer strategies
$deliveryRule = new ThresholdDeliveryRule(10.0, 2.0);
$discountOffer = new PercentageDiscountOffer(15.0);
$redWidgetOffer = new BuyOneGetHalfOffRedWidgetOffer();

// Create basket with dependency injection
$basket = new Basket($catalogue, [$deliveryRule], [$discountOffer, $redWidgetOffer]);

// Add products by code
$basket->add('APPLE');
$basket->add('RED_WIDGET');
$basket->add('RED_WIDGET'); // Second red widget gets half price
$basket->add('BANANA');

// Get basket information
echo "Total: $" . $basket->total() . "\n"; // Includes all discounts and delivery
echo "Item count: " . $basket->itemCount() . "\n";
echo "Total quantity: " . $basket->totalQuantity() . "\n";

// Remove items
$basket->remove('BANANA');

// Clear basket
$basket->clear();

// Run examples
php example-basket.php
php example-red-widget-offer.php
php example-domain.php
```

### Legacy Basket Usage

The original basket implementation is still available as `LegacyBasket`:

```php
<?php

require_once 'vendor/autoload.php';

use Acme\Basket\LegacyBasket;

$basket = new LegacyBasket();

// Add items to the basket
$basket->addItem('Apple', 1.50);
$basket->addItem('Banana', 0.75, 3);

// Get basket information
echo "Total: $" . $basket->getTotal() . "\n";
echo "Item count: " . $basket->getItemCount() . "\n";

// Remove an item
$basket->removeItem('Apple');

// Clear the basket
$basket->clear();
```

## Testing

### Local Testing

Run the test suite:

```bash
composer test
```

Or with coverage:

```bash
composer test:coverage
```

### Docker Testing

```bash
docker-compose run --rm test
```

## Static Analysis

### Local PHPStan

Run static analysis:

```bash
composer phpstan
```

Generate baseline (to ignore existing errors):

```bash
composer phpstan:baseline
```

### Docker PHPStan

```bash
docker-compose run --rm phpstan
```

## Quality Checks

Run both tests and static analysis:

### Local

```bash
composer quality
```

### Docker

```bash
docker-compose run --rm quality
```

## Available Composer Scripts

- `composer test` - Run PHPUnit tests
- `composer test:coverage` - Run tests with HTML coverage report
- `composer test:coverage-text` - Run tests with text coverage report
- `composer phpstan` - Run PHPStan static analysis
- `composer phpstan:baseline` - Generate PHPStan baseline
- `composer check` - Run PHPStan and tests
- `composer quality` - Run PHPStan and tests with coverage

## Project Structure

```
├── composer.json          # Composer configuration with PSR-4 autoloading
├── phpunit.xml           # PHPUnit configuration
├── phpstan.neon          # PHPStan configuration
├── README.md             # This file
├── example-basket.php    # Example usage of domain-driven Basket
├── example-red-widget-offer.php # Example of red widget offer
├── example-domain.php    # Example usage of domain types
├── Dockerfile            # Docker configuration with PHP 8.3 CLI
├── docker-compose.yml    # Docker Compose services
├── docker-run.sh         # Convenience script for Docker commands
├── .dockerignore         # Docker build exclusions
├── src/                  # Source code
│   ├── Basket.php        # Domain-driven Basket class
│   ├── LegacyBasket.php  # Original basket implementation
│   ├── Product.php       # Product value object
│   ├── Offer.php         # Offer interface
│   ├── DeliveryRule.php  # DeliveryRule interface
│   ├── Offers/           # Offer implementations
│   │   ├── PercentageDiscountOffer.php
│   │   └── BuyOneGetHalfOffRedWidgetOffer.php
│   └── DeliveryRules/    # DeliveryRule implementations
│       └── ThresholdDeliveryRule.php
└── tests/                # Test files
    ├── BasketTest.php    # PHPUnit tests for domain-driven Basket
    ├── ProductTest.php   # Tests for Product value object
    ├── Offers/           # Tests for offer implementations
    │   ├── PercentageDiscountOfferTest.php
    │   └── BuyOneGetHalfOffRedWidgetOfferTest.php
    └── DeliveryRules/    # Tests for delivery rule implementations
        └── ThresholdDeliveryRuleTest.php
```

## Design Patterns Used

### Dependency Injection
- Basket constructor accepts catalogue, delivery rules, and offers
- Dependencies are injected rather than created internally
- Enables easy testing and configuration

### Strategy Pattern
- `Offer` and `DeliveryRule` interfaces define strategies
- Different implementations can be swapped easily
- Multiple strategies can be applied (e.g., multiple offers)

### Value Objects
- `Product` is an immutable value object
- Equality based on business identity (product code)
- Self-validating with constructor validation

### Domain-Driven Design
- Clear separation of domain concepts
- Rich domain model with business logic
- Ubiquitous language in method names

## Requirements

### Local Requirements
- PHP 7.4 or higher
- Composer

### Docker Requirements
- Docker
- Docker Compose

## License

MIT 