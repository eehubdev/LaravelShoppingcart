<?php
namespace Gloudemans\Shoppingcart\Facades;

use Illuminate\Support\Facades\Facade;

class Cart extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return 'cart';
    }
}
