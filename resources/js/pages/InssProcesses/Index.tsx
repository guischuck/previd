import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/react';
import { useState } from 'react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Badge } from '@/components/ui/badge';
import { Calendar, FileText, CheckCircle, AlertCircle, Users, Search, Filter, X, ExternalLink } from 'lucide-react';
import { cn } from '@/lib/utils';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
    {
        title: 'Processos INSS',
        href: '/inss-processes',
    },
];

interface Processo {
    id: number;
    protocolo: string;
    nome: string;
    cpf: string;
    servico: string;
    situacao: string;
    protocolado_em: string;
    ultima_atualizacao: string;
}

interface ProcessosIndexProps {
    processos: {
        data: Processo[];
        total: number;
        current_page: number;
        last_page: number;
    };
    stats: {
        processos_ativos: number;
        processos_concluidos: number;
        processos_exigencia: number;
        protocolados_hoje: number;
        total_processos: number;
    };
    statusOptions: string[];
    servicoOptions: string[];
    filters: {
        search?: string;
        status?: string;
        servico?: string;
        periodo?: string;
    };
    error?: string;
}

export default function InssProcessesIndex({ processos, stats, statusOptions, servicoOptions, filters, error }: ProcessosIndexProps) {
    const [searchTerm, setSearchTerm] = useState(filters.search || '');
    const [selectedStatus, setSelectedStatus] = useState(filters.status || 'all');
    const [selectedServico, setSelectedServico] = useState(filters.servico || 'all');
    const [selectedPeriodo, setSelectedPeriodo] = useState(filters.periodo || 'all');

    const handleFilter = () => {
        router.get('/inss-processes', {
            search: searchTerm,
            status: selectedStatus === 'all' ? '' : selectedStatus,
            servico: selectedServico === 'all' ? '' : selectedServico,
            periodo: selectedPeriodo === 'all' ? '' : selectedPeriodo,
        }, {
            preserveState: true,
        });
    };

    const clearFilters = () => {
        setSearchTerm('');
        setSelectedStatus('all');
        setSelectedServico('all');
        setSelectedPeriodo('all');
        router.get('/inss-processes');
    };

    const filterByStatus = (status: string) => {
        setSelectedStatus(status);
        
        router.get('/inss-processes', {
            search: searchTerm,
            status: status === 'all' ? '' : status,
            servico: selectedServico === 'all' ? '' : selectedServico,
            periodo: selectedPeriodo === 'all' ? '' : selectedPeriodo,
        }, {
            preserveState: true,
        });
    };

    const getStatusColor = (status: string) => {
        const normalizedStatus = status?.toUpperCase() || '';
        
        switch (normalizedStatus) {
            case 'EM ANÁLISE':
            case 'EM ANALISE':
                return 'bg-blue-100 text-blue-800 border-blue-200 dark:bg-blue-900/20 dark:text-blue-300 dark:border-blue-700';
            case 'EXIGÊNCIA':
            case 'EXIGENCIA':
                return 'bg-orange-100 text-orange-800 border-orange-200 dark:bg-orange-900/20 dark:text-orange-300 dark:border-orange-700';
            case 'CONCLUÍDA':
            case 'CONCLUIDA':
            case 'DEFERIDO':
            case 'APROVADO':
                return 'bg-green-100 text-green-800 border-green-200 dark:bg-green-900/20 dark:text-green-300 dark:border-green-700';
            case 'INDEFERIDO':
            case 'REJEITADO':
                return 'bg-red-100 text-red-800 border-red-200 dark:bg-red-900/20 dark:text-red-300 dark:border-red-700';
            default:
                return 'bg-gray-100 text-gray-800 border-gray-200 dark:bg-gray-900/20 dark:text-gray-300 dark:border-gray-700';
        }
    };

    const normalizeSituacao = (situacao: string): string => {
        if (!situacao) return 'N/A';
        
        const normalized = situacao.toUpperCase();
        switch (normalized) {
            case 'EM ANÁLISE':
            case 'EM ANALISE':
                return 'EM ANÁLISE';
            case 'EXIGÊNCIA':
            case 'EXIGENCIA':
                return 'EXIGÊNCIA';
            case 'CONCLUÍDA':
            case 'CONCLUIDA':
                return 'CONCLUÍDA';
            default:
                return normalized;
        }
    };

    const formatDate = (dateString: string) => {
        if (!dateString) return 'N/A';
        return new Date(dateString).toLocaleDateString('pt-BR', {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
        });
    };

    const calculateExigenciaDeadline = (ultimaAtualizacao: string) => {
        if (!ultimaAtualizacao) return null;
        const dataUltimaAtualizacao = new Date(ultimaAtualizacao);
        const prazoExigencia = new Date(dataUltimaAtualizacao);
        prazoExigencia.setDate(prazoExigencia.getDate() + 30);
        return prazoExigencia;
    };

    const formatDeadline = (deadline: Date) => {
        return deadline.toLocaleDateString('pt-BR', {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric',
        });
    };

    const getDeadlineStatus = (deadline: Date) => {
        const hoje = new Date();
        const diffTime = deadline.getTime() - hoje.getTime();
        const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
        
        if (diffDays < 0) {
            return { status: 'vencido', text: 'Vencido', color: 'bg-red-100 text-red-800 border-red-200 dark:bg-red-900/20 dark:text-red-300 dark:border-red-700' };
        } else if (diffDays <= 3) {
            return { status: 'urgente', text: 'Urgente', color: 'bg-orange-100 text-orange-800 border-orange-200 dark:bg-orange-900/20 dark:text-orange-300 dark:border-orange-700' };
        } else if (diffDays <= 7) {
            return { status: 'proximo', text: 'Próximo', color: 'bg-yellow-100 text-yellow-800 border-yellow-200 dark:bg-yellow-900/20 dark:text-yellow-300 dark:border-yellow-700' };
        } else {
            return { status: 'normal', text: 'Normal', color: 'bg-green-100 text-green-800 border-green-200 dark:bg-green-900/20 dark:text-green-300 dark:border-green-700' };
        }
    };

    if (error) {
        return (
            <AppLayout breadcrumbs={breadcrumbs}>
                <Head title="Processos - PrevidIA" />
                <div className="flex h-full flex-1 flex-col gap-6 p-4 md:p-6">
                    <div className="flex items-center justify-between">
                        <div>
                            <h1 className="text-2xl md:text-3xl font-bold">Processos INSS</h1>
                            <p className="text-muted-foreground text-sm md:text-base">
                                Gerencie seus processos do INSS
                            </p>
                        </div>
                    </div>

                    <Card className="p-8 text-center">
                        <CardHeader>
                            <CardTitle className="text-red-600">Erro ao carregar dados</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <p className="text-muted-foreground">{error}</p>
                        </CardContent>
                    </Card>
                </div>
            </AppLayout>
        );
    }

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Processos INSS" />
            <div className="flex h-full flex-1 flex-col gap-6 p-4 md:p-6">
                {/* Header */}
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-2xl md:text-3xl font-bold">Processos INSS</h1>
                        <p className="text-muted-foreground text-sm md:text-base">
                            Gerencie seus processos do INSS - {stats?.total_processos || 0} processos encontrados
                        </p>
                    </div>
                </div>

                {/* Stats Cards */}
                <div className="grid gap-4 grid-cols-1 sm:grid-cols-2 md:grid-cols-4">
                    <Card className={cn(
                        "cursor-pointer transition-all duration-200 hover:shadow-md",
                        selectedStatus === 'Em Análise' && "ring-2 ring-blue-500"
                    )} onClick={() => filterByStatus('Em Análise')}>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-xs md:text-sm font-medium">Em Análise</CardTitle>
                            <FileText className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-lg md:text-2xl font-bold">{stats?.processos_ativos || 0}</div>
                            <p className="text-xs text-muted-foreground mt-1">Ver todos ➜</p>
                        </CardContent>
                    </Card>
                    
                    <Card className={cn(
                        "cursor-pointer transition-all duration-200 hover:shadow-md",
                        selectedStatus === 'Concluída' && "ring-2 ring-green-500"
                    )} onClick={() => filterByStatus('Concluída')}>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-xs md:text-sm font-medium">Concluída</CardTitle>
                            <CheckCircle className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-lg md:text-2xl font-bold">{stats?.processos_concluidos || 0}</div>
                            <p className="text-xs text-muted-foreground mt-1">Ver todos ➜</p>
                        </CardContent>
                    </Card>
                    
                    <Card className={cn(
                        "cursor-pointer transition-all duration-200 hover:shadow-md",
                        selectedStatus === 'Exigência' && "ring-2 ring-orange-500"
                    )} onClick={() => filterByStatus('Exigência')}>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-xs md:text-sm font-medium">Em Exigência</CardTitle>
                            <AlertCircle className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-lg md:text-2xl font-bold">{stats?.processos_exigencia || 0}</div>
                            <p className="text-xs text-muted-foreground mt-1">Ver todos ➜</p>
                        </CardContent>
                    </Card>
                    <Card className={cn(
                        "transition-all duration-200 hover:shadow-md"
                    )}>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-xs md:text-sm font-medium">Protocolados Hoje</CardTitle>
                            <Calendar className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-lg md:text-2xl font-bold">{stats?.protocolados_hoje || 0}</div>
                        </CardContent>
                    </Card>
                </div>

                {/* Filtros */}
                <Card>
                    <CardHeader>
                        <CardTitle className="flex items-center gap-2 text-sm md:text-base">
                            <Filter className="h-4 w-4" />
                            Filtros
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-6 gap-4">
                            <div className="space-y-2 lg:col-span-2">
                                <label className="text-sm font-medium">Buscar</label>
                                <div className="relative">
                                    <Search className="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-muted-foreground" />
                                    <Input
                                        placeholder="Nome, CPF ou protocolo..."
                                        value={searchTerm}
                                        onChange={(e) => setSearchTerm(e.target.value)}
                                        className="pl-10"
                                    />
                                </div>
                            </div>
                            
                            <div className="space-y-2">
                                <label className="text-sm font-medium">Status</label>
                                <Select value={selectedStatus} onValueChange={setSelectedStatus}>
                                    <SelectTrigger>
                                        <SelectValue placeholder="Todos os status" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="all">Todos</SelectItem>
                                        {statusOptions?.map((status) => (
                                            <SelectItem key={status} value={status}>
                                                {status}
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                            </div>
                            
                            <div className="space-y-2">
                                <label className="text-sm font-medium">Serviço</label>
                                <Select value={selectedServico} onValueChange={setSelectedServico}>
                                    <SelectTrigger>
                                        <SelectValue placeholder="Todos os serviços" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="all">Todos</SelectItem>
                                        {servicoOptions?.map((servico) => (
                                            <SelectItem key={servico} value={servico}>
                                                {servico}
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                            </div>
                            
                            <div className="space-y-2">
                                <label className="text-sm font-medium">Período</label>
                                <div className="flex flex-col sm:flex-row gap-2">
                                    <Select value={selectedPeriodo} onValueChange={setSelectedPeriodo}>
                                        <SelectTrigger>
                                            <SelectValue placeholder="Selecionar" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="all">Todos</SelectItem>
                                            <SelectItem value="hoje">Hoje</SelectItem>
                                            <SelectItem value="semana">Esta semana</SelectItem>
                                            <SelectItem value="mes">Este mês</SelectItem>
                                            <SelectItem value="trimestre">Este trimestre</SelectItem>
                                        </SelectContent>
                                    </Select>
                                    <div className="flex gap-2">
                                        <Button onClick={handleFilter} className="flex-1 sm:flex-none">
                                            Filtrar
                                        </Button>
                                        <Button onClick={clearFilters} variant="outline" size="icon" className="shrink-0">
                                            <X className="h-4 w-4" />
                                        </Button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                {/* Lista de Processos */}
                <Card>
                    <CardHeader>
                        <CardTitle className="flex items-center gap-2 text-sm md:text-base">
                                <FileText className="h-4 w-4" />
                                    Processos ({processos?.data?.length || 0})
                                </CardTitle>
                    </CardHeader>
                    <CardContent className="p-0">
                        {processos?.data && processos.data.length > 0 ? (
                            <div className="space-y-0">
                                {/* Header da tabela - visível apenas em desktop */}
                                <div className="hidden md:grid grid-cols-12 gap-4 p-4 text-sm font-medium text-muted-foreground border-b bg-muted/30">
                                    <div className="col-span-2">PROTOCOLO</div>
                                    <div className="col-span-2">CLIENTE</div>
                                    <div className="col-span-2">SERVIÇO</div>
                                    <div className="col-span-2">SITUAÇÃO</div>
                                    <div className="col-span-2">ÚLTIMA ATUALIZAÇÃO</div>
                                    <div className="col-span-2">AÇÕES</div>
                                </div>

                                {/* Lista de processos */}
                                {processos.data.map((processo, index) => (
                                    <div 
                                        key={processo.id}
                                        className={cn(
                                            "md:grid md:grid-cols-12 gap-4 p-4 hover:bg-muted/50 transition-colors",
                                            index !== processos.data.length - 1 && "border-b"
                                        )}
                                    >
                                        {/* Visualização Mobile */}
                                        <div className="md:hidden space-y-3">
                                            <div className="flex justify-between items-start">
                                                <div>
                                                    <div className="font-mono text-sm font-medium">
                                                        {processo.protocolo}
                                                    </div>
                                                    <div className="font-medium mt-1">{processo.nome}</div>
                                                    <div className="text-sm text-muted-foreground">
                                                        CPF: {processo.cpf}
                                                    </div>
                                                </div>
                                                <Badge className={getStatusColor(processo.situacao)}>
                                                    {normalizeSituacao(processo.situacao)}
                                                </Badge>
                                            </div>

                                            <div className="space-y-2">
                                                <div className="text-sm">
                                                    <span className="text-muted-foreground">Serviço:</span> {processo.servico}
                                                </div>
                                                <div className="text-sm">
                                                    <span className="text-muted-foreground">Última atualização:</span> {formatDate(processo.ultima_atualizacao)}
                                                </div>
                                                {processo.situacao?.toUpperCase().includes('EXIGÊNCIA') || processo.situacao?.toUpperCase().includes('EXIGENCIA') ? (
                                                    (() => {
                                                        const deadline = calculateExigenciaDeadline(processo.ultima_atualizacao);
                                                        if (deadline) {
                                                            return (
                                                                <div className="text-sm">
                                                                    <span className="text-muted-foreground">Prazo:</span> {formatDeadline(deadline)}
                                                                </div>
                                                            );
                                                        }
                                                        return null;
                                                    })()
                                                ) : null}
                                            </div>

                                            <div className="pt-2">
                                                <Button
                                                    variant="outline"
                                                    size="sm"
                                                    className="w-full"
                                                    asChild
                                                >
                                                    <a 
                                                        href={`https://atendimento.inss.gov.br/tarefas/detalhar_tarefa/${processo.protocolo}`}
                                                        target="_blank" 
                                                        rel="noopener noreferrer"
                                                        className="flex items-center justify-center gap-1"
                                                    >
                                                        <ExternalLink className="h-4 w-4" />
                                                        Ver no INSS
                                                    </a>
                                                </Button>
                                            </div>
                                        </div>

                                        {/* Visualização Desktop */}
                                        <div className="hidden md:block col-span-2">
                                            <div className="font-mono text-sm font-medium">
                                                {processo.protocolo}
                                            </div>
                                        </div>
                                        
                                        <div className="hidden md:block col-span-2">
                                            <div className="font-medium">{processo.nome}</div>
                                            <div className="text-sm text-muted-foreground">
                                                CPF: {processo.cpf}
                                            </div>
                                        </div>
                                        
                                        <div className="hidden md:block col-span-2">
                                            <div className="text-sm">{processo.servico}</div>
                                        </div>
                                        
                                        <div className="hidden md:block col-span-2">
                                            <div className="space-y-2">
                                                <Badge className={getStatusColor(processo.situacao)}>
                                                    {normalizeSituacao(processo.situacao)}
                                                </Badge>
                                                {processo.situacao?.toUpperCase().includes('EXIGÊNCIA') || processo.situacao?.toUpperCase().includes('EXIGENCIA') ? (
                                                    (() => {
                                                        const deadline = calculateExigenciaDeadline(processo.ultima_atualizacao);
                                                        if (deadline) {
                                                            return (
                                                                <div className="text-xs text-muted-foreground">
                                                                    Prazo: {formatDeadline(deadline)}
                                                                </div>
                                                            );
                                                        }
                                                        return null;
                                                    })()
                                                ) : null}
                                            </div>
                                        </div>
                                        
                                        <div className="hidden md:block col-span-2">
                                            <div className="text-sm">{formatDate(processo.ultima_atualizacao)}</div>
                                        </div>
                                        
                                        <div className="hidden md:block col-span-2">
                                            <div className="flex items-center gap-2">
                                                <Button
                                                    variant="outline"
                                                    size="sm"
                                                    asChild
                                                >
                                                    <a 
                                                        href={`https://atendimento.inss.gov.br/tarefas/detalhar_tarefa/${processo.protocolo}`}
                                                        target="_blank" 
                                                        rel="noopener noreferrer"
                                                        className="flex items-center gap-1"
                                                    >
                                                        <ExternalLink className="h-4 w-4" />
                                                        Ver no INSS
                                                    </a>
                                                </Button>
                                            </div>
                                        </div>
                                    </div>
                                ))}

                                {/* Paginação */}
                                {processos.last_page > 1 && (
                                    <div className="flex flex-col sm:flex-row items-center justify-center gap-4 p-4 border-t">
                                        <div className="text-sm text-muted-foreground">
                                            Página {processos.current_page} de {processos.last_page}
                                        </div>
                                        <div className="flex gap-2">
                                            <Button
                                                onClick={() => router.get('/inss-processes', {
                                                    page: processos.current_page - 1,
                                                    search: searchTerm,
                                                    status: selectedStatus === 'all' ? '' : selectedStatus,
                                                    servico: selectedServico === 'all' ? '' : selectedServico,
                                                    periodo: selectedPeriodo === 'all' ? '' : selectedPeriodo,
                                                }, { preserveState: true })}
                                                variant="outline"
                                                size="sm"
                                                disabled={processos.current_page === 1}
                                                className="w-24"
                                            >
                                                Anterior
                                            </Button>
                                            <Button
                                                onClick={() => router.get('/inss-processes', {
                                                    page: processos.current_page + 1,
                                                    search: searchTerm,
                                                    status: selectedStatus === 'all' ? '' : selectedStatus,
                                                    servico: selectedServico === 'all' ? '' : selectedServico,
                                                    periodo: selectedPeriodo === 'all' ? '' : selectedPeriodo,
                                                }, { preserveState: true })}
                                                variant="outline"
                                                size="sm"
                                                disabled={processos.current_page === processos.last_page}
                                                className="w-24"
                                            >
                                                Próxima
                                            </Button>
                                        </div>
                                    </div>
                                )}
                            </div>
                        ) : (
                            <div className="py-12 text-center">
                                <Users className="mx-auto h-12 w-12 text-muted-foreground mb-4" />
                                <h3 className="text-lg font-semibold mb-2">Nenhum processo encontrado</h3>
                                <p className="mb-4 text-muted-foreground">
                                    {searchTerm || (selectedStatus !== 'all') || (selectedServico !== 'all') || (selectedPeriodo !== 'all') 
                                        ? 'Nenhum processo encontrado com os filtros aplicados' 
                                        : 'Nenhum processo encontrado com status: Em Análise, Em Exigência ou Concluída'}
                                </p>
                                {(searchTerm || (selectedStatus !== 'all') || (selectedServico !== 'all') || (selectedPeriodo !== 'all')) && (
                                    <Button onClick={clearFilters} variant="outline">
                                        Limpar filtros
                                    </Button>
                                )}
                            </div>
                        )}
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}