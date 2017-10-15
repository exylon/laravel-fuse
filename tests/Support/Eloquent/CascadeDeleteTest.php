<?php


namespace Tests\Support\Eloquent;


use Tests\Models\Cart;
use Tests\Models\CartItem;
use Tests\Models\Customer;
use Tests\Models\User;
use Tests\TestCase;

class CascadeDeleteTest extends TestCase
{
    protected function setUp()
    {
        parent::setUp();

        $this->loadMigrationsFrom([
            '--database' => 'testing',
            '--realpath' => realpath(__DIR__ . '/../../migrations')
        ]);

        $this->artisan('migrate', ['--database' => 'testing']);
    }

    public function testCascadeDelete()
    {
        $user = Customer::forceCreate([
            'name' => 'Joe Mullen'
        ]);

        $cart = $user->carts()->save(Cart::forceMake([
            'name' => 'Shopping Cart'
        ]));

        $cart->items()->save(CartItem::forceMake([
            'name' => 'Ketchup'
        ]));

        $cart->items()->save(CartItem::forceMake([
            'name' => 'Soy Sauce'
        ]));

        $user->delete();

        $this->assertDatabaseMissing('users', ['name' => 'Joe Mullen']);
        $this->assertDatabaseMissing('carts', ['name' => 'Shopping Cart']);
        $this->assertDatabaseMissing('cart_items', ['name' => 'Ketchup']);
        $this->assertDatabaseMissing('cart_items', ['name' => 'Soy Sauce']);
    }
}
