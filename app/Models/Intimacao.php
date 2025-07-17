<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Intimacao extends Model
{
    use HasFactory;

    protected $table = 'intimacoes';

    protected $fillable = [
        'processo_id',
        'protocolo',
        'assunto',
        'conteudo',
        'email_origem',
        'anexos',
        'data_recebimento',
        'status',
        'erro_mensagem',
    ];

    protected $casts = [
        'anexos' => 'array',
        'data_recebimento' => 'datetime',
    ];

    public function processo(): BelongsTo
    {
        return $this->belongsTo(Processo::class);
    }
}
