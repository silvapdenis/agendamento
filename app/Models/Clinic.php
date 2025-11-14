<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Clinic extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'cnpj',
        'phone',
        'email',
        'address',
        'city',
        'state',
        'zip_code',
        'description',
        'logo_path',
        'business_hours',
        'subscription_plan',
        'subscription_expires_at',
        'is_active'
    ];

    protected $casts = [
        'business_hours' => 'array',
        'subscription_expires_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    // Relacionamentos

    /**
     * Get doctors associated with this clinic
     */
    public function doctors()
    {
        return $this->belongsToMany(Doctor::class)
                   ->withPivot('is_admin')
                   ->withTimestamps();
    }

    /**
     * Get appointments at this clinic
     */
    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }

    /**
     * Get clinic admins (doctors who are admins)
     */
    public function admins()
    {
        return $this->belongsToMany(Doctor::class)
                   ->wherePivot('is_admin', true)
                   ->withTimestamps();
    }

    // Scopes

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeWithValidSubscription($query)
    {
        return $query->where('subscription_expires_at', '>', now())
                    ->orWhereNull('subscription_expires_at');
    }

    // Mutators

    public function setSlugAttribute($value)
    {
        $this->attributes['slug'] = \Str::slug($value ?: $this->name);
    }

    // Accessors

    public function getIsSubscriptionActiveAttribute()
    {
        return is_null($this->subscription_expires_at) || 
               $this->subscription_expires_at->isFuture();
    }
}