<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\EmploymentRelationship;
use Illuminate\Http\Request;

class EmploymentRelationshipController extends Controller
{
    public function update(Request $request, $id)
    {
        $relationship = EmploymentRelationship::findOrFail($id);
        $data = $request->only([
            'start_date', 'end_date', 'cargo', 'documentos', 'observacoes', 'is_active'
        ]);
        
        // Log para debug
        \Log::info('Atualizando Employment Relationship', [
            'id' => $id,
            'data_recebida' => $data,
            'request_all' => $request->all()
        ]);
        
        // Compatibilidade com campos antigos
        if (isset($data['cargo'])) $data['position'] = $data['cargo'];
        
        $relationship->fill($data);
        $relationship->save();
        
        \Log::info('Employment Relationship atualizado', [
            'id' => $id,
            'is_active_antes' => $relationship->getOriginal('is_active'),
            'is_active_depois' => $relationship->is_active,
            'dados_salvos' => $relationship->toArray()
        ]);
        
        // Atualizar status do caso baseado no progresso da coleta
        $case = $relationship->legalCase;
        $caseProgress = null;
        $caseStatus = null;
        
        if ($case) {
            $case->updateStatusBasedOnProgress();
            $refreshedCase = $case->fresh();
            $caseProgress = $refreshedCase->collection_progress;
            $caseStatus = $refreshedCase->status;
            
            \Log::info('Status do caso atualizado', [
                'case_id' => $case->id,
                'novo_status' => $caseStatus,
                'progresso' => $caseProgress
            ]);
        }
        
        return response()->json([
            'success' => true, 
            'data' => $relationship,
            'case_progress' => $caseProgress,
            'case_status' => $caseStatus,
            'case_id' => $case ? $case->id : null
        ]);
    }
} 