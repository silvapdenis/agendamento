<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Specialty extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // Relacionamentos

    /**
     * Get doctors with this specialty
     */
    public function doctors()
    {
        return $this->hasMany(Doctor::class);
    }

    // Scopes

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Mutators

    public function setSlugAttribute($value)
    {
        $this->attributes['slug'] = \Str::slug($value ?: $this->name);
    }
}