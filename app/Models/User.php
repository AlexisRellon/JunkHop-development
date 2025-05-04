<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use App\Notifications\CustomVerifyEmail;
use Illuminate\Support\Facades\DB;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, HasRoles, Notifiable;

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'email_verified_at',
        'last_login_at',
        'created_at',
        'updated_at',
    ];

    /**
     * Update the user's last login timestamp.
     *
     * @return bool
     */
    public function updateLastLoginAt()
    {
        $this->last_login_at = now();
        return $this->save();
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'avatar',
        'password',
        'last_login_at',
        'ulid',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'id',
        'password',
        'remember_token',
        'email_verified_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function userProviders(): HasMany
    {
        return $this->hasMany(UserProvider::class);
    }

    public function mustVerifyEmail(): bool
    {
        return $this instanceof MustVerifyEmail && !$this->hasVerifiedEmail();
    }

    public function createDeviceToken(string $device, string $ip, bool $remember = false): string
    {
        $sanctumToken = $this->createToken(
            $device,
            ['*'],
            $remember ?
                now()->addMonth() :
                now()->addDay()
        );

        $sanctumToken->accessToken->ip = $ip;
        $sanctumToken->accessToken->save();

        return $sanctumToken->plainTextToken;
    }

    /**
     * Get the junkshops owned by the user.
     */
    public function junkshops()
    {
        return $this->hasMany(Junkshop::class);
    }

    /**
     * Get the merchant profile associated with the user.
     */
    public function merchant()
    {
        return $this->hasOne(Merchant::class, 'user_id', 'ulid');
    }

    /**
     * Send the email verification notification.
     *
     * @return void
     */
    public function sendEmailVerificationNotification()
    {
        $this->notify(new CustomVerifyEmail);
    }

    /**
     * Get the role attribute - enhanced for better reliability
     *
     * @return string
     */
    public function getRoleAttribute()
    {
        // First try direct DB query for maximum reliability
        $directRole = DB::table('model_has_roles')
            ->where('model_id', $this->id)
            ->where('model_type', get_class($this))
            ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
            ->select('roles.name')
            ->first();

        if ($directRole) {
            return $directRole->name;
        }

        // Fall back to Spatie method
        return $this->roles->first()->name ?? 'user';
    }

    /**
     * Append role to the model
     */
    protected $appends = ['role'];
}
