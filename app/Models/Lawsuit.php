<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Lawsuit extends Model
{
    use HasFactory;

    /**
     * Os atributos que podem ser preenchidos em massa.
     *
     * @var array
     */
    protected $fillable = [
        'process_number', 
        'protocol_number', 
        'status'
    ];

    /**
     * Os atributos que devem ser convertidos para tipos nativos.
     *
     * @var array
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relacionamento com clientes
     */
    public function customers()
    {
        return $this->belongsToMany(Customer::class, 'lawsuit_customers');
    }

    /**
     * Relacionamento com movimentações
     */
    public function movements()
    {
        return $this->hasMany(LawsuitMovement::class);
    }

    /**
     * Relacionamento com tarefas
     */
    public function tasks()
    {
        return $this->belongsToMany(Task::class, 'lawsuit_tasks');
    }

    /**
     * Escopo para buscar por número de protocolo
     */
    public function scopeByProtocolNumber($query, $protocolNumber)
    {
        return $query->where('protocol_number', $protocolNumber);
    }

    /**
     * Escopo para buscar por número do processo
     */
    public function scopeByProcessNumber($query, $processNumber)
    {
        return $query->where('process_number', $processNumber);
    }
} 