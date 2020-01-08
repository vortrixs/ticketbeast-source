<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

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
}
