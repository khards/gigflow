<?php

namespace App\Domains\Auth\Models;

use App\Booking\Availability\Schedule;
use App\Booking\Availability\Traits\ScheduledAvailable;
use App\Booking\Business;
use App\Booking\Models\Calendar;
use App\Domains\Auth\Models\Traits\Attribute\UserAttribute;
use App\Domains\Auth\Models\Traits\Method\UserMethod;
use App\Domains\Auth\Models\Traits\Relationship\UserRelationship;
use App\Domains\Auth\Models\Traits\Scope\UserScope;
use App\Domains\Auth\Notifications\Frontend\ResetPasswordNotification;
use App\Domains\Auth\Notifications\Frontend\VerifyEmail;
use App\Domains\Order\Order;
use App\Models\Traits\Uuid;
use Carbon\Carbon;
use DarkGhostHunter\Laraguard\Contracts\TwoFactorAuthenticatable;
use DarkGhostHunter\Laraguard\TwoFactorAuthentication;
use Database\Factories\UserFactory;
use Illuminate\Auth\MustVerifyEmail as MustVerifyEmailTrait;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Lab404\Impersonate\Models\Impersonate;
use Laravel\Passport\HasApiTokens;
use Recurr\Exception\InvalidRRule;
use Recurr\Exception\InvalidWeekday;
use Spatie\Permission\Traits\HasRoles;
use Spatie\SchemalessAttributes\SchemalessAttributes;

/**
 * Class User.
 */
class User extends Authenticatable implements MustVerifyEmail, TwoFactorAuthenticatable
{
    use Uuid;
    use HasFactory;
    use HasRoles;
    use Impersonate;
    use MustVerifyEmailTrait;
    use Notifiable;
    use SoftDeletes;
    use TwoFactorAuthentication;
    use UserAttribute;
    use UserMethod;
    use UserRelationship;
    use UserScope;
    //Booking.
    use ScheduledAvailable;
    //Passport
    use HasApiTokens;

    public const TYPE_ADMIN = 'admin';

    public const TYPE_USER = 'user';

    public const TYPE_CUSTOMER = 'customer';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'type',
        'name',
        'email',
        'email_verified_at',
        'password',
        'password_changed_at',
        'active',
        'timezone',
        'last_login_at',
        'last_login_ip',
        'to_be_logged_out',
        'provider',
        'provider_id',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * @var array
     */
    protected $dates = [
        'last_login_at',
        'email_verified_at',
        'password_changed_at',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'active' => 'boolean',
        'last_login_at' => 'datetime',
        'email_verified_at' => 'datetime',
        'to_be_logged_out' => 'boolean',

        //Booking system
        'booking_settings' => 'array',
    ];

    /**
     * @var array
     */
    protected $appends = [
        'avatar',
    ];

    /**
     * @var string[]
     */
    protected $with = [
        'permissions',
        'roles',
    ];

    /**
     * Create a new factory instance for the model.
     *
     * @return Factory
     */
    protected static function newFactory(): Factory
    {
        return UserFactory::new();
    }

    /**
     * Send the password reset notification.
     *
     * @param string $token
     * @return void
     */
    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new ResetPasswordNotification($token));
    }

    /**
     * Send the registration verification email.
     */
    public function sendEmailVerificationNotification(): void
    {
        $this->notify(new VerifyEmail());
    }

    /**
     * Return true or false if the user can impersonate an other user.
     *
     * @param void
     * @return bool
     */
    public function canImpersonate(): bool
    {
        return $this->can('admin.access.user.impersonate');
    }

    /**
     * Return true or false if the user can be impersonate.
     *
     * @param void
     * @return  bool
     */
    public function canBeImpersonated(): bool
    {
        return ! $this->isMasterAdmin();
    }

    public function getBookingSettingsAttribute(): SchemalessAttributes
    {
        return SchemalessAttributes::createForModel($this, 'booking_settings');
    }

    public function scopeWithBookingSettings(): Builder
    {
        return SchemalessAttributes::scopeWithSchemalessAttributes('booking_settings');
    }

    /**
     * The businesses that belong to the user.
     */
    public function businesses(): BelongsToMany
    {
        return $this->belongsToMany(Business::class, 'business_user', 'user_id');
    }

    /**
     * The Users calendar entries (used for holiday).
     *
     * @return MorphMany
     */
    public function calendar(): MorphMany
    {
        return $this->morphMany(Calendar::class, 'model');
    }

    /**
     * The Users holidays - hasMany availability's.
     *
     * @return MorphMany
     */
    public function availability(): MorphMany
    {
        $className = (new Schedule())->getMorphClass();

        return $this->morphMany($className, 'model');
    }

    /**
     * Check if a member of staff is available during a given period.
     *
     * @param Carbon $startDateTime
     * @param Carbon $finishDateTime
     * @return bool
     * @throws InvalidRRule
     * @throws InvalidWeekday
     */
    public function isAvailableDuring(Carbon $startDateTime, Carbon $finishDateTime): bool
    {
        $scheduledAvailability = $this->scheduledAvailableDuring($startDateTime, $finishDateTime);

        if (! $scheduledAvailability) {
            return false;
        }

        $query = Calendar::quantityBookedBetween($this, $startDateTime, $finishDateTime);
        $quantityBooked = $query->get()->count();

        //Assumption is that staff can only handle a single booking at the time!
        return $quantityBooked == 0;
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}
