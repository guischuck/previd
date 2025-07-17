<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Task extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Os atributos que podem ser preenchidos em massa.
     *
     * @var array
     */
    protected $fillable = [
        'case_id',
        'workflow_template_id',
        'title',
        'description',
        'status',
        'priority',
        'due_date',
        'completed_at',
        'assigned_to',
        'created_by',
        'required_documents',
        'notes',
        'order',
        'is_workflow_task'
    ];

    /**
     * Os atributos que devem ser convertidos para tipos nativos.
     *
     * @var array
     */
    protected $casts = [
        'due_date' => 'date',
        'completed_at' => 'date',
        'required_documents' => 'array',
        'is_workflow_task' => 'boolean',
    ];

    /**
     * Relacionamento com o caso legal
     */
    public function case()
    {
        return $this->belongsTo(LegalCase::class, 'case_id');
    }

    /**
     * Relacionamento com o usuário responsável
     */
    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /**
     * Relacionamento com o usuário que criou a tarefa
     */
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Relacionamento com o template de workflow
     */
    public function workflowTemplate()
    {
        return $this->belongsTo(WorkflowTemplate::class, 'workflow_template_id');
    }

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