<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Inspiring;
use Illuminate\Http\Request;
use Inertia\Middleware;
use Tighten\Ziggy\Ziggy;
use App\Models\HistoricoSituacao;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        [$message, $author] = str(Inspiring::quotes()->random())->explode('-');

        // Calcular estatísticas de andamentos não vistos para usuários autenticados
        $stats = null;
        if ($request->user() && $request->user()->company_id) {
            $stats = [
                'nao_vistos' => HistoricoSituacao::where('id_empresa', $request->user()->company_id)
                    ->where('visto', false)
                    ->count()
            ];
        }

        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'quote' => ['message' => trim($message), 'author' => trim($author)],
            'auth' => [
                'user' => $request->user() ? [
                    'id' => $request->user()->id,
                    'name' => $request->user()->name,
                    'email' => $request->user()->email,
                    'role' => $request->user()->role,
                    'company_id' => $request->user()->company_id,
                    'is_super_admin' => $request->user()->isSuperAdmin(),
                    'is_admin' => $request->user()->isAdmin(),
                    'can_manage_companies' => $request->user()->canManageCompanies(),
                    'can_manage_users' => $request->user()->canManageUsers(),
                ] : null,
            ],
            'stats' => $stats,
            'ziggy' => fn (): array => [
                ...(new Ziggy)->toArray(),
                'location' => $request->url(),
            ],
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
        ];
    }
}
