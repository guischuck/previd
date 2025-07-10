import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/react';

import { Briefcase, Plus, Search, List, Grid3X3 } from 'lucide-react';
import { useState, useEffect } from 'react';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
    {
        title: 'Casos',
        href: '/cases',
    },
];

interface Case {
    id: number;
    case_number: string;
    client_name: string;
    client_cpf: string;
    status: string;
    created_at: string;
    assigned_to?: {
        id: number;
        name: string;
    };
}

interface CasesIndexProps {
    cases: {
        data: Case[];
        total: number;
    };
    users: Array<{ id: number; name: string }>;
    statuses: Record<string, string>;
    filters: {
        search?: string;
        status?: string;
        assigned_to?: string;
    };
}

export default function CasesIndex({ cases, users, statuses, filters }: CasesIndexProps) {
    const [searchTerm, setSearchTerm] = useState(filters.search || '');
    const [casesData, setCasesData] = useState(cases.data);
    const [viewMode, setViewMode] = useState<'list' | 'kanban'>('list');

    // Listener para atualizações de progresso em tempo real
    useEffect(() => {
        const handleProgressUpdate = (event: CustomEvent) => {
            const { caseId, progress, status } = event.detail;
            
            // Atualizar o status do caso na lista
            setCasesData(prevCases => 
                prevCases.map(case_ => 
                    case_.id === caseId 
                        ? { ...case_, status: status }
                        : case_
                )
            );
        };

        // Adicionar listener para eventos de atualização de progresso
        window.addEventListener('caseProgressUpdated' as any, handleProgressUpdate);

        // Cleanup do listener
        return () => {
            window.removeEventListener('caseProgressUpdated' as any, handleProgressUpdate);
        };
    }, []);

    // Função helper para obter o texto do status
    const getStatusText = (status: string) => {
        const statusMap: Record<string, string> = {
            pendente: 'Pendente',
            em_coleta: 'Em Coleta',
            aguarda_peticao: 'Aguarda Petição',
            protocolado: 'Protocolado',
            concluido: 'Concluído',
            rejeitado: 'Rejeitado',
        };

        return statusMap[status] || statuses[status] || status;
    };

    // Função helper para obter a cor do status
    const getStatusColor = (status: string) => {
        switch (status) {
            case 'pendente':
                return 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200';
            case 'em_coleta':
                return 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200';
            case 'aguarda_peticao':
                return 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200';
            case 'protocolado':
                return 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200';
            case 'concluido':
                return 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200';
            case 'arquivado':
                return 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200';
            default:
                return 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200';
        }
    };

    const handleSearch = () => {
        router.get(
            '/cases',
            {
                search: searchTerm,
            },
            {
                preserveState: true,
            },
        );
    };

    const handleKeyPress = (e: React.KeyboardEvent) => {
        if (e.key === 'Enter') {
            handleSearch();
        }
    };

    // Organizar casos por status para o Kanban
    const getCasesByStatus = () => {
        const statusColumns = {
            pendente: { title: 'Pendente', cases: [] as Case[] },
            em_coleta: { title: 'Em Coleta', cases: [] as Case[] },
            aguarda_peticao: { title: 'Aguarda Petição', cases: [] as Case[] },
            protocolado: { title: 'Protocolado', cases: [] as Case[] },
            concluido: { title: 'Concluído', cases: [] as Case[] },
            arquivado: { title: 'Arquivado', cases: [] as Case[] },
        };

        casesData.forEach(case_ => {
            if (statusColumns[case_.status as keyof typeof statusColumns]) {
                statusColumns[case_.status as keyof typeof statusColumns].cases.push(case_);
            } else {
                // Casos com status desconhecido vão para "Pendente"
                statusColumns.pendente.cases.push(case_);
            }
        });

        return statusColumns;
    };

    const statusColumns = getCasesByStatus();

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Casos - Sistema Jurídico" />
            <div className="flex h-full flex-1 flex-col gap-6 p-6">
                {/* Header */}
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-3xl font-bold">Casos</h1>
                        <p className="text-muted-foreground">Gerencie os casos jurídicos dos clientes</p>
                    </div>
                    <Link href="/cases/create">
                        <Button>
                            <Plus className="mr-2 h-4 w-4" />
                            Novo Caso
                        </Button>
                    </Link>
                </div>

                {/* Search Bar */}
                <Card>
                    <CardContent className="pt-6">
                        <div className="flex items-center space-x-2">
                            <div className="relative flex-1">
                                <Search className="absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 transform text-muted-foreground" />
                                <Input
                                    placeholder="Buscar por nome do cliente..."
                                    value={searchTerm}
                                    onChange={(e) => setSearchTerm(e.target.value)}
                                    onKeyPress={handleKeyPress}
                                    className="pl-10"
                                />
                            </div>
                            <Button onClick={handleSearch} variant="outline">
                                Buscar
                            </Button>
                        </div>
                    </CardContent>
                </Card>

                {/* View Mode Toggle */}
                <Card>
                    <CardContent className="pt-6">
                        <div className="flex items-center justify-between">
                            <div className="flex items-center space-x-2">
                                <span className="text-sm font-medium">Modo de Visualização:</span>
                                <div className="flex rounded-lg border">
                                    <Button
                                        variant={viewMode === 'list' ? 'default' : 'ghost'}
                                        size="sm"
                                        onClick={() => setViewMode('list')}
                                        className="rounded-r-none"
                                    >
                                        <List className="h-4 w-4 mr-2" />
                                        Lista
                                    </Button>
                                    <Button
                                        variant={viewMode === 'kanban' ? 'default' : 'ghost'}
                                        size="sm"
                                        onClick={() => setViewMode('kanban')}
                                        className="rounded-l-none"
                                    >
                                        <Grid3X3 className="h-4 w-4 mr-2" />
                                        Kanban
                                    </Button>
                                </div>
                            </div>
                            <div className="text-sm text-muted-foreground">
                                {casesData.length} caso{casesData.length !== 1 ? 's' : ''} encontrado{casesData.length !== 1 ? 's' : ''}
                            </div>
                        </div>
                    </CardContent>
                </Card>

                {/* Cases Content */}
                {casesData && casesData.length > 0 ? (
                    viewMode === 'list' ? (
                        /* List View */
                        <Card>
                            <CardHeader>
                                <CardTitle>Casos ({cases?.total || 0})</CardTitle>
                                <CardDescription>Lista de todos os casos jurídicos cadastrados no sistema</CardDescription>
                            </CardHeader>
                            <CardContent>
                                <div className="space-y-4">
                                    {casesData.map((case_) => (
                                        <div
                                            key={case_.id}
                                            className="flex items-center justify-between rounded-lg border p-4 transition-colors hover:bg-accent hover:text-accent-foreground"
                                        >
                                            <div className="flex-1">
                                                <div className="flex items-center space-x-3">
                                                    <div>
                                                        <h3 className="text-lg font-medium">{case_.client_name}</h3>
                                                        <p className="text-sm text-muted-foreground">CPF: {case_.client_cpf}</p>
                                                    </div>
                                                    <div className="flex items-center">
                                                        <span
                                                            className={`rounded-full px-3 py-1.5 text-xs font-semibold tracking-wide uppercase ${getStatusColor(case_.status)} transition-all duration-200 hover:scale-105`}
                                                        >
                                                            {getStatusText(case_.status)}
                                                        </span>
                                                    </div>
                                                </div>
                                                <div className="mt-2 text-sm text-muted-foreground">
                                                    <span>Criado em: {new Date(case_.created_at).toLocaleDateString('pt-BR')}</span>
                                                </div>
                                            </div>
                                            <div className="flex items-center space-x-2">
                                                <Link href={`/cases/${case_.id}/vinculos`}>
                                                    <Button variant="outline" size="sm">
                                                        Vínculos
                                                    </Button>
                                                </Link>
                                                <Link href={`/cases/${case_.id}`}>
                                                    <Button variant="outline" size="sm">
                                                        Ver
                                                    </Button>
                                                </Link>
                                                <Link href={`/cases/${case_.id}/edit`}>
                                                    <Button variant="outline" size="sm">
                                                        Editar
                                                    </Button>
                                                </Link>
                                            </div>
                                        </div>
                                    ))}
                                </div>
                            </CardContent>
                        </Card>
                    ) : (
                        /* Kanban View */
                        <div className="space-y-6">
                            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-4">
                                {Object.entries(statusColumns).map(([statusKey, column]) => (
                                    <div key={statusKey} className="space-y-4">
                                        <div className="flex items-center justify-between">
                                            <h3 className="font-semibold text-sm">{column.title}</h3>
                                            <span className="text-xs bg-muted px-2 py-1 rounded-full">
                                                {column.cases.length}
                                            </span>
                                        </div>
                                        <div className="space-y-3">
                                            {column.cases.map((case_) => (
                                                <Card key={case_.id} className="cursor-pointer hover:shadow-md transition-shadow">
                                                    <CardContent className="p-4">
                                                        <div className="space-y-3">
                                                            <div>
                                                                <h4 className="font-medium text-sm line-clamp-2">
                                                                    {case_.client_name}
                                                                </h4>
                                                                <p className="text-xs text-muted-foreground">
                                                                    CPF: {case_.client_cpf}
                                                                </p>
                                                            </div>
                                                            
                                                            <div className="flex items-center justify-between">
                                                                <span className="text-xs text-muted-foreground">
                                                                    {new Date(case_.created_at).toLocaleDateString('pt-BR')}
                                                                </span>
                                                                <span
                                                                    className={`rounded-full px-2 py-1 text-xs font-semibold ${getStatusColor(case_.status)}`}
                                                                >
                                                                    {getStatusText(case_.status)}
                                                                </span>
                                                            </div>

                                                            <div className="flex items-center gap-2 pt-2 border-t">
                                                                <Link href={`/cases/${case_.id}`} className="flex-1">
                                                                    <Button variant="outline" size="sm" className="w-full text-xs">
                                                                        Ver
                                                                    </Button>
                                                                </Link>
                                                                <Link href={`/cases/${case_.id}/edit`}>
                                                                    <Button variant="ghost" size="sm" className="text-xs">
                                                                        Editar
                                                                    </Button>
                                                                </Link>
                                                            </div>
                                                        </div>
                                                    </CardContent>
                                                </Card>
                                            ))}
                                        </div>
                                    </div>
                                ))}
                            </div>
                        </div>
                    )
                ) : (
                    /* Empty State */
                    <Card>
                        <CardContent className="py-12 text-center">
                            <Briefcase className="mx-auto mb-4 h-12 w-12 text-muted-foreground" />
                            <h3 className="mb-2 text-lg font-medium">Nenhum caso encontrado</h3>
                            <p className="mb-4 text-muted-foreground">
                                {searchTerm ? 'Nenhum caso encontrado com os critérios de busca' : 'Comece criando seu primeiro caso jurídico'}
                            </p>
                            {!searchTerm && (
                                <Link href="/cases/create">
                                    <Button>
                                        <Plus className="mr-2 h-4 w-4" />
                                        Criar Primeiro Caso
                                    </Button>
                                </Link>
                            )}
                        </CardContent>
                    </Card>
                )}
            </div>
        </AppLayout>
    );
}
