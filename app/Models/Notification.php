<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'title',
        'message',
        'data',
        'channel',
        'sent_at',
        'read_at',
        'is_sent'
    ];

    protected $casts = [
        'data' => 'array',
        'sent_at' => 'datetime',
        'read_at' => 'datetime',
        'is_sent' => 'boolean',
    ];

    // Relacionamentos

    /**
     * Get the user who owns this notification
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Scopes

    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }

    public function scopeRead($query)
    {
        return $query->whereNotNull('read_at');
    }

    public function scopeSent($query)
    {
        return $query->where('is_sent', true);
    }

    public function scopePending($query)
    {
        return $query->where('is_sent', false);
    }

    public function scopeByChannel($query, $channel)
    {
        return $query->where('channel', $channel);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    // Accessors

    public function getIsReadAttribute()
    {
        return !is_null($this->read_at);
    }

    public function getFormattedDateAttribute()
    {
        return $this->created_at->diffForHumans();
    }

    public function getIconAttribute()
    {
        return match($this->type) {
            'appointment_reminder' => 'calendar',
            'appointment_confirmed' => 'check-circle',
            'appointment_cancelled' => 'x-circle',
            'new_message' => 'message-circle',
            'payment_received' => 'dollar-sign',
            default => 'bell'
        };
    }

    public function getColorAttribute()
    {
        return match($this->type) {
            'appointment_reminder' => 'blue',
            'appointment_confirmed' => 'green',
            'appointment_cancelled' => 'red',
            'new_message' => 'indigo',
            'payment_received' => 'emerald',
            default => 'gray'
        };
    }

    // Methods

    /**
     * Mark notification as read
     */
    public function markAsRead()
    {
        $this->update(['read_at' => now()]);
    }

    /**
     * Mark notification as sent
     */
    public function markAsSent()
    {
        $this->update([
            'is_sent' => true,
            'sent_at' => now()
        ]);
    }

    /**
     * Create appointment reminder notification
     */
    public static function createAppointmentReminder($userId, $appointment)
    {
        return static::create([
            'user_id' => $userId,
            'type' => 'appointment_reminder',
            'title' => 'Lembrete de Consulta',
            'message' => "Você tem uma consulta marcada para {$appointment->formatted_date}",
            'data' => ['appointment_id' => $appointment->id],
            'channel' => 'system'
        ]);
    }

    /**
     * Create appointment confirmation notification
     */
    public static function createAppointmentConfirmation($userId, $appointment)
    {
        return static::create([
            'user_id' => $userId,
            'type' => 'appointment_confirmed',
            'title' => 'Consulta Confirmada',
            'message' => "Sua consulta foi confirmada para {$appointment->formatted_date}",
            'data' => ['appointment_id' => $appointment->id],
            'channel' => 'system'
        ]);
    }
}