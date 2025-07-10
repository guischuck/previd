import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, router, usePage } from '@inertiajs/react';
import { useState } from 'react';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { 
    CheckCircle, 
    Clock, 
    AlertCircle,
    Calendar,
    Star,
    Zap,
    Target,
    TrendingUp
} from 'lucide-react';
import { cn } from '@/lib/utils';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
    {
        title: 'Features',
        href: '/features',
    },
];

interface Feature {
    id: number;
    title: string;
    description: string;
    deadline: string | null;
    status: 'in_progress' | 'planned' | 'completed';
    priority: 'high' | 'medium' | 'low';
}

export default function FeaturesIndex() {
    const { features, auth } = usePage().props as any;
    const [localFeatures, setLocalFeatures] = useState<Feature[]>(features);
    const isSuperAdmin = auth?.user?.roles?.some((role: any) => role.name === 'superadmin');

    const getStatusIcon = (status: string) => {
        switch (status) {
            case 'completed':
                return <CheckCircle className="h-5 w-5 text-green-500" />;
            case 'in_progress':
                return <Clock className="h-5 w-5 text-blue-500" />;
            case 'planned':
                return <AlertCircle className="h-5 w-5 text-orange-500" />;
            default:
                return <Clock className="h-5 w-5 text-gray-500" />;
        }
    };

    const getStatusText = (status: string) => {
        switch (status) {
            case 'completed':
                return 'Concluído';
            case 'in_progress':
                return 'Em Desenvolvimento';
            case 'planned':
                return 'Planejado';
            default:
                return 'Desconhecido';
        }
    };

    const getStatusColor = (status: string) => {
        switch (status) {
            case 'completed':
                return 'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-200';
            case 'in_progress':
                return 'bg-blue-100 text-blue-800 dark:bg-blue-900/20 dark:text-blue-200';
            case 'planned':
                return 'bg-orange-100 text-orange-800 dark:bg-orange-900/20 dark:text-orange-200';
            default:
                return 'bg-gray-100 text-gray-800 dark:bg-gray-900/20 dark:text-gray-200';
        }
    };

    const getPriorityIcon = (priority: string) => {
        switch (priority) {
            case 'high':
                return <Zap className="h-4 w-4 text-red-500" />;
            case 'medium':
                return <Target className="h-4 w-4 text-yellow-500" />;
            case 'low':
                return <TrendingUp className="h-4 w-4 text-green-500" />;
            default:
                return <Star className="h-4 w-4 text-gray-500" />;
        }
    };

    const getPriorityText = (priority: string) => {
        switch (priority) {
            case 'high':
                return 'Alta';
            case 'medium':
                return 'Média';
            case 'low':
                return 'Baixa';
            default:
                return 'Desconhecida';
        }
    };

    const getPriorityColor = (priority: string) => {
        switch (priority) {
            case 'high':
                return 'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-200';
            case 'medium':
                return 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-200';
            case 'low':
                return 'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-200';
            default:
                return 'bg-gray-100 text-gray-800 dark:bg-gray-900/20 dark:text-gray-200';
        }
    };

    const formatDeadline = (deadline: string | null) => {
        if (!deadline) return 'Em breve';
        
        const date = new Date(deadline);
        return date.toLocaleDateString('pt-BR', {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric'
        });
    };

    const toggleFeatureStatus = (featureId: number) => {
        if (!isSuperAdmin) return;

        router.patch(`/features/${featureId}/toggle-status`, {}, {
            onSuccess: () => {
                setLocalFeatures(prev => prev.map(feature => {
                    if (feature.id === featureId) {
                        const newStatus = feature.status === 'completed' ? 'in_progress' : 'completed';
                        return { ...feature, status: newStatus };
                    }
                    return feature;
                }));
            },
        });
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Features" />
            <div className="flex h-full flex-1 flex-col gap-6 p-4 md:p-6">
                {/* Header */}
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-2xl md:text-3xl font-bold">Features</h1>
                        <p className="text-muted-foreground text-sm md:text-base">
                            Próximas implementações e funcionalidades em desenvolvimento
                        </p>
                    </div>
                </div>

                {/* Estatísticas */}
                <div className="grid gap-4 md:grid-cols-4">
                    <Card>
                        <CardContent className="p-4">
                            <div className="flex items-center gap-2">
                                <Clock className="h-5 w-5 text-blue-500" />
                                <div>
                                    <p className="text-sm text-muted-foreground">Em Desenvolvimento</p>
                                    <p className="text-2xl font-bold">
                                        {localFeatures.filter(f => f.status === 'in_progress').length}
                                    </p>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardContent className="p-4">
                            <div className="flex items-center gap-2">
                                <AlertCircle className="h-5 w-5 text-orange-500" />
                                <div>
                                    <p className="text-sm text-muted-foreground">Planejado</p>
                                    <p className="text-2xl font-bold">
                                        {localFeatures.filter(f => f.status === 'planned').length}
                                    </p>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardContent className="p-4">
                            <div className="flex items-center gap-2">
                                <CheckCircle className="h-5 w-5 text-green-500" />
                                <div>
                                    <p className="text-sm text-muted-foreground">Concluído</p>
                                    <p className="text-2xl font-bold">
                                        {localFeatures.filter(f => f.status === 'completed').length}
                                    </p>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardContent className="p-4">
                            <div className="flex items-center gap-2">
                                <Zap className="h-5 w-5 text-red-500" />
                                <div>
                                    <p className="text-sm text-muted-foreground">Alta Prioridade</p>
                                    <p className="text-2xl font-bold">
                                        {localFeatures.filter(f => f.priority === 'high').length}
                                    </p>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                {/* Lista de Features */}
                <div className="space-y-4">
                    {localFeatures.map((feature) => (
                        <Card key={feature.id} className={cn(
                            "transition-all duration-200",
                            feature.status === 'completed' && "opacity-75"
                        )}>
                            <CardContent className="p-6">
                                <div className="flex items-start justify-between">
                                    <div className="flex-1">
                                        <div className="flex items-center gap-3 mb-2">
                                            {getStatusIcon(feature.status)}
                                            <h3 className="text-lg font-semibold">{feature.title}</h3>
                                        </div>
                                        
                                        <p className="text-muted-foreground mb-4">
                                            {feature.description}
                                        </p>

                                        <div className="flex items-center gap-4 flex-wrap">
                                            <Badge className={getStatusColor(feature.status)}>
                                                {getStatusText(feature.status)}
                                            </Badge>
                                            
                                            <Badge className={getPriorityColor(feature.priority)}>
                                                <div className="flex items-center gap-1">
                                                    {getPriorityIcon(feature.priority)}
                                                    {getPriorityText(feature.priority)}
                                                </div>
                                            </Badge>

                                            <div className="flex items-center gap-2 text-sm text-muted-foreground">
                                                <Calendar className="h-4 w-4" />
                                                <span>Prazo: {formatDeadline(feature.deadline)}</span>
                                            </div>
                                        </div>
                                    </div>

                                    {isSuperAdmin && (
                                        <div className="ml-4">
                                            <Button
                                                variant={feature.status === 'completed' ? 'outline' : 'default'}
                                                size="sm"
                                                onClick={() => toggleFeatureStatus(feature.id)}
                                                className={cn(
                                                    feature.status === 'completed' 
                                                        ? 'border-green-500 text-green-600 hover:bg-green-50' 
                                                        : 'bg-green-600 hover:bg-green-700'
                                                )}
                                            >
                                                {feature.status === 'completed' ? (
                                                    <>
                                                        <CheckCircle className="h-4 w-4 mr-2" />
                                                        Concluído
                                                    </>
                                                ) : (
                                                    <>
                                                        <CheckCircle className="h-4 w-4 mr-2" />
                                                        Marcar Concluído
                                                    </>
                                                )}
                                            </Button>
                                        </div>
                                    )}
                                </div>
                            </CardContent>
                        </Card>
                    ))}
                </div>

                {/* Informação para usuários não-admin */}
                {!isSuperAdmin && (
                    <Card className="border-blue-200 bg-blue-50 dark:border-blue-800 dark:bg-blue-900/20">
                        <CardContent className="p-4">
                            <div className="flex items-center gap-2">
                                <AlertCircle className="h-5 w-5 text-blue-600" />
                                <p className="text-blue-800 dark:text-blue-200">
                                    Apenas super administradores podem marcar features como concluídas. 
                                    Esta página serve para acompanhar as próximas implementações do sistema.
                                </p>
                            </div>
                        </CardContent>
                    </Card>
                )}
            </div>
        </AppLayout>
    );
} 