import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/react';
import { useState } from 'react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { AlertCircle, CheckCircle, Settings, User, Users, Briefcase, Calendar } from 'lucide-react';
import { cn } from '@/lib/utils';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
    {
        title: 'Usuários',
        href: '/usuarios',
    },
];

interface AdvboxSettings {
    success: boolean;
    data: {
        id: number;
        name: string;
        email: string;
        phone: string;
        company: {
            id: number;
            name: string;
            document: string;
            address: string;
            city: string;
            state: string;
            zip_code: string;
            phone: string;
            email: string;
            website: string;
            logo: string;
        };
        subscription: {
            id: number;
            name: string;
            description: string;
            price: number;
            billing_cycle: string;
            trial_days: number;
            features: string[];
            active: boolean;
            created_at: string;
            expires_at: string;
        };
        role: {
            id: number;
            name: string;
            description: string;
        };
        permissions: string[];
        created_at: string;
        updated_at: string;
    } | null;
    error: string | null;
}

interface UsuariosIndexProps {
    advboxSettings: AdvboxSettings;
}

export default function UsuariosIndex({ advboxSettings }: UsuariosIndexProps) {
    const [isLoading, setIsLoading] = useState(false);

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Usuários - Integração AdvBox" />
            
            <div className="space-y-6">
                <div className="flex items-center justify-between">
                    <h1 className="text-2xl font-bold">Configurações de Usuário - AdvBox</h1>
                </div>

                {advboxSettings.error && (
                    <div className="rounded-lg border border-red-200 bg-red-50 p-4">
                        <div className="flex items-center gap-3">
                            <AlertCircle className="h-5 w-5 text-red-600" />
                            <span className="font-medium text-red-800">
                                {advboxSettings.error}
                            </span>
                        </div>
                    </div>
                )}

                {advboxSettings.success && advboxSettings.data && (
                    <div className="grid gap-6 md:grid-cols-2">
                        <Card>
                            <CardHeader className="pb-2">
                                <CardTitle className="flex items-center gap-2">
                                    <User className="h-5 w-5 text-primary" />
                                    Informações do Usuário
                                </CardTitle>
                            </CardHeader>
                            <CardContent>
                                <div className="space-y-4">
                                    <div>
                                        <p className="text-sm font-medium text-muted-foreground">Nome</p>
                                        <p className="text-base">{advboxSettings.data.name}</p>
                                    </div>
                                    <div>
                                        <p className="text-sm font-medium text-muted-foreground">Email</p>
                                        <p className="text-base">{advboxSettings.data.email}</p>
                                    </div>
                                    <div>
                                        <p className="text-sm font-medium text-muted-foreground">Telefone</p>
                                        <p className="text-base">{advboxSettings.data.phone || 'Não informado'}</p>
                                    </div>
                                    <div>
                                        <p className="text-sm font-medium text-muted-foreground">Função</p>
                                        <Badge variant="outline" className="mt-1">
                                            {advboxSettings.data.role.name}
                                        </Badge>
                                    </div>
                                    <div>
                                        <p className="text-sm font-medium text-muted-foreground">Conta criada em</p>
                                        <p className="text-base">
                                            {new Date(advboxSettings.data.created_at).toLocaleDateString('pt-BR')}
                                        </p>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>

                        <Card>
                            <CardHeader className="pb-2">
                                <CardTitle className="flex items-center gap-2">
                                    <Briefcase className="h-5 w-5 text-primary" />
                                    Informações da Empresa
                                </CardTitle>
                            </CardHeader>
                            <CardContent>
                                <div className="space-y-4">
                                    <div>
                                        <p className="text-sm font-medium text-muted-foreground">Nome da Empresa</p>
                                        <p className="text-base">{advboxSettings.data.company.name}</p>
                                    </div>
                                    <div>
                                        <p className="text-sm font-medium text-muted-foreground">Documento</p>
                                        <p className="text-base">{advboxSettings.data.company.document}</p>
                                    </div>
                                    <div>
                                        <p className="text-sm font-medium text-muted-foreground">Endereço</p>
                                        <p className="text-base">
                                            {advboxSettings.data.company.address}, {advboxSettings.data.company.city}/{advboxSettings.data.company.state}
                                        </p>
                                    </div>
                                    <div>
                                        <p className="text-sm font-medium text-muted-foreground">Contato</p>
                                        <p className="text-base">
                                            {advboxSettings.data.company.phone} | {advboxSettings.data.company.email}
                                        </p>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>

                        <Card className="md:col-span-2">
                            <CardHeader className="pb-2">
                                <CardTitle className="flex items-center gap-2">
                                    <Settings className="h-5 w-5 text-primary" />
                                    Assinatura
                                </CardTitle>
                            </CardHeader>
                            <CardContent>
                                <div className="space-y-4">
                                    <div className="flex items-center justify-between">
                                        <div>
                                            <p className="text-sm font-medium text-muted-foreground">Plano</p>
                                            <p className="text-base font-medium">{advboxSettings.data.subscription.name}</p>
                                        </div>
                                        <Badge 
                                            className={cn(
                                                "text-xs font-medium",
                                                advboxSettings.data.subscription.active 
                                                    ? "bg-green-100 text-green-800" 
                                                    : "bg-red-100 text-red-800"
                                            )}
                                        >
                                            {advboxSettings.data.subscription.active ? 'Ativo' : 'Inativo'}
                                        </Badge>
                                    </div>
                                    
                                    <div>
                                        <p className="text-sm font-medium text-muted-foreground">Descrição</p>
                                        <p className="text-base">{advboxSettings.data.subscription.description}</p>
                                    </div>
                                    
                                    <div className="grid grid-cols-2 gap-4">
                                        <div>
                                            <p className="text-sm font-medium text-muted-foreground">Valor</p>
                                            <p className="text-base">
                                                {new Intl.NumberFormat('pt-BR', { 
                                                    style: 'currency', 
                                                    currency: 'BRL' 
                                                }).format(advboxSettings.data.subscription.price)}
                                            </p>
                                        </div>
                                        <div>
                                            <p className="text-sm font-medium text-muted-foreground">Ciclo de Cobrança</p>
                                            <p className="text-base">{advboxSettings.data.subscription.billing_cycle}</p>
                                        </div>
                                    </div>
                                    
                                    <div className="grid grid-cols-2 gap-4">
                                        <div>
                                            <p className="text-sm font-medium text-muted-foreground">Data de Criação</p>
                                            <p className="text-base">
                                                {new Date(advboxSettings.data.subscription.created_at).toLocaleDateString('pt-BR')}
                                            </p>
                                        </div>
                                        <div>
                                            <p className="text-sm font-medium text-muted-foreground">Data de Expiração</p>
                                            <p className="text-base">
                                                {advboxSettings.data.subscription.expires_at 
                                                    ? new Date(advboxSettings.data.subscription.expires_at).toLocaleDateString('pt-BR')
                                                    : 'Não expira'}
                                            </p>
                                        </div>
                                    </div>
                                    
                                    <div>
                                        <p className="text-sm font-medium text-muted-foreground">Recursos Incluídos</p>
                                        <div className="mt-2 flex flex-wrap gap-2">
                                            {advboxSettings.data.subscription.features.map((feature, index) => (
                                                <Badge key={index} variant="secondary" className="flex items-center gap-1">
                                                    <CheckCircle className="h-3 w-3" />
                                                    {feature}
                                                </Badge>
                                            ))}
                                        </div>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>
                    </div>
                )}

                {!advboxSettings.success && !advboxSettings.error && (
                    <div className="flex flex-col items-center justify-center rounded-lg border border-dashed p-8">
                        <Settings className="mb-4 h-12 w-12 text-muted-foreground" />
                        <h3 className="mb-2 text-lg font-medium">Configurações não disponíveis</h3>
                        <p className="text-center text-muted-foreground">
                            Não foi possível carregar as configurações do AdvBox.
                            Verifique se a API key está configurada corretamente.
                        </p>
                    </div>
                )}
            </div>
        </AppLayout>
    );
}
