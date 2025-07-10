<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LegalDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'type',
        'description',
        'file_path',
        'file_name',
        'mime_type',
        'file_size',
        'extracted_text',
        'metadata',
        'is_processed',
        'uploaded_by',
    ];

    protected $casts = [
        'extracted_text' => 'array',
        'metadata' => 'array',
        'is_processed' => 'boolean',
        'file_size' => 'integer',
    ];

    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function getFileSizeFormattedAttribute(): string
    {
        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }

    public function getTypeTextAttribute(): string
    {
        return match($this->type) {
            'acordao' => 'Acórdão',
            'lei' => 'Lei',
            'jurisprudencia' => 'Jurisprudência',
            'sumula' => 'Súmula',
            'portaria' => 'Portaria',
            'decreto' => 'Decreto',
            'resolucao' => 'Resolução',
            default => ucfirst($this->type),
        };
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeProcessed($query)
    {
        return $query->where('is_processed', true);
    }

    public function scopeUnprocessed($query)
    {
        return $query->where('is_processed', false);
    }

    public function scopeSearch($query, $term)
    {
        return $query->where(function($q) use ($term) {
            $q->where('title', 'like', "%{$term}%")
              ->orWhere('description', 'like', "%{$term}%")
              ->orWhereRaw("JSON_CONTAINS(extracted_text, '\"{$term}\"', '$')");
        });
    }
} 