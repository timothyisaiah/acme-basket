# Acme Basket

A PHP basket implementation with PSR-4 autoloading using Composer.

## Features

- PSR-4 autoloading with Composer
- Basket class with basic shopping cart functionality
- Comprehensive test suite with PHPUnit
- Add/remove items with quantities
- Calculate totals
- Clear basket functionality
- Docker support with PHP 8.3 CLI and xdebug

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

# Open shell in container
./docker-run.sh shell

# Stop containers
./docker-run.sh stop

# Clean up everything
./docker-run.sh clean
```

## Usage

```php
<?php

require_once 'vendor/autoload.php';

use Acme\Basket\Basket;

$basket = new Basket();

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
./vendor/bin/phpunit
```

Or with coverage:

```bash
./vendor/bin/phpunit --coverage-html coverage
```

### Docker Testing

```bash
docker-compose run --rm test
```

## Project Structure

```
├── composer.json          # Composer configuration with PSR-4 autoloading
├── phpunit.xml           # PHPUnit configuration
├── README.md             # This file
├── Dockerfile            # Docker configuration with PHP 8.3 CLI
├── docker-compose.yml    # Docker Compose services
├── docker-run.sh         # Convenience script for Docker commands
├── .dockerignore         # Docker build exclusions
├── src/                  # Source code
│   └── Basket.php        # Main Basket class
└── tests/                # Test files
    └── BasketTest.php    # PHPUnit tests for Basket class
```

## Requirements

### Local Requirements
- PHP 7.4 or higher
- Composer

### Docker Requirements
- Docker
- Docker Compose

## License

MIT 