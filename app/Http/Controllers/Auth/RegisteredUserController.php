<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class RegisteredUserController extends Controller
{
    /**
     * Show the registration page.
     */
    public function create(): Response
    {
        return Inertia::render('auth/register');
    }

    /**
     * Gerar slug único para a empresa
     */
    private function generateUniqueSlug(string $name): string
    {
        // Remove acentos e caracteres especiais
        $slug = Str::slug($name);
        
        // Verifica se o slug já existe
        $baseSlug = $slug;
        $counter = 1;
        
        while (DB::table('companies')->where('slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }
        
        return $slug;
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|lowercase|email|max:255|unique:'.User::class,
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'company_name' => 'required|string|max:255',
            'company_cnpj' => 'nullable|string|max:18',
        ]);

        DB::beginTransaction();

        try {
            // Gerar slug único para a empresa
            $slug = $this->generateUniqueSlug($request->company_name);

            // Criar empresa
            $company = \App\Models\Company::create([
                'name' => $request->company_name,
                'slug' => $slug,
                'email' => $request->email,
                'cnpj' => $request->company_cnpj,
                'plan' => 'basic',
                'max_users' => 5,
                'max_cases' => 100,
                'is_active' => true,
                'api_key' => Str::random(32), // Gerar API key automaticamente
            ]);

            // Criar usuário vinculado à empresa
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'company_id' => $company->id,
            ]);

            DB::commit();

            event(new Registered($user));

            Auth::login($user);

            return redirect()->intended(route('dashboard', absolute: false));
        } catch (\Exception $e) {
            DB::rollback();
            
            return back()->withErrors([
                'email' => 'Erro ao criar conta e empresa. Por favor, tente novamente.',
            ])->withInput();
        }
    }
}
