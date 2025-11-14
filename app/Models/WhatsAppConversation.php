<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WhatsAppConversation extends Model
{
    use HasFactory;

    protected $fillable = [
        'phone_number',
        'state',
        'context',
        'last_message_at'
    ];

    protected $casts = [
        'context' => 'array',
        'last_message_at' => 'datetime'
    ];

    /**
     * Scope para conversas ativas (atualizadas nas últimas 24 horas)
     */
    public function scopeActive($query)
    {
        return $query->where('updated_at', '>=', now()->subDay());
    }

    /**
     * Scope para conversas abandonadas (não atualizadas há mais de 24 horas)
     */
    public function scopeAbandoned($query)
    {
        return $query->where('updated_at', '<', now()->subDay());
    }
}