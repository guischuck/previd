<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LegalDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class DocumentController extends Controller
{
    public function index()
    {
        $documents = LegalDocument::with('createdBy')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return Inertia::render('Admin/Documents/Index', [
            'documents' => $documents,
            'stats' => [
                'total' => LegalDocument::count(),
                'public' => LegalDocument::where('is_public', true)->count(),
                'private' => LegalDocument::where('is_public', false)->count(),
            ]
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
            'file' => 'required|file|mimes:pdf,doc,docx|max:10240', // 10MB
            'is_public' => 'boolean',
            'category' => 'required|string|max:50',
        ]);

        $path = $request->file('file')->store('documents');

        LegalDocument::create([
            'title' => $request->title,
            'description' => $request->description,
            'file_path' => $path,
            'file_name' => $request->file('file')->getClientOriginalName(),
            'file_size' => $request->file('file')->getSize(),
            'file_type' => $request->file('file')->getMimeType(),
            'is_public' => $request->is_public ?? false,
            'category' => $request->category,
            'created_by' => auth()->id(),
        ]);

        return redirect()->route('admin.documents.index')
            ->with('success', 'Documento criado com sucesso!');
    }

    public function show(LegalDocument $document)
    {
        $document->load('createdBy');

        return Inertia::render('Admin/Documents/Show', [
            'document' => $document
        ]);
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
            'file' => 'nullable|file|mimes:pdf,doc,docx|max:10240', // 10MB
            'is_public' => 'boolean',
            'category' => 'required|string|max:50',
        ]);

        $data = $request->except('file');

        if ($request->hasFile('file')) {
            // Excluir arquivo antigo
            Storage::delete($document->file_path);

            // Salvar novo arquivo
            $path = $request->file('file')->store('documents');
            $data['file_path'] = $path;
            $data['file_name'] = $request->file('file')->getClientOriginalName();
            $data['file_size'] = $request->file('file')->getSize();
            $data['file_type'] = $request->file('file')->getMimeType();
        }

        $document->update($data);

        return redirect()->route('admin.documents.index')
            ->with('success', 'Documento atualizado com sucesso!');
    }

    public function destroy(LegalDocument $document)
    {
        // Excluir arquivo
        Storage::delete($document->file_path);

        $document->delete();

        return redirect()->route('admin.documents.index')
            ->with('success', 'Documento excluído com sucesso!');
    }

    public function download(LegalDocument $document)
    {
        return Storage::download($document->file_path, $document->file_name);
    }
} 