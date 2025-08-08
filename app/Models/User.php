<?php

namespace App\Models;

/**
 * @method bool hasRole(string $role)
 * @method bool can(string $permission)
 * @method bool hasPermissionTo(string $permission)
 */

use App\Models\UserLoginDetail;
use App\Notifications\CustomResetPassword;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Log;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'username',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    // This makes the broker use your custom notification
    public function sendPasswordResetNotification($token)
    {
        // Log::info("message=Sending password reset notification to user: {$this->email}");
        $this->notify(new CustomResetPassword($token));
    }

    public function loginDetail(): HasOne
    {
        return $this->hasOne(UserLoginDetail::class, 'user_id', 'id')->withDefault();
    }
}
