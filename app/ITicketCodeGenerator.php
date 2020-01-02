<?php


namespace App;

interface ITicketCodeGenerator
{
    public function generateFor(Ticket $ticket) : string;
}
