<?php


namespace App\Facades;

use App\IInvitationCodeGenerator;
use Illuminate\Support\Facades\Facade;

/**
 * @method static string generate()
 */
class InvitationCode extends Facade
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
        return IInvitationCodeGenerator::class;
    }
}
