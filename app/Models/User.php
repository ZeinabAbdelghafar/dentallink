<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'email',
        'username',
        'password',
        'image',
        'gender',
        'verified',
        'role',
        'full_name',
        'birth_date',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'verified' => 'boolean',
        'birth_date' => 'date',
    ];

    public static function login($email, $password)
    {
        $user = self::where('email', $email)->first();
        
        if (!$user) {
            throw new \Exception('User is not registered');
        }

        if (!Hash::check($password, $user->password)) {
            throw new \Exception('Incorrect Password');
        }

        return $user;
    }

    // ✅ Add these for JWTAuth
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }
}
