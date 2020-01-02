<?php


namespace App\Facades;

use App\ITicketCodeGenerator;
use App\Ticket;
use Illuminate\Support\Facades\Facade;

/**
 * @method static string generateFor(Ticket $ticket)
 */
class TicketCode extends Facade
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
        return ITicketCodeGenerator::class;
    }

    protected static function getMockableClass()
    {
        return static::getFacadeAccessor();
    }
}
