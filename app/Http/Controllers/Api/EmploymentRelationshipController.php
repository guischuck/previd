<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\EmploymentRelationship;
use Illuminate\Http\Request;

class EmploymentRelationshipController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->only([
            'case_id',
            'employer_name',
            'employer_cnpj',
            'start_date',
            'end_date',
            'cargo',
            'documentos',
            'observacoes',
            'status_empresa',
            'is_active'
        ]);
        
        // Log para debug
        \Log::info('Criando Employment Relationship', [
            'data_recebida' => $data,
            'request_all' => $request->all()
        ]);
        
        $relationship = EmploymentRelationship::create($data);
        
        return response()->json([
            'success' => true,
            'data' => $relationship
        ]);
    }
    public function update(Request $request, $id)
    {
        $relationship = EmploymentRelationship::findOrFail($id);
        $data = $request->only([
            'start_date', 'end_date', 'cargo', 'documentos', 'observacoes', 'is_active', 'status_empresa'
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
    
    public function destroy($id)
    {
        $relationship = EmploymentRelationship::findOrFail($id);
        
        // Log para debug
        \Log::info('Removendo Employment Relationship', [
            'id' => $id,
            'dados' => $relationship->toArray()
        ]);
        
        // Armazenar o caso antes de excluir o relacionamento
        $case = $relationship->legalCase;
        
        // Excluir o relacionamento
        $relationship->delete();
        
        // Atualizar status do caso baseado no progresso da coleta
        $caseProgress = null;
        $caseStatus = null;
        
        if ($case) {
            $case->updateStatusBasedOnProgress();
            $refreshedCase = $case->fresh();
            $caseProgress = $refreshedCase->collection_progress;
            $caseStatus = $refreshedCase->status;
            
            \Log::info('Status do caso atualizado após remoção de vínculo', [
                'case_id' => $case->id,
                'novo_status' => $caseStatus,
                'progresso' => $caseProgress
            ]);
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Vínculo removido com sucesso',
            'case_progress' => $caseProgress,
            'case_status' => $caseStatus,
            'case_id' => $case ? $case->id : null
        ]);
    }
}