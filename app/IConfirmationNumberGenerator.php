<?php


namespace App;

interface IConfirmationNumberGenerator
{
    public function generate() : string;
}
