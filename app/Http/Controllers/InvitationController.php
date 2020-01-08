<?php

namespace App\Http\Controllers;

use App\Invitation;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class InvitationController extends Controller
{
    public function show(string $code)
    {
        /** @var Invitation $invitation */
        $invitation = Invitation::findByCode($code);

        abort_if($invitation->hasBeenUsed(), Response::HTTP_NOT_FOUND);

        return view('invitation.show', ['invitation' => $invitation]);
    }
}
