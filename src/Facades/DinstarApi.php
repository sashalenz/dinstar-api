<?php

namespace Sashalenz\DinstarApi\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Sashalenz\DinstarApi\DinstarApi
 */
class DinstarApi extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'dinstar-api';
    }
}
