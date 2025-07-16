<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdvboxTask extends Model
{
    /**
     * A tabela associada ao modelo.
     *
     * @var string
     */
    protected $table = 'advbox_tasks';

    /**
     * Os atributos que são atribuíveis em massa.
     *
     * @var array
     */
    protected $fillable = [
        'advbox_id',
        'user_id',
        'from',
        'guests',
        'tasks_id',
        'lawsuits_id',
        'comments',
        'start_date',
        'start_time',
        'end_date',
        'end_time',
        'date_deadline',
        'local',
        'urgent',
        'important',
        'display_schedule',
        'date',
        'folder',
        'protocol_number',
        'process_number',
        'api_response',
        'status',
        'error_message'
    ];

    /**
     * Os atributos que devem ser convertidos para tipos nativos.
     *
     * @var array
     */
    protected $casts = [
        'guests' => 'array',
        'api_response' => 'array',
        'urgent' => 'boolean',
        'important' => 'boolean',
        'display_schedule' => 'boolean',
    ];

    /**
     * Obtém o usuário que criou a tarefa.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
