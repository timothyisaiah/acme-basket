<?php

namespace Acme\Basket;

class LegacyBasket
{
    private array $items = [];
    private float $total = 0.0;

    /**
     * Add an item to the basket
     *
     * @param string $name
     * @param float $price
     * @param int $quantity
     * @return void
     */
    public function addItem(string $name, float $price, int $quantity = 1): void
    {
        if (isset($this->items[$name])) {
            $this->items[$name]['quantity'] += $quantity;
        } else {
            $this->items[$name] = [
                'price' => $price,
                'quantity' => $quantity
            ];
        }
        
        $this->calculateTotal();
    }

    /**
     * Remove an item from the basket
     *
     * @param string $name
     * @return bool
     */
    public function removeItem(string $name): bool
    {
        if (isset($this->items[$name])) {
            unset($this->items[$name]);
            $this->calculateTotal();
            return true;
        }
        
        return false;
    }

    /**
     * Get all items in the basket
     *
     * @return array
     */
    public function getItems(): array
    {
        return $this->items;
    }

    /**
     * Get the total price of all items
     *
     * @return float
     */
    public function getTotal(): float
    {
        return $this->total;
    }

    /**
     * Clear all items from the basket
     *
     * @return void
     */
    public function clear(): void
    {
        $this->items = [];
        $this->total = 0.0;
    }

    /**
     * Get the number of items in the basket
     *
     * @return int
     */
    public function getItemCount(): int
    {
        return count($this->items);
    }

    /**
     * Calculate the total price of all items
     *
     * @return void
     */
    private function calculateTotal(): void
    {
        $this->total = 0.0;
        
        foreach ($this->items as $item) {
            $this->total += $item['price'] * $item['quantity'];
        }
    }
} 