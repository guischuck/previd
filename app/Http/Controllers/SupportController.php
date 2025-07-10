<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;
use App\Mail\SupportEmail;

class SupportController extends Controller
{
    public function index()
    {
        return Inertia::render('Support/Index');
    }

    public function send(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'subject' => 'required|string|max:255',
            'message' => 'required|string|max:2000',
        ]);

        try {
            // Enviar email para o suporte
            Mail::to('suporte@previdia.com.br')->send(new SupportEmail(
                $request->name,
                $request->email,
                $request->subject,
                $request->message
            ));

            return redirect()->back()->with('success', 'Mensagem enviada com sucesso! Entraremos em contato em breve.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Erro ao enviar mensagem. Tente novamente ou entre em contato diretamente pelo email suporte@previdia.com.br');
        }
    }
} 