<?php

namespace App;

use App\Mail\InvitationEmail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Mail;

class Invitation extends Model
{
    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public static function findByCode(string $code) : Invitation
    {
        return self::where('code', $code)->firstOrFail();
    }

    public function hasBeenUsed() : bool
    {
        return null !== $this->user_id;
    }

    public function useForUser(User $user)
    {
        $this->update(['user_id' => $user->id]);
    }

    public function send()
    {
        Mail::to($this->email)->send(new InvitationEmail($this));
    }
}
