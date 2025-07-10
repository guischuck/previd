<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Log;

class ErrorLog extends Model
{
    protected $fillable = [
        'type',
        'message',
        'stack_trace',
        'file',
        'line',
        'url',
        'method',
        'request_data',
        'user_agent',
        'ip',
        'user_id',
        'resolved',
        'resolution_notes',
        'resolved_at',
    ];

    protected $casts = [
        'request_data' => 'array',
        'resolved' => 'boolean',
        'resolved_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function logError(\Throwable $exception, array $context = []): self
    {
        try {
            $request = request();
            
            $data = [
                'type' => get_class($exception),
                'message' => $exception->getMessage(),
                'stack_trace' => $exception->getTraceAsString(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'request_data' => $request->except(['password', 'password_confirmation']),
                'user_agent' => $request->userAgent(),
                'ip' => $request->ip(),
                'user_id' => auth()->id(),
            ] + $context;

            Log::info('Registrando erro:', $data);
            
            return self::create($data);
        } catch (\Exception $e) {
            Log::error('Erro ao registrar erro:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return new self();
        }
    }
} 