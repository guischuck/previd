import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/react';
import {
    AlertTriangle,
    BarChart3,
    BookOpen,
    Briefcase,
    Building2,
    CheckCircle,
    FileText,
    MessageSquare,
    Plus,
    Upload,
    Users,
    Workflow,
} from 'lucide-react';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
];

interface DashboardProps {
    isSuperAdmin?: boolean;
    stats?: {
        total_cases: number;
        pendente: number;
        em_coleta: number;
        protocolado: number;
        concluido: number;
        rejeitado: number;
    };
    inssStats?: {
        total_processos: number;
        processos_ativos: number;
        processos_exigencia: number;
        processos_concluidos: number;
    };
    companiesStats?: {
        total: number;
        active: number;
    };
    usersStats?: {
        total: number;
        active: number;
    };
    petitionTemplatesStats?: {
        total: number;
        active: number;
    };
    workflowTemplatesStats?: {
        total: number;
        active: number;
    };
    financial?: {
        monthly_revenue: number;
        recent_payments: number;
        active_subscriptions: number;
    };
    recentCompanies?: Array<any>;
    recent_activity?: {
        new_companies: number;
        new_users: number;
        recent_payments: number;
    };
}

export default function Dashboard(allProps: DashboardProps) {
    // Extrair props
    const { isSuperAdmin = false, stats, inssStats, ...restProps } = allProps;

    const defaultStats = {
        total_cases: 0,
        pendente: 0,
        em_coleta: 0,
        protocolado: 0,
        concluido: 0,
        rejeitado: 0,
    };

    const currentStats = stats || defaultStats;

    const formatCurrency = (value: number) => {
        return new Intl.NumberFormat('pt-BR', {
            style: 'currency',
            currency: 'BRL',
        }).format(value);
    };

    const getStatusBadgeVariant = (status: string) => {
        switch (status) {
            case 'concluido':
                return 'default';
            case 'pendente':
                return 'secondary';
            case 'em_coleta':
                return 'outline';
            default:
                return 'secondary';
        }
    };

    const getSituacaoStyle = (situacao: string): string => {
        const normalizedSituacao = situacao?.toUpperCase() || '';
        
        switch (normalizedSituacao) {
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

    const formatDate = (dateString: string): string => {
        return new Date(dateString).toLocaleDateString('pt-BR', {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
        });
    };



    // Dashboard do Super Admin
    if (isSuperAdmin) {
        const adminStatCards = [
            {
                title: 'Empresas',
                value: restProps.companiesStats?.total || 0,
                icon: Building2,
                color: 'bg-blue-500',
                href: '/companies',
                subtitle: `${restProps.companiesStats?.active || 0} ativas`,
            },
            {
                title: 'Usuários',
                value: restProps.usersStats?.total || 0,
                icon: Users,
                color: 'bg-green-500',
                href: '/users',
                subtitle: `${restProps.usersStats?.active || 0} ativos`,
            },
            {
                title: 'Templates',
                value: restProps.petitionTemplatesStats?.total || 0,
                icon: FileText,
                color: 'bg-purple-500',
                href: '/templates',
                subtitle: `${restProps.petitionTemplatesStats?.active || 0} ativos`,
            },
            {
                title: 'Workflows',
                value: restProps.workflowTemplatesStats?.total || 0,
                icon: Workflow,
                color: 'bg-orange-500',
                href: '/workflows',
                subtitle: `${restProps.workflowTemplatesStats?.active || 0} ativos`,
            },
        ];

        return (
            <AppLayout breadcrumbs={breadcrumbs}>
                <Head title="Dashboard Administrativo - PrevidIA" />
                <div className="flex h-full min-h-screen flex-1 flex-col gap-6 bg-background p-6">
                    {/* Header */}
                    <div className="flex items-center justify-between">
                        <div>
                            <h1 className="text-3xl font-bold">Dashboard Administrativo</h1>
                            <p className="text-muted-foreground">Visão geral do sistema PrevidIA</p>
                        </div>
                    </div>

                    {/* Stats Cards */}
                    <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                        {adminStatCards.map((stat) => (
                            <Card key={stat.title} className="border-border bg-card">
                                <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                                    <CardTitle className="text-sm font-medium text-card-foreground">{stat.title}</CardTitle>
                                    <stat.icon className={`h-4 w-4 ${stat.color.replace('bg-', 'text-')}`} />
                                </CardHeader>
                                <CardContent>
                                    <div className="text-2xl font-bold text-card-foreground">{stat.value}</div>
                                    <p className="text-xs text-muted-foreground">{stat.subtitle}</p>
                                </CardContent>
                            </Card>
                        ))}
                    </div>

                    {/* Financial Cards */}
                    {restProps.financial && (
                        <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                            <Card className="border-border bg-card">
                                <CardHeader>
                                    <CardTitle className="text-sm text-card-foreground">Receita Mensal</CardTitle>
                                </CardHeader>
                                <CardContent>
                                    <div className="text-2xl font-bold text-green-600">
                                        {formatCurrency(restProps.financial.monthly_revenue || 0)}
                                    </div>
                                </CardContent>
                            </Card>
                            <Card className="border-border bg-card">
                                <CardHeader>
                                    <CardTitle className="text-sm text-card-foreground">Pagamentos (30 dias)</CardTitle>
                                </CardHeader>
                                <CardContent>
                                    <div className="text-2xl font-bold text-card-foreground">{restProps.financial.recent_payments || 0}</div>
                                </CardContent>
                            </Card>
                            <Card className="border-border bg-card">
                                <CardHeader>
                                    <CardTitle className="text-sm text-card-foreground">Assinaturas Ativas</CardTitle>
                                </CardHeader>
                                <CardContent>
                                    <div className="text-2xl font-bold text-card-foreground">{restProps.financial.active_subscriptions || 0}</div>
                                </CardContent>
                            </Card>
                        </div>
                    )}

                    {/* Recent Companies */}
                    {restProps.recentCompanies && restProps.recentCompanies.length > 0 && (
                        <Card className="border-border bg-card">
                            <CardHeader>
                                <CardTitle className="text-card-foreground">Empresas Recentes</CardTitle>
                                <CardDescription className="text-muted-foreground">Últimas empresas cadastradas no sistema</CardDescription>
                            </CardHeader>
                            <CardContent>
                                <div className="space-y-3">
                                    {restProps.recentCompanies.slice(0, 5).map((company: any) => (
                                        <div
                                            key={company.id}
                                            className="flex items-center justify-between rounded-lg border border-border bg-card p-3"
                                        >
                                            <div className="flex-1">
                                                <p className="font-medium text-card-foreground">{company.name}</p>
                                                <p className="text-sm text-muted-foreground">
                                                    {company.users_count} usuários • {company.cases_count} casos
                                                </p>
                                            </div>
                                            <Badge variant={company.is_active ? 'default' : 'secondary'}>
                                                {company.is_active ? 'Ativa' : 'Inativa'}
                                            </Badge>
                                        </div>
                                    ))}
                                </div>
                            </CardContent>
                        </Card>
                    )}
                </div>
            </AppLayout>
        );
    }

    // Dashboard do Usuário Normal
    const statCards = [
        {
            title: 'Total de Casos',
            value: stats?.total_cases || currentStats.total_cases || 0,
            icon: Briefcase,
            color: 'bg-blue-500',
            href: '/cases',
        },
        {
            title: 'Pendentes',
            value: stats?.pendente || currentStats.pendente || 0,
            icon: AlertTriangle,
            color: 'bg-yellow-500',
            href: '/cases?status=pendente',
        },
        {
            title: 'Em Coleta',
            value: stats?.em_coleta || currentStats.em_coleta || 0,
            icon: BarChart3,
            color: 'bg-blue-500',
            href: '/cases?status=em_coleta',
        },
        {
            title: 'Concluídos',
            value: stats?.concluido || currentStats.concluido || 0,
            icon: CheckCircle,
            color: 'bg-green-500',
            href: '/cases?status=concluido',
        },
    ];

    const quickActions = [
        {
            title: 'Novo Caso',
            icon: Plus,
            href: '/cases/create',
            color: 'bg-blue-500 hover:bg-blue-600',
        },
        {
            title: 'Coletas',
            icon: Upload,
            href: '/coletas',
            color: 'bg-green-500 hover:bg-green-600',
        },
        {
            title: 'Gerar Petição',
            icon: BookOpen,
            href: '/petitions/create',
            color: 'bg-purple-500 hover:bg-purple-600',
        },
        {
            title: 'AI Chat',
            icon: MessageSquare,
            href: '/chat',
            color: 'bg-orange-500 hover:bg-orange-600',
        },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Dashboard - PrevidIA" />
            <div className="flex h-full min-h-screen flex-1 flex-col gap-6 bg-background p-6">
                {/* Header */}
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-3xl font-bold">Dashboard</h1>
                        <p className="text-muted-foreground">Visão geral dos seus casos e atividades</p>
                    </div>
                    <Link href="/cases/create">
                        <Button>
                            <Plus className="mr-2 h-4 w-4" />
                            Novo Caso
                        </Button>
                    </Link>
                </div>

                {/* Stats Cards */}
                <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                    {statCards.map((stat) => (
                        <Link key={stat.title} href={stat.href}>
                            <Card className="border-border bg-card cursor-pointer transition-all duration-200 hover:shadow-md">
                                <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                                    <CardTitle className="text-sm font-medium text-card-foreground">{stat.title}</CardTitle>
                                    <stat.icon className={`h-4 w-4 ${stat.color.replace('bg-', 'text-')}`} />
                                </CardHeader>
                                <CardContent>
                                    <div className="text-2xl font-bold text-card-foreground">{stat.value}</div>
                                    <p className="text-xs text-muted-foreground mt-1">Ver detalhes ➜</p>
                                </CardContent>
                            </Card>
                        </Link>
                    ))}
                </div>

                {/* Quick Actions */}
                <div>
                    <h2 className="mb-4 text-xl font-semibold text-foreground">Ações Rápidas</h2>
                    <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                        {quickActions.map((action) => (
                            <Link key={action.title} href={action.href}>
                                <Card className="border-border bg-card cursor-pointer transition-all duration-200 hover:shadow-md">
                                    <CardHeader>
                                        <div className="flex items-center space-x-2">
                                            <div className={`rounded-lg p-2 ${action.color}`}>
                                                <action.icon className="h-4 w-4 text-white" />
                                            </div>
                                            <CardTitle className="text-sm text-card-foreground">{action.title}</CardTitle>
                                        </div>
                                    </CardHeader>
                                </Card>
                            </Link>
                        ))}
                    </div>
                </div>

                {/* Processos INSS Stats */}
                <div>
                    <h2 className="mb-4 text-xl font-semibold text-foreground">Processos INSS</h2>
                    <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                        <Link href="/inss-processes">
                            <Card className="border-border bg-card cursor-pointer transition-all duration-200 hover:shadow-md">
                                <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                                    <CardTitle className="text-sm font-medium text-card-foreground">Total de Processos</CardTitle>
                                    <Briefcase className="h-4 w-4 text-blue-500" />
                                </CardHeader>
                                <CardContent>
                                    <div className="text-2xl font-bold text-card-foreground">{inssStats?.total_processos || 0}</div>
                                    <p className="text-xs text-muted-foreground mt-1">Ver todos ➜</p>
                                </CardContent>
                            </Card>
                        </Link>
                        
                        <Link href="/inss-processes?status=Em Análise">
                            <Card className="border-border bg-card cursor-pointer transition-all duration-200 hover:shadow-md">
                                <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                                    <CardTitle className="text-sm font-medium text-card-foreground">Em Análise</CardTitle>
                                    <FileText className="h-4 w-4 text-blue-500" />
                                </CardHeader>
                                <CardContent>
                                    <div className="text-2xl font-bold text-card-foreground">{inssStats?.processos_ativos || 0}</div>
                                    <p className="text-xs text-muted-foreground mt-1">Ver todos ➜</p>
                                </CardContent>
                            </Card>
                        </Link>
                        
                        <Link href="/inss-processes?status=Exigência">
                            <Card className="border-border bg-card cursor-pointer transition-all duration-200 hover:shadow-md">
                                <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                                    <CardTitle className="text-sm font-medium text-card-foreground">Em Exigência</CardTitle>
                                    <AlertTriangle className="h-4 w-4 text-orange-500" />
                                </CardHeader>
                                <CardContent>
                                    <div className="text-2xl font-bold text-card-foreground">{inssStats?.processos_exigencia || 0}</div>
                                    <p className="text-xs text-muted-foreground mt-1">Ver todos ➜</p>
                                </CardContent>
                            </Card>
                        </Link>
                        
                        <Link href="/inss-processes?status=Concluída">
                            <Card className="border-border bg-card cursor-pointer transition-all duration-200 hover:shadow-md">
                                <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                                    <CardTitle className="text-sm font-medium text-card-foreground">Concluídos</CardTitle>
                                    <CheckCircle className="h-4 w-4 text-green-500" />
                                </CardHeader>
                                <CardContent>
                                    <div className="text-2xl font-bold text-card-foreground">{inssStats?.processos_concluidos || 0}</div>
                                    <p className="text-xs text-muted-foreground mt-1">Ver todos ➜</p>
                                </CardContent>
                            </Card>
                        </Link>
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}
