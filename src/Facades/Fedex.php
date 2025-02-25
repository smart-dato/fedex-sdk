<?php

namespace SmartDato\FedEx\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \SmartDato\FedEx\Fedex
 */
class Fedex extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \SmartDato\FedEx\Fedex::class;
    }
}
