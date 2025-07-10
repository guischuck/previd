<?php

namespace App\Http\Controllers;

use App\Models\LegalDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Smalot\PdfParser\Parser;

class LegalDocumentController extends Controller
{
    public function index(Request $request)
    {
        $query = LegalDocument::query();

        // Filtros
        if ($request->has('type')) {
            $query->byType($request->type);
        }

        if ($request->has('search')) {
            $query->search($request->search);
        }

        // Ordenação
        $query->orderBy($request->get('sort', 'created_at'), $request->get('order', 'desc'));

        $documents = $query->paginate(15);

        return view('legal-documents.index', [
            'documents' => $documents,
            'types' => [
                'acordao' => 'Acórdão',
                'lei' => 'Lei',
                'jurisprudencia' => 'Jurisprudência',
                'sumula' => 'Súmula',
                'portaria' => 'Portaria',
                'decreto' => 'Decreto',
                'resolucao' => 'Resolução',
            ]
        ]);
    }

    public function create()
    {
        return view('legal-documents.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|string',
            'description' => 'nullable|string',
            'file' => 'required|file|mimes:pdf|max:10240', // max 10MB
            'metadata' => 'nullable|array'
        ]);

        $file = $request->file('file');
        $path = $file->store('legal-documents', 'public');

        // Criar o documento
        $document = new LegalDocument([
            'title' => $request->title,
            'type' => $request->type,
            'description' => $request->description,
            'file_path' => $path,
            'file_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'file_size' => $file->getSize(),
            'metadata' => $request->metadata,
            'uploaded_by' => auth()->id()
        ]);

        $document->save();

        // Processar o PDF em background
        dispatch(function() use ($document) {
            $parser = new Parser();
            $pdf = $parser->parseFile(storage_path('app/public/' . $document->file_path));
            
            $text = $pdf->getText();
            
            $document->update([
                'extracted_text' => $text,
                'is_processed' => true
            ]);
        })->afterResponse();

        return redirect()
            ->route('legal-documents.index')
            ->with('success', 'Documento legal adicionado com sucesso!');
    }

    public function show(LegalDocument $document)
    {
        return view('legal-documents.show', compact('document'));
    }

    public function download(LegalDocument $document)
    {
        return Storage::disk('public')->download(
            $document->file_path, 
            $document->file_name
        );
    }

    public function destroy(LegalDocument $document)
    {
        // Remover arquivo
        Storage::disk('public')->delete($document->file_path);
        
        // Remover registro
        $document->delete();

        return redirect()
            ->route('legal-documents.index')
            ->with('success', 'Documento legal removido com sucesso!');
    }
} 