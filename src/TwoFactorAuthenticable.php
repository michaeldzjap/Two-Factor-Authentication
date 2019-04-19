<?php

namespace MichaelDzjap\TwoFactorAuth;

use Illuminate\Database\Eloquent\Relations\HasOne;
use MichaelDzjap\TwoFactorAuth\TwoFactorAuth;
use Illuminate\Support\Facades\DB;

trait TwoFactorAuthenticable
{
    /**
     * Get the two-factor auth record associated with the user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function twoFactorAuth() : HasOne
    {
        return $this->hasOne(
            TwoFactorAuth::class, 'user_id', $this->getKeyName()
        );
    }

    /**
     * Set the two-factor auth id.
     *
     * @param  string  $id
     * @return void
     */
    public function setTwoFactorAuthId(string $id) : void
    {
        $enabled = config('twofactor-auth.enabled', 'user');

        if ($enabled === 'user') {
            // respect when 2fa is not set for user, never insert
            $this->twoFactorAuth->update(['id' => $id]);
        }

        if ($enabled === 'always') {
            $this->upsertTwoFactorAuthId($id);
        }
    }

    /**
     * Get the two-factor auth id.
     *
     * @return string $id
     */
    public function getTwoFactorAuthId() : string
    {
        return $this->twoFactorAuth->id;
    }

    /**
     * Create or update a two-factor authentication record with the given id.
     *
     * @param  string  $id
     * @return void
     */
    private function upsertTwoFactorAuthId(string $id) : void
    {
        DB::transaction(function () use ($id) {
            $attributes = ['id' => $id];

            if (!$this->twoFactorAuth()->exists()) {
                $this->twoFactorAuth()->create($attributes);
            } else {
                $this->twoFactorAuth->update($attributes);
            }
        });
    }
}
