<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LegalDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class LegalDocumentController extends Controller
{
    public function index()
    {
        $documents = LegalDocument::orderBy('created_at', 'desc')
            ->paginate(10);

        return Inertia::render('Admin/Documents/Index', [
            'documents' => $documents
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Documents/Create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'category' => 'required|string|max:50',
            'file' => 'required|file|mimes:pdf|max:10240', // max 10MB
            'is_public' => 'required|boolean'
        ]);

        try {
            $path = $request->file('file')->store('public/conhecimento');

            LegalDocument::create([
                'title' => $request->title,
                'description' => $request->description,
                'category' => $request->category,
                'file_path' => $path,
                'is_public' => $request->is_public
            ]);

            return redirect()->route('admin.documents.index')
                ->with('success', 'Documento adicionado com sucesso!');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Erro ao adicionar documento: ' . $e->getMessage()]);
        }
    }

    public function edit(LegalDocument $document)
    {
        return Inertia::render('Admin/Documents/Edit', [
            'document' => $document
        ]);
    }

    public function update(Request $request, LegalDocument $document)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'category' => 'required|string|max:50',
            'file' => 'nullable|file|mimes:pdf|max:10240', // max 10MB
            'is_public' => 'required|boolean'
        ]);

        try {
            $data = [
                'title' => $request->title,
                'description' => $request->description,
                'category' => $request->category,
                'is_public' => $request->is_public
            ];

            if ($request->hasFile('file')) {
                // Excluir arquivo antigo
                Storage::delete($document->file_path);
                
                // Salvar novo arquivo
                $data['file_path'] = $request->file('file')->store('public/conhecimento');
            }

            $document->update($data);

            return redirect()->route('admin.documents.index')
                ->with('success', 'Documento atualizado com sucesso!');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Erro ao atualizar documento: ' . $e->getMessage()]);
        }
    }

    public function destroy(LegalDocument $document)
    {
        try {
            // Excluir arquivo
            Storage::delete($document->file_path);
            
            // Excluir registro
            $document->delete();

            return redirect()->route('admin.documents.index')
                ->with('success', 'Documento excluído com sucesso!');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Erro ao excluir documento: ' . $e->getMessage()]);
        }
    }

    public function download(LegalDocument $document)
    {
        try {
            return Storage::download($document->file_path);
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Erro ao baixar documento: ' . $e->getMessage()]);
        }
    }
} 