import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import AppLayout from '@/layouts/app-layout';
import { Head, Link } from '@inertiajs/react';
import { Calendar, ClipboardList, Cpu, Download, Eye, File, FileText, Plus, Settings, User } from 'lucide-react';

interface Petition {
    id: number;
    title: string;
    type: 'pre_cadastrada' | 'ia';
    created_at: string;
    legal_case: {
        id: number;
        client_name: string;
        case_number: string;
    };
    user: {
        name: string;
    };
}

interface Props {
    petitions: {
        data: Petition[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
    };
    templatesCount?: number;
}

export default function Index({ petitions, templatesCount = 0 }: Props) {
    const formatDate = (dateString: string) => {
        return new Date(dateString).toLocaleDateString('pt-BR');
    };

    const getTypeBadge = (type: string) => {
        return type === 'pre_cadastrada' ? (
            <Badge variant="secondary" className="flex items-center gap-1 text-xs">
                <FileText className="h-3 w-3" />
                Template
            </Badge>
        ) : (
            <Badge variant="default" className="flex items-center gap-1 text-xs">
                <Cpu className="h-3 w-3" />
                IA
            </Badge>
        );
    };

    return (
        <AppLayout>
            <Head title="Petições - PrevidIA" />

            <div className="flex h-full flex-1 flex-col gap-6 p-4 md:p-6">
                {/* Header */}
                <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h1 className="text-2xl md:text-3xl font-bold">
                            <FileText className="mr-2 inline-block" /> Petições
                        </h1>
                        <p className="text-muted-foreground text-sm md:text-base">Gerencie todas as petições e templates do sistema</p>
                    </div>
                    <div className="flex flex-col gap-2 sm:flex-row">
                        <Link href={route('petition-templates.index')}>
                            <Button variant="outline" className="w-full sm:w-auto">
                                <Settings className="mr-2 h-4 w-4" />
                                Gerenciar Templates
                            </Button>
                        </Link>
                        <Link href={route('petitions.create')}>
                            <Button className="w-full sm:w-auto">
                                <Plus className="mr-2 h-4 w-4" />
                                Nova Petição
                            </Button>
                        </Link>
                    </div>
                </div>

                {/* Stats Cards */}
                <div className="grid gap-4 grid-cols-2 md:grid-cols-4">
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-xs md:text-sm font-medium">Total de Petições</CardTitle>
                            <FileText className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-lg md:text-2xl font-bold">{petitions.total}</div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-xs md:text-sm font-medium">Templates Disponíveis</CardTitle>
                            <File className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-lg md:text-2xl font-bold">{templatesCount}</div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-xs md:text-sm font-medium">Com Template</CardTitle>
                            <ClipboardList className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-lg md:text-2xl font-bold">
                                {petitions.data.filter((p) => p.type === 'pre_cadastrada').length}
                            </div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-xs md:text-sm font-medium">Geradas por IA</CardTitle>
                            <Cpu className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-lg md:text-2xl font-bold">
                                {petitions.data.filter((p) => p.type === 'ia').length}
                            </div>
                        </CardContent>
                    </Card>
                </div>

                {/* Quick Actions */}
                <Card>
                    <CardContent className="p-6">
                        <div className="flex flex-col items-center justify-between gap-4 sm:flex-row">
                            <div>
                                <h3 className="mb-1 text-lg font-semibold">Ações Rápidas</h3>
                                <p className="text-sm text-muted-foreground">Acesse rapidamente as funcionalidades mais utilizadas</p>
                            </div>
                            <div className="flex w-full flex-col gap-2 sm:w-auto sm:flex-row">
                                <Link href={route('petition-templates.create')}>
                                    <Button variant="outline" className="w-full sm:w-auto">
                                        <Plus className="mr-2 h-4 w-4" />
                                        Novo Template
                                    </Button>
                                </Link>
                                <Link href={route('petitions.create')}>
                                    <Button className="w-full sm:w-auto">
                                        <FileText className="mr-2 h-4 w-4" />
                                        Criar Petição
                                    </Button>
                                </Link>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                {/* Petitions List */}
                <Card>
                    <CardHeader>
                        <CardTitle className="flex items-center gap-2 text-sm md:text-base">
                            <FileText className="h-4 w-4" />
                            Lista de Petições
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        {petitions.data.length === 0 ? (
                            <div className="py-12 text-center">
                                <FileText className="mx-auto mb-4 h-16 w-16 text-muted-foreground" />
                                <h3 className="mb-2 text-lg font-medium">Nenhuma petição encontrada</h3>
                                <p className="mx-auto mb-6 max-w-md text-muted-foreground">
                                    Comece criando sua primeira petição usando um template ou gerando com IA
                                </p>
                                <div className="flex flex-col justify-center gap-2 sm:flex-row">
                                    <Link href={route('petition-templates.index')}>
                                        <Button variant="outline">
                                            <Settings className="mr-2 h-4 w-4" />
                                            Ver Templates
                                        </Button>
                                    </Link>
                                    <Link href={route('petitions.create')}>
                                        <Button>
                                            <Plus className="mr-2 h-4 w-4" />
                                            Criar Petição
                                        </Button>
                                    </Link>
                                </div>
                            </div>
                        ) : (
                            <div className="space-y-4">
                                {/* Desktop Table */}
                                <div className="hidden lg:block">
                                    <div className="overflow-x-auto">
                                        <Table>
                                            <TableHeader>
                                                <TableRow>
                                                    <TableHead>Título</TableHead>
                                                    <TableHead>Cliente</TableHead>
                                                    <TableHead>Tipo</TableHead>
                                                    <TableHead>Criado por</TableHead>
                                                    <TableHead>Data</TableHead>
                                                    <TableHead className="text-right">Ações</TableHead>
                                                </TableRow>
                                            </TableHeader>
                                            <TableBody>
                                                {petitions.data.map((petition) => (
                                                    <TableRow key={petition.id}>
                                                        <TableCell className="font-medium">
                                                            <Link
                                                                href={route('petitions.show', petition.id)}
                                                                className="hover:underline"
                                                            >
                                                                {petition.title}
                                                            </Link>
                                                        </TableCell>
                                                        <TableCell>{petition.legal_case.client_name}</TableCell>
                                                        <TableCell>{getTypeBadge(petition.type)}</TableCell>
                                                        <TableCell>{petition.user.name}</TableCell>
                                                        <TableCell>{formatDate(petition.created_at)}</TableCell>
                                                        <TableCell className="text-right">
                                                            <div className="flex items-center justify-end gap-2">
                                                                <Link href={route('petitions.show', petition.id)}>
                                                                    <Button variant="outline" size="sm">
                                                                        <Eye className="h-4 w-4" />
                                                                    </Button>
                                                                </Link>
                                                                <Link href={route('petitions.download', petition.id)}>
                                                                    <Button variant="outline" size="sm">
                                                                        <Download className="h-4 w-4" />
                                                                    </Button>
                                                                </Link>
                                                            </div>
                                                        </TableCell>
                                                    </TableRow>
                                                ))}
                                            </TableBody>
                                        </Table>
                                    </div>
                                </div>

                                {/* Mobile Cards */}
                                <div className="space-y-4 lg:hidden">
                                    {petitions.data.map((petition) => (
                                        <Card key={petition.id} className="transition-shadow hover:shadow-md">
                                            <CardContent className="p-4">
                                                <div className="mb-3 flex items-start justify-between">
                                                    <div className="min-w-0 flex-1">
                                                        <Link
                                                            href={route('petitions.show', petition.id)}
                                                            className="block truncate font-medium hover:underline"
                                                        >
                                                            {petition.title}
                                                        </Link>
                                                        <p className="mt-1 text-sm text-muted-foreground">
                                                            {petition.legal_case.client_name}
                                                        </p>
                                                    </div>
                                                    {getTypeBadge(petition.type)}
                                                </div>

                                                <div className="mb-3 flex items-center justify-between text-xs text-muted-foreground">
                                                    <div className="flex items-center gap-1">
                                                        <User className="h-3 w-3" />
                                                        {petition.user.name}
                                                    </div>
                                                    <div className="flex items-center gap-1">
                                                        <Calendar className="h-3 w-3" />
                                                        {formatDate(petition.created_at)}
                                                    </div>
                                                </div>

                                                <div className="flex gap-2">
                                                    <Link href={route('petitions.show', petition.id)} className="flex-1">
                                                        <Button variant="outline" size="sm" className="w-full">
                                                            <Eye className="mr-1 h-4 w-4" />
                                                            Ver
                                                        </Button>
                                                    </Link>
                                                    <Link href={route('petitions.download', petition.id)} className="flex-1">
                                                        <Button variant="outline" size="sm" className="w-full">
                                                            <Download className="mr-1 h-4 w-4" />
                                                            Download
                                                        </Button>
                                                    </Link>
                                                </div>
                                            </CardContent>
                                        </Card>
                                    ))}
                                </div>
                            </div>
                        )}
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}
