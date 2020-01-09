<?php


namespace App;

interface IInvitationCodeGenerator
{
    public function generate() : string;
}
