<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CommandLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'command',
        'parameters',
        'exit_code',
        'output',
        'duration_ms',
        'triggered_by',
        'ip_address',
    ];

    protected $casts = [
        'parameters' => 'array',
        'exit_code' => 'integer',
        'duration_ms' => 'integer',
    ];

    public function isSuccess(): bool
    {
        return $this->exit_code === 0;
    }

    public function getDurationFormattedAttribute(): string
    {
        if ($this->duration_ms < 1000) {
            return $this->duration_ms . ' ms';
        }
        return round($this->duration_ms / 1000, 2) . ' s';
    }
}
