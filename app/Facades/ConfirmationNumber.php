<?php


namespace App\Facades;

use App\IConfirmationNumberGenerator;
use Illuminate\Support\Facades\Facade;

/**
 * @method static string generate()
 */
class ConfirmationNumber extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     *
     * @throws \RuntimeException
     */
    protected static function getFacadeAccessor()
    {
        return IConfirmationNumberGenerator::class;
    }
}
