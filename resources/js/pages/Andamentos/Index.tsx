import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/react';
import { useState } from 'react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Badge } from '@/components/ui/badge';
import { 
    CalendarIcon, 
    FileText, 
    CheckCircle, 
    AlertCircle, 
    Users, 
    Search, 
    Filter, 
    X, 
    ExternalLink,
    Eye,
    Clock,
    ArrowUpDown,
    Loader2,
    MessageSquare,
    Copy
} from 'lucide-react';
import { cn } from '@/lib/utils';
import axios from 'axios';
import { toast } from 'react-toastify';
import AdvboxTaskModal from '@/components/modals/AdvboxTaskModal';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
} from "@/components/ui/dialog";

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
    {
        title: 'Andamentos dos Processos',
        href: '/andamentos',
    },
];

interface HistoricoSituacao {
    id: number;
    situacao_anterior: string | null;
    situacao_atual: string;
    data_mudanca: string;
    visto: boolean;
    visto_em: string | null;
    processo: {
        id: number;
        protocolo: string;
        nome: string;
        cpf: string;
        servico: string;
        situacao: string;
        ultima_atualizacao: string;
    };
    despacho?: {
        id: number;
        protocolo: string;
        servico: string;
        conteudo: string;
        data_email: string;
    };
}

interface AndamentosIndexProps {
    andamentos: {
        data: HistoricoSituacao[];
        total: number;
        current_page: number;
        last_page: number;
    };
    stats: {
        mudancas_hoje: number;
        mudancas_semana: number;
        mudancas_mes: number;
        nao_vistos: number;
        total_mudancas: number;
    };
    situacaoOptions: string[];
    situacaoAnteriorOptions: string[];
    filters: {
        search?: string;
        nova_situacao?: string;
        situacao_anterior?: string;
        visualizacao?: string;
        periodo?: string;
    };
    error?: string;
}

interface DashboardData {
    mudancas_hoje: number;
    mudancas_semana: number;
    mudancas_mes: number;
    nao_vistos: number;
    total_mudancas: number;
}

export default function AndamentosIndex({ 
    andamentos, 
    stats, 
    situacaoOptions, 
    situacaoAnteriorOptions, 
    filters, 
    error 
}: AndamentosIndexProps) {
    const [searchTerm, setSearchTerm] = useState(filters.search || '');
    const [selectedNovaSituacao, setSelectedNovaSituacao] = useState(filters.nova_situacao || 'all');
    const [selectedSituacaoAnterior, setSelectedSituacaoAnterior] = useState(filters.situacao_anterior || 'all');
    const [selectedVisualizacao, setSelectedVisualizacao] = useState(filters.visualizacao || 'nao_visto');
    const [selectedPeriodo, setSelectedPeriodo] = useState(filters.periodo || 'all');
    const [selectedAndamento, setSelectedAndamento] = useState<any>(null);
    const [isAdvboxModalOpen, setIsAdvboxModalOpen] = useState(false);
    const [isDespachoModalOpen, setIsDespachoModalOpen] = useState(false);
    const [loadingDespacho, setLoadingDespacho] = useState(false);
    const [despachoData, setDespachoData] = useState<any>(null);

    const handleFilter = () => {
        router.get('/andamentos', {
            search: searchTerm,
            nova_situacao: selectedNovaSituacao === 'all' ? '' : selectedNovaSituacao,
            situacao_anterior: selectedSituacaoAnterior === 'all' ? '' : selectedSituacaoAnterior,
            visualizacao: selectedVisualizacao === 'all' ? '' : selectedVisualizacao,
            periodo: selectedPeriodo === 'all' ? '' : selectedPeriodo,
        }, { preserveState: true });
    };

    const clearFilters = () => {
        setSearchTerm('');
        setSelectedNovaSituacao('all');
        setSelectedSituacaoAnterior('all');
        setSelectedVisualizacao('all');
        setSelectedPeriodo('all');
        router.get('/andamentos');
    };

    const handleQuickFilter = (type: string, value?: string) => {
        let params = {
            search: searchTerm,
            nova_situacao: selectedNovaSituacao === 'all' ? '' : selectedNovaSituacao,
            situacao_anterior: selectedSituacaoAnterior === 'all' ? '' : selectedSituacaoAnterior,
            visualizacao: selectedVisualizacao === 'all' ? '' : selectedVisualizacao,
            periodo: selectedPeriodo === 'all' ? '' : selectedPeriodo,
        };

        switch (type) {
            case 'hoje':
                params.periodo = 'hoje';
                setSelectedPeriodo('hoje');
                break;
            case 'semana':
                params.periodo = 'semana';
                setSelectedPeriodo('semana');
                break;
            case 'mes':
                params.periodo = 'mes';
                setSelectedPeriodo('mes');
                break;
            case 'nao_vistos':
                params.visualizacao = 'nao_visto';
                setSelectedVisualizacao('nao_visto');
                break;
        }

        router.get('/andamentos', params, { preserveState: true });
    };

    const getSituacaoStyle = (situacao: string): string => {
        const normalizedSituacao = situacao?.toUpperCase() || '';
        
        switch (normalizedSituacao) {
            case 'EM ANÁLISE':
                return 'bg-blue-100 text-blue-800 border-blue-200 dark:bg-blue-950 dark:text-blue-200 dark:border-blue-800';
            case 'EXIGÊNCIA':
                return 'bg-orange-100 text-orange-800 border-orange-200 dark:bg-orange-950 dark:text-orange-200 dark:border-orange-800';
            case 'CONCLUÍDA':
                return 'bg-green-100 text-green-800 border-green-200 dark:bg-green-950 dark:text-green-200 dark:border-green-800';
            default:
                return 'bg-gray-100 text-gray-800 border-gray-200 dark:bg-gray-950 dark:text-gray-200 dark:border-gray-800';
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

    const formatDate = (dateString: string): string => {
        return new Date(dateString).toLocaleDateString('pt-BR', {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
        });
    };

    const markAsViewed = (id: number) => {
        router.patch(`/andamentos/${id}/marcar-visto`, {}, {
            preserveState: true,
            onSuccess: () => {
                router.reload({ only: ['andamentos', 'stats'] });
            }
        });
    };

    const markAllAsViewed = () => {
        router.patch('/andamentos/marcar-todos-vistos', {
            search: searchTerm,
            nova_situacao: selectedNovaSituacao === 'all' ? '' : selectedNovaSituacao,
            situacao_anterior: selectedSituacaoAnterior === 'all' ? '' : selectedSituacaoAnterior,
            visualizacao: selectedVisualizacao === 'all' ? '' : selectedVisualizacao,
            periodo: selectedPeriodo === 'all' ? '' : selectedPeriodo,
        }, { preserveState: true, onSuccess: () => {} });
    };

    const handleAddToAdvbox = (andamento: any) => {
        setSelectedAndamento(andamento);
        setIsAdvboxModalOpen(true);
    };

    const handleVerDespacho = async (andamento: HistoricoSituacao) => {
        try {
            setLoadingDespacho(true);
            setIsDespachoModalOpen(true);
            setSelectedAndamento(andamento); // Armazena o andamento selecionado

            // Se já temos o despacho no andamento, use-o
            if (andamento.despacho) {
                setDespachoData(andamento.despacho);
                return;
            }

            // Caso contrário, busque do servidor
            const response = await axios.get(`/andamentos/${andamento.processo.protocolo}/despacho`);
            setDespachoData(response.data.despacho);
        } catch (error: any) {
            toast.error(error.response?.data?.error || 'Erro ao carregar despacho');
        } finally {
            setLoadingDespacho(false);
        }
    };

    // Função para abrir o processo no INSS
    const handleVerNoInss = (protocolo: string) => {
        window.open(`https://atendimento.inss.gov.br/tarefas/detalhar_tarefa/${protocolo}`, '_blank');
    };

    if (error) {
        return (
            <AppLayout breadcrumbs={breadcrumbs}>
                <Head title="Andamentos" />
                <div className="flex h-full flex-1 flex-col gap-6 p-4 md:p-6">
                    <div className="flex items-center justify-between">
                        <div>
                            <h1 className="text-2xl md:text-3xl font-bold">Andamentos</h1>
                            <p className="text-muted-foreground text-sm md:text-base">Acompanhe as mudanças de situação dos processos</p>
                        </div>
                    </div>
                    <Card className="p-8 text-center bg-card text-card-foreground">
                        <CardContent>
                            <AlertCircle className="text-red-600" />
                        </CardContent>
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
            <Head title="Andamentos - PrevidIA" />
            <div className="flex h-full flex-1 flex-col gap-6 p-4 md:p-6">
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-2xl md:text-3xl font-bold">Andamentos</h1>
                        <p className="text-muted-foreground text-sm md:text-base">
                            Acompanhe as mudanças de situação dos processos - {stats?.total_mudancas || 0} alterações encontradas
                        </p>
                    </div>
                </div>

                {/* Cards de Estatísticas - Responsivos */}
                <div className="grid gap-4 grid-cols-2 md:grid-cols-4">
                    <Card className="bg-card text-card-foreground">
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-xs md:text-sm font-medium">Hoje</CardTitle>
                            <CalendarIcon className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-lg md:text-2xl font-bold">{stats?.mudancas_hoje || 0}</div>
                            <Button 
                                variant="link" 
                                className="h-auto p-0 text-xs text-muted-foreground hover:underline"
                                onClick={() => handleQuickFilter('hoje')}
                            >
                                Ver mudanças →
                            </Button>
                        </CardContent>
                    </Card>
                    
                    <Card className="bg-card text-card-foreground">
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-xs md:text-sm font-medium">Esta Semana</CardTitle>
                            <Clock className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-lg md:text-2xl font-bold">{stats?.mudancas_semana || 0}</div>
                            <Button 
                                variant="link" 
                                className="h-auto p-0 text-xs text-muted-foreground hover:underline"
                                onClick={() => handleQuickFilter('semana')}
                            >
                                Ver mudanças →
                            </Button>
                        </CardContent>
                    </Card>
                    
                    <Card className="bg-card text-card-foreground">
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-xs md:text-sm font-medium">Este Mês</CardTitle>
                            <FileText className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-lg md:text-2xl font-bold">{stats?.mudancas_mes || 0}</div>
                            <Button 
                                variant="link" 
                                className="h-auto p-0 text-xs text-muted-foreground hover:underline"
                                onClick={() => handleQuickFilter('mes')}
                            >
                                Ver mudanças →
                            </Button>
                        </CardContent>
                    </Card>
                    
                    <Card className="bg-card text-card-foreground">
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-xs md:text-sm font-medium">Não Vistos</CardTitle>
                            <Eye className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-lg md:text-2xl font-bold">{stats?.nao_vistos || 0}</div>
                            <Button 
                                variant="link" 
                                className="h-auto p-0 text-xs text-muted-foreground hover:underline"
                                onClick={() => handleQuickFilter('nao_vistos')}
                            >
                                Ver não vistos →
                            </Button>
                        </CardContent>
                    </Card>
                </div>

                {/* Filtros - Layout melhorado para mobile */}
                <Card className="bg-card text-card-foreground">
                    <CardHeader>
                        <CardTitle className="flex items-center gap-2 text-sm md:text-base">
                            <Filter className="h-4 w-4" />
                            Filtros
                            {stats?.nao_vistos > 0 && (
                                <Button 
                                    onClick={markAllAsViewed}
                                    variant="outline" 
                                    size="sm"
                                    className="ml-auto"
                                >
                                    Marcar Todos como Vistos
                                </Button>
                            )}
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
                                <label className="text-sm font-medium">Nova Situação</label>
                                <Select value={selectedNovaSituacao} onValueChange={setSelectedNovaSituacao}>
                                    <SelectTrigger>
                                        <SelectValue placeholder="Selecionar" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="all">Todas</SelectItem>
                                        {situacaoOptions.map((situacao) => (
                                            <SelectItem key={situacao} value={situacao}>
                                                {normalizeSituacao(situacao)}
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                            </div>
                            
                            <div className="space-y-2">
                                <label className="text-sm font-medium">Situação Anterior</label>
                                <Select value={selectedSituacaoAnterior} onValueChange={setSelectedSituacaoAnterior}>
                                    <SelectTrigger>
                                        <SelectValue placeholder="Selecionar" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="all">Todas</SelectItem>
                                        {situacaoAnteriorOptions.map((situacao) => (
                                            <SelectItem key={situacao} value={situacao}>
                                                {normalizeSituacao(situacao)}
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                            </div>
                            
                            <div className="space-y-2">
                                <label className="text-sm font-medium">Visualização</label>
                                <Select value={selectedVisualizacao} onValueChange={setSelectedVisualizacao}>
                                    <SelectTrigger>
                                        <SelectValue placeholder="Selecionar" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="all">Todos</SelectItem>
                                        <SelectItem value="visto">Vistos</SelectItem>
                                        <SelectItem value="nao_visto">Não vistos</SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>
                            
                            <div className="space-y-2">
                                <label className="text-sm font-medium">Período</label>
                                <div className="flex gap-2">
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
                                    <Button onClick={handleFilter} className="shrink-0">
                                        Filtrar
                                    </Button>
                                    <Button onClick={clearFilters} variant="outline" size="icon" className="shrink-0">
                                        <X className="h-4 w-4" />
                                    </Button>
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                {/* Tabela/Lista de Andamentos - Responsiva */}
                <div className="space-y-4">
                    {andamentos?.data?.length > 0 ? (
                        <>
                            {/* Desktop: Tabela normal */}
                            <div className="hidden md:block overflow-x-auto">
                                <table className="w-full border-collapse">
                                    <thead>
                                        <tr className="border-b bg-muted/50">
                                            <th className="text-left p-4 font-medium">PROTOCOLO</th>
                                            <th className="text-left p-4 font-medium">CLIENTE</th>
                                            <th className="text-left p-4 font-medium">SERVIÇO</th>
                                            <th className="text-center p-4 font-medium">SITUAÇÃO ANTERIOR</th>
                                            <th className="text-center p-4 font-medium">NOVA SITUAÇÃO</th>
                                            <th className="text-center p-4 font-medium pr-8">ÚLTIMA ATUALIZAÇÃO</th>
                                            <th className="text-center p-4 font-medium pl-8">AÇÕES</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {andamentos.data.map((andamento) => (
                                            <tr 
                                                key={andamento.id} 
                                                className={cn(
                                                    "border-b hover:bg-muted/30 transition-colors cursor-pointer",
                                                    !andamento.visto && "border-l-4 border-l-red-500 bg-muted/20 dark:bg-muted/10"
                                                )}
                                                onClick={() => handleVerDespacho(andamento)}
                                            >
                                                <td className="p-4">
                                                    <div className="flex items-center gap-2">
                                                        {!andamento.visto && (
                                                            <div className="w-2 h-2 bg-red-500 rounded-full flex-shrink-0"></div>
                                                        )}
                                                        <span className="font-mono text-sm">
                                                            {andamento.processo?.protocolo || '-'}
                                                        </span>
                                                    </div>
                                                </td>
                                                
                                                <td className="p-4">
                                                    <div>
                                                        <div className="font-medium text-sm">
                                                            {andamento.processo?.nome || '-'}
                                                        </div>
                                                        <div className="text-xs text-muted-foreground">
                                                            CPF: {andamento.processo?.cpf || '-'}
                                                        </div>
                                                    </div>
                                                </td>
                                                
                                                <td className="p-4">
                                                    <span className="text-sm">
                                                        {andamento.processo?.servico || '-'}
                                                    </span>
                                                </td>
                                                
                                                <td className="p-4 text-center">
                                                    <Badge className={cn('text-xs border', getSituacaoStyle(andamento.situacao_anterior || ''))}>
                                                        {normalizeSituacao(andamento.situacao_anterior || 'N/A')}
                                                    </Badge>
                                                </td>
                                                
                                                <td className="p-4 text-center">
                                                    <Badge className={cn('text-xs border', getSituacaoStyle(andamento.situacao_atual))}>
                                                        {normalizeSituacao(andamento.situacao_atual)}
                                                    </Badge>
                                                </td>
                                                
                                                <td className="p-4 text-center pr-8">
                                                    <div className="text-xs text-muted-foreground">
                                                        {formatDate(andamento.processo?.ultima_atualizacao)}
                                                    </div>
                                                </td>
                                                
                                                <td className="p-4 text-center pl-8">
                                                    <div className="flex gap-2 justify-center">
                                                        {!andamento.visto && (
                                                            <Button
    onClick={e => { e.stopPropagation(); markAsViewed(andamento.id); }}
    size="sm"
    variant="outline"
    className="h-8 w-8 p-0"
    title="Marcar como visto"
>
    <CheckCircle className="h-4 w-4 text-green-600" />
</Button>
                                                        )}
                                                        <Button
                                                            asChild
                                                            size="sm"
                                                            variant="outline"
                                                            className="h-8 w-8 p-0"
                                                            title="Ver detalhes do processo"
                                                        >
                                                            <a
                                                                href={`https://atendimento.inss.gov.br/tarefas/detalhar_tarefa/${andamento.processo?.protocolo}`}
                                                                target="_blank"
                                                                rel="noopener noreferrer"
                                                            >
                                                                <ExternalLink className="h-4 w-4 text-blue-600" />
                                                            </a>
                                                        </Button>
                                                        <Button
                                                            onClick={() => handleVerDespacho(andamento)}
                                                            size="sm"
                                                            variant="outline"
                                                            className="h-8 w-8 p-0"
                                                            title="Ver despacho"
                                                        >
                                                            <Eye className="h-4 w-4 text-purple-600" />
                                                        </Button>
                                                        <Button
                                                            onClick={() => handleAddToAdvbox(andamento)}
                                                            size="sm"
                                                            variant="outline"
                                                            className="h-8 w-8 p-0"
                                                            title="Adicionar no advbox"
                                                        >
                                                            <FileText className="h-4 w-4 text-indigo-600" />
                                                        </Button>
                                                    </div>
                                                </td>
                                            </tr>
                                        ))}
                                    </tbody>
                                </table>
                            </div>

                            {/* Mobile: Cards */}
                            <div className="md:hidden space-y-3">
                                {andamentos.data.map((andamento) => (
                                                        <Card 
                        key={andamento.id}
                        className={cn(
                            "relative bg-card text-card-foreground",
                            !andamento.visto && "border-l-4 border-l-red-500 bg-muted/20 dark:bg-muted/10"
                        )}
                    >
                                        <CardContent className="p-4">
                                            <div className="flex items-start justify-between mb-3">
                                                <div className="flex items-center gap-2">
                                                    {!andamento.visto && (
                                                        <div className="w-2 h-2 bg-red-500 rounded-full flex-shrink-0"></div>
                                                    )}
                                                    <span className="font-mono text-sm font-medium">
                                                        {andamento.processo?.protocolo || '-'}
                                                    </span>
                                                </div>
                                                <div className="flex gap-1">
                                                    {!andamento.visto && (
                                                        <Button
                                                            onClick={() => markAsViewed(andamento.id)}
                                                            size="sm"
                                                            variant="outline"
                                                            className="h-7 w-7 p-0"
                                                        >
                                                            <CheckCircle className="h-3 w-3 text-green-600" />
                                                        </Button>
                                                    )}
                                                    <Button
                                                        asChild
                                                        size="sm"
                                                        variant="outline"
                                                        className="h-7 w-7 p-0"
                                                    >
                                                        <a
                                                            href={`https://atendimento.inss.gov.br/tarefas/detalhar_tarefa/${andamento.processo?.protocolo}`}
                                                            target="_blank"
                                                            rel="noopener noreferrer"
                                                        >
                                                            <ExternalLink className="h-3 w-3 text-blue-600" />
                                                        </a>
                                                    </Button>
                                                </div>
                                            </div>
                                            
                                            <div className="space-y-2">
                                                <div>
                                                    <div className="font-medium text-sm">
                                                        {andamento.processo?.nome || '-'}
                                                    </div>
                                                    <div className="text-xs text-muted-foreground">
                                                        CPF: {andamento.processo?.cpf || '-'}
                                                    </div>
                                                </div>
                                                
                                                <div className="text-sm">
                                                    <span className="text-muted-foreground">Serviço: </span>
                                                    {andamento.processo?.servico || '-'}
                                                </div>
                                                
                                                <div className="flex flex-wrap gap-2">
                                                    <div className="flex items-center gap-2">
                                                        <span className="text-xs text-muted-foreground">De:</span>
                                                        <Badge className={cn('text-xs', getSituacaoStyle(andamento.situacao_anterior || ''))}>
                                                            {normalizeSituacao(andamento.situacao_anterior || 'N/A')}
                                                        </Badge>
                                                    </div>
                                                    <div className="flex items-center gap-2">
                                                        <span className="text-xs text-muted-foreground">Para:</span>
                                                        <Badge className={cn('text-xs', getSituacaoStyle(andamento.situacao_atual))}>
                                                            {normalizeSituacao(andamento.situacao_atual)}
                                                        </Badge>
                                                    </div>
                                                </div>
                                                
                                                <div className="text-xs text-muted-foreground">
                                                    {formatDate(andamento.processo?.ultima_atualizacao)}
                                                </div>
                                                
                                                <div className="flex gap-2 pt-2">
                                                    <Button
                                                        onClick={() => handleVerDespacho(andamento)}
                                                        size="sm"
                                                        variant="outline"
                                                        className="h-7 w-7 p-0"
                                                        title="Ver despacho"
                                                    >
                                                        <Eye className="h-3 w-3 text-purple-600" />
                                                    </Button>
                                                    <Button
                                                        onClick={() => handleAddToAdvbox(andamento)}
                                                        size="sm"
                                                        variant="outline"
                                                        className="h-7 w-7 p-0"
                                                        title="Adicionar no advbox"
                                                    >
                                                        <FileText className="h-3 w-3 text-indigo-600" />
                                                    </Button>
                                                </div>
                                            </div>
                                        </CardContent>
                                    </Card>
                                ))}
                            </div>

                            {/* Paginação */}
                            {andamentos.last_page > 1 && (
                                <div className="flex items-center justify-center gap-4 p-4">
                                    <div className="text-sm text-muted-foreground">
                                        Página {andamentos.current_page} de {andamentos.last_page}
                                    </div>
                                    <div className="flex gap-2">
                                        <Button
                                            onClick={() => router.get('/andamentos', {
                                                page: andamentos.current_page - 1,
                                                search: searchTerm,
                                                nova_situacao: selectedNovaSituacao === 'all' ? '' : selectedNovaSituacao,
                                                situacao_anterior: selectedSituacaoAnterior === 'all' ? '' : selectedSituacaoAnterior,
                                                visualizacao: selectedVisualizacao === 'all' ? '' : selectedVisualizacao,
                                                periodo: selectedPeriodo === 'all' ? '' : selectedPeriodo,
                                            }, { preserveState: true })}
                                            variant="outline"
                                            size="sm"
                                            disabled={andamentos.current_page === 1}
                                        >
                                            Anterior
                                        </Button>
                                        <Button
                                            onClick={() => router.get('/andamentos', {
                                                page: andamentos.current_page + 1,
                                                search: searchTerm,
                                                nova_situacao: selectedNovaSituacao === 'all' ? '' : selectedNovaSituacao,
                                                situacao_anterior: selectedSituacaoAnterior === 'all' ? '' : selectedSituacaoAnterior,
                                                visualizacao: selectedVisualizacao === 'all' ? '' : selectedVisualizacao,
                                                periodo: selectedPeriodo === 'all' ? '' : selectedPeriodo,
                                            }, { preserveState: true })}
                                            variant="outline"
                                            size="sm"
                                            disabled={andamentos.current_page === andamentos.last_page}
                                        >
                                            Próxima
                                        </Button>
                                    </div>
                                </div>
                            )}
                        </>
                    ) : (
                        <Card className="p-8 text-center bg-card text-card-foreground">
                            <CardContent>
                                <Users className="mx-auto h-12 w-12 text-muted-foreground mb-4" />
                                <h3 className="text-lg font-semibold mb-2">Nenhum andamento encontrado</h3>
                                <p className="mb-4 text-muted-foreground">
                                    {searchTerm || (selectedNovaSituacao !== 'all') || (selectedSituacaoAnterior !== 'all') || (selectedVisualizacao !== 'all') || (selectedPeriodo !== 'all')
                                        ? 'Nenhum andamento encontrado com os filtros aplicados' 
                                        : 'Os andamentos aparecerão aqui quando houver mudanças de situação nos processos.'}
                                </p>
                                {(searchTerm || (selectedNovaSituacao !== 'all') || (selectedSituacaoAnterior !== 'all') || (selectedVisualizacao !== 'all') || (selectedPeriodo !== 'all')) && (
                                    <Button onClick={clearFilters} variant="outline">
                                        Limpar filtros
                                    </Button>
                                )}
                            </CardContent>
                        </Card>
                    )}
                </div>
            </div>
            {selectedAndamento && (
                <AdvboxTaskModal
                    isOpen={isAdvboxModalOpen}
                    onClose={() => {
                        setIsAdvboxModalOpen(false);
                        setSelectedAndamento(null);
                    }}
                    andamento={selectedAndamento}
                />
            )}

            {/* Modal de Despacho */}
            <Dialog open={isDespachoModalOpen} onOpenChange={setIsDespachoModalOpen}>
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle className="flex items-center gap-2">
                            <FileText className="h-5 w-5 text-purple-600" />
                            Despacho do INSS
                        </DialogTitle>
                    </DialogHeader>
                    {loadingDespacho ? (
                        <div className="flex items-center justify-center py-8">
                            <Loader2 className="h-8 w-8 animate-spin text-purple-600" />
                        </div>
                    ) : despachoData ? (
                        <div className="space-y-6">
                            <div className="rounded-lg border bg-card p-4">
                                <div className="grid gap-4 sm:grid-cols-2">
                                    <div>
                                        <div className="text-sm font-medium text-muted-foreground">Protocolo</div>
                                        <div className="font-mono text-base">{despachoData.protocolo}</div>
                                    </div>
                                    <div>
                                        <div className="text-sm font-medium text-muted-foreground">Data do Email</div>
                                        <div className="text-base">
                                            {new Date(despachoData.data_email).toLocaleDateString('pt-BR', {
                                                day: '2-digit',
                                                month: '2-digit',
                                                year: 'numeric',
                                                hour: '2-digit',
                                                minute: '2-digit'
                                            })}
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div className="space-y-2">
                                <div className="flex items-center gap-2">
                                    <MessageSquare className="h-4 w-4 text-purple-600" />
                                    <span className="text-sm font-medium">Conteúdo do Despacho</span>
                                </div>
                                <div className="whitespace-pre-wrap rounded-lg border bg-muted/50 p-4 text-sm">
                                    {despachoData.conteudo}
                                </div>
                            </div>

                            <div className="flex justify-end gap-2">
                                {/* Botão Marcar como Visto */}
                                {selectedAndamento && !selectedAndamento.visto && (
    <Button
        variant="outline"
        onClick={() => {
            markAsViewed(selectedAndamento.id);
            setIsDespachoModalOpen(false);
        }}
        className="h-8 w-8 p-0"
        title="Marcar como Visto"
    >
        <CheckCircle className="h-4 w-4 text-green-600" />
    </Button>
)}

                                {/* Botão Ver no INSS */}
                                <Button
    variant="outline"
    onClick={() => handleVerNoInss(despachoData.protocolo)}
    className="h-8 w-8 p-0"
    title="Ver no INSS"
>
    <ExternalLink className="h-4 w-4 text-blue-600" />
</Button>

                                {/* Botão Importar para Advbox */}
                                {selectedAndamento && (
                                    <Button
                                        variant="outline"
                                        onClick={() => {
                                            setIsDespachoModalOpen(false);
                                            handleAddToAdvbox(selectedAndamento);
                                        }}
                                        className="h-8 w-8 p-0"
                                        title="Importar para Advbox"
                                    >
                                        <FileText className="h-4 w-4 text-indigo-600" />
                                    </Button>
                                )}
                            </div>
                        </div>
                    ) : (
                        <div className="flex flex-col items-center justify-center gap-4 py-8">
                            <AlertCircle className="h-12 w-12 text-muted-foreground" />
                            <div className="text-center">
                                <p className="text-lg font-medium">Nenhum despacho encontrado</p>
                                <p className="text-sm text-muted-foreground">
                                    Não foi encontrado nenhum despacho para este protocolo.
                                </p>
                            </div>
                        </div>
                    )}
                </DialogContent>
            </Dialog>
        </AppLayout>
    );
}