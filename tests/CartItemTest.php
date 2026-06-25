<?php

namespace Gloudemans\Tests\Shoppingcart;

use Orchestra\Testbench\TestCase;
use PHPUnit\Framework\Attributes\Test;
use Gloudemans\Shoppingcart\CartItem;
use Gloudemans\Shoppingcart\ShoppingcartServiceProvider;
use Gloudemans\Tests\Shoppingcart\Fixtures\BuyableProduct;

class CartItemTest extends TestCase
{
    /**
     * Set the package service provider.
     *
     * @param \Illuminate\Foundation\Application $app
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return [ShoppingcartServiceProvider::class];
    }

    #[Test]
    public function it_can_be_cast_to_an_array()
    {
        $cartItem = new CartItem(1, 'Some item', 10.00, ['size' => 'XL', 'color' => 'red']);
        $cartItem->setQuantity(2);

        $this->assertEquals([
            'id' => 1,
            'name' => 'Some item',
            'price' => 10.00,
            'rowId' => '07d5da5550494c62daf9993cf954303f',
            'qty' => 2,
            'options' => [
                'size' => 'XL',
                'color' => 'red'
            ],
            'tax' => 0.0,
            'subtotal' => 20.00,
            'isSaved' => false
        ], $cartItem->toArray());
    }

    #[Test]
    public function it_can_be_cast_to_json()
    {
        $cartItem = new CartItem(1, 'Some item', 10.00, ['size' => 'XL', 'color' => 'red']);
        $cartItem->setQuantity(2);

        $this->assertJson($cartItem->toJson());

        $json = '{"rowId":"07d5da5550494c62daf9993cf954303f","id":1,"name":"Some item","qty":2,"price":10,"options":{"size":"XL","color":"red"},"tax":"0.00","isSaved":false,"subtotal":"20.00"}';

        $this->assertEquals($json, $cartItem->toJson());
    }

    #[Test]
    public function it_can_be_marked_as_saved_for_later()
    {
        $cartItem = new CartItem(1, 'Some item', 10.00);

        $this->assertFalse($cartItem->toArray()['isSaved']);

        $cartItem->setSaved(true);

        $this->assertTrue($cartItem->toArray()['isSaved']);
    }

    #[Test]
    public function updating_from_a_buyable_replaces_the_computed_priceTax_with_a_raw_value()
    {
        $cartItem = new CartItem(1, 'Some item', 10.00);
        $cartItem->setTaxRate(21);

        // Before any update, priceTax is a virtual attribute: __get computes
        // and formats it as a string.
        $this->assertSame('12.10', $cartItem->priceTax);

        $cartItem->updateFromBuyable(new BuyableProduct(1, 'Updated item', 20.00));

        $this->assertSame(20.0, $cartItem->price);

        // tax has no backing property, so __get still computes and formats it.
        $this->assertSame('4.20', $cartItem->tax);

        // Known quirk: updateFromBuyable() assigns a *dynamic* priceTax property
        // which then shadows the __get accessor, so priceTax now returns a raw
        // float (price + tax) instead of a formatted string. This pins the
        // current behaviour rather than endorsing it.
        $this->assertIsFloat($cartItem->priceTax);
        $this->assertEqualsWithDelta(24.2, $cartItem->priceTax, 0.001);
    }
}
