<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Inertia\Inertia;

class ClientRegisterController extends Controller
{
    public function showRegister()
    {
        return Inertia::render("ClientRegister");
    }

    private function generateSlug($text)
    {
        // Remove acentos
        $text = iconv("UTF-8", "ASCII//TRANSLIT", $text);
        // Remove caracteres especiais e converte para minúsculo
        $text = strtolower(preg_replace("/[^a-zA-Z0-9\s]/", "", $text));
        // Substitui espaços por hífens
        $text = preg_replace("/\s+/", "-", trim($text));
        // Remove hífens duplos
        $text = preg_replace("/-+/", "-", $text);
        return trim($text, "-");
    }

    public function register(Request $request)
    {
        Log::info("=== INÍCIO DO CADASTRO ===");
        
        $request->validate([
            "name" => "required|string|max:255",
            "cpf" => "required|string|min:11|max:14", 
            "email" => "required|string|email|max:255|unique:users",
            "phone" => "required|string|max:20",
            "password" => "required|string|min:6|confirmed"
        ], [
            "name.required" => "Nome é obrigatório",
            "cpf.required" => "CPF é obrigatório", 
            "email.required" => "Email é obrigatório",
            "email.unique" => "Este email já está cadastrado",
            "phone.required" => "Telefone é obrigatório",
            "password.required" => "Senha é obrigatória",
            "password.min" => "Senha deve ter pelo menos 6 caracteres",
            "password.confirmed" => "Confirmação de senha não confere"
        ]);

        $cpf = preg_replace("/[^0-9]/", "", $request->cpf);

        // Verificar se CPF já existe
        if (User::where("cpf", $cpf)->exists()) {
            return back()->withErrors(["cpf" => "Este CPF já está cadastrado."]);
        }

        DB::beginTransaction();
        
        try {
            // Gerar slug único para a empresa
            $baseSlug = $this->generateSlug("escritorio-" . $request->name);
            $slug = $baseSlug;
            $counter = 1;
            
            // Garantir que o slug seja único
            while (DB::table("companies")->where("slug", $slug)->exists()) {
                $slug = $baseSlug . "-" . $counter;
                $counter++;
            }
            
            Log::info("Slug gerado: " . $slug);
            
            // 1. Criar nova empresa
            $companyData = [
                "name" => "Escritório de " . $request->name,
                "slug" => $slug,
                "email" => $request->email,
                "phone" => preg_replace("/[^0-9]/", "", $request->phone),
                "api_key" => Str::random(32),
                "is_active" => 1,
                "created_at" => now(),
                "updated_at" => now()
            ];
            
            $companyId = DB::table("companies")->insertGetId($companyData);
            Log::info("Empresa criada com ID: " . $companyId);

            // 2. Criar usuário
            $userData = [
                "name" => $request->name,
                "email" => $request->email,
                "cpf" => $cpf,
                "phone" => preg_replace("/[^0-9]/", "", $request->phone),
                "password" => Hash::make($request->password),
                "company_id" => $companyId,
                "created_at" => now(),
                "updated_at" => now()
            ];
            
            $userId = DB::table("users")->insertGetId($userData);
            Log::info("Usuário criado com ID: " . $userId);

            DB::commit();

            // Login automático
            $user = User::find($userId);
            Auth::login($user);
            
            return redirect()->route("dashboard")->with("success", "Cadastro realizado! Bem-vindo ao seu escritório virtual.");
            
        } catch (\Exception $e) {
            Log::error("ERRO: " . $e->getMessage());
            DB::rollback();
            
            return back()->withErrors([
                "email" => "Erro ao criar conta: " . $e->getMessage()
            ])->withInput();
        }
    }
}