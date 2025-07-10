<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Despacho extends Model
{
    protected $table = 'despachos';

    protected $fillable = [
        'id_empresa',
        'protocolo',
        'conteudo',
        'data_email',
    ];

    protected $casts = [
        'data_email' => 'datetime',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class, 'id_empresa');
    }
} 