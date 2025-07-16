<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Task extends Model
{
    use HasFactory;

    /**
     * Os atributos que podem ser preenchidos em massa.
     *
     * @var array
     */
    protected $fillable = [
        'name', 
        'category', 
        'description', 
        'status', 
        'deadline'
    ];

    /**
     * Os atributos que devem ser convertidos para tipos nativos.
     *
     * @var array
     */
    protected $casts = [
        'deadline' => 'datetime',
    ];

    /**
     * Relacionamento com processos judiciais
     */
    public function lawsuits()
    {
        return $this->belongsToMany(Lawsuit::class, 'lawsuit_tasks');
    }

    /**
     * Relacionamento com usuários
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'task_users');
    }
} 