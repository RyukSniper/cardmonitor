<?php

namespace App\Models\Apis;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class Api extends Model
{
    protected $dates = [
        'articles_synced_at',
        'invalid_at',
        'orders_synced_at',
    ];

    protected $guarded = [
        'id',
    ];

    public function isConnected() : bool
    {
        return (! is_null($this->attributes['access_token']));
    }

    public function isDeletable() : bool
    {
        return true;
    }

    public function reset() : self
    {
        $this->update([
            'request_token' => null,
            'access_token' => null,
            'access_token_secret' => null,
            'invalid_at' => null,
        ]);

        return $this;
    }

    public function setAccessToken(string $request_token, string $access_token, string $access_token_secret)
    {
        $this->update([
            'request_token' => $request_token,
            'access_token' => $access_token,
            'access_token_secret' => $access_token_secret,
            'invalid_at' => now()->addHours(24),
        ]);
    }

    public function getAccessdataAttribute() : array
    {
        return [
            'request_token' => $this->request_token,
            'access_token' => $this->access_token,
            'access_token_secret' => $this->access_token_secret,
        ];
    }

    public function getRequestTokenAttribute()
    {
        return is_null($this->attributes['request_token']) ? null :  Crypt::decryptString($this->attributes['request_token']);
    }

    public function getAccessTokenAttribute()
    {
        return is_null($this->attributes['access_token']) ? null : Crypt::decryptString($this->attributes['access_token']);
    }

    public function getAccessTokenSecretAttribute()
    {
        return is_null($this->attributes['access_token_secret']) ? null : Crypt::decryptString($this->attributes['access_token_secret']);
    }

    public function setRequestTokenAttribute($value)
    {
        $this->attributes['request_token'] = Crypt::encryptString($value);
    }

    public function setAccessTokenAttribute($value)
    {
        $this->attributes['access_token'] = Crypt::encryptString($value);
    }

    public function setAccessTokenSecretAttribute($value)
    {
        $this->attributes['access_token_secret'] = Crypt::encryptString($value);
    }
}
