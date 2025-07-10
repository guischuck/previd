import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import { Input } from '@/components/ui/input';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import AppLayout from '@/layouts/app-layout';
import { Head, Link, router } from '@inertiajs/react';
import { Copy, Edit, Eye, File, FileText, Filter, MoreHorizontal, Plus, Search, Settings, Trash2 } from 'lucide-react';
import { useState } from 'react';

interface PetitionTemplate {
    id: number;
    name: string;
    category: string;
    benefit_type: string | null;
    description: string | null;
    is_active: boolean;
    is_default: boolean;
    created_at: string;
    updated_at: string;
    creator: {
        name: string;
    };
    petitions_count: number;
}

interface Props {
    templates: {
        data: PetitionTemplate[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
    };
    filters: {
        search?: string;
        category?: string;
        benefit_type?: string;
        status?: string;
    };
    categories: Array<{ value: string; label: string }>;
    benefitTypes: Array<{ value: string; label: string }>;
}

export default function Templates({ templates, filters, categories, benefitTypes }: Props) {
    const [search, setSearch] = useState(filters.search || '');
    const [category, setCategory] = useState(filters.category || '');
    const [benefitType, setBenefitType] = useState(filters.benefit_type || '');
    const [status, setStatus] = useState(filters.status || '');

    const formatDate = (dateString: string) => {
        return new Date(dateString).toLocaleDateString('pt-BR');
    };

    const getCategoryBadge = (category: string) => {
        const colors: Record<string, string> = {
            aposentadoria: 'bg-blue-100 text-blue-800 border-blue-200 dark:bg-blue-900/20 dark:text-blue-300 dark:border-blue-700',
            auxilio: 'bg-green-100 text-green-800 border-green-200 dark:bg-green-900/20 dark:text-green-300 dark:border-green-700',
            pensao: 'bg-purple-100 text-purple-800 border-purple-200 dark:bg-purple-900/20 dark:text-purple-300 dark:border-purple-700',
            recurso: 'bg-orange-100 text-orange-800 border-orange-200 dark:bg-orange-900/20 dark:text-orange-300 dark:border-orange-700',
            outros: 'bg-gray-100 text-gray-800 border-gray-200 dark:bg-gray-900/20 dark:text-gray-300 dark:border-gray-700',
        };

        return <Badge className={`text-xs border ${colors[category] || colors.outros}`}>{category.charAt(0).toUpperCase() + category.slice(1)}</Badge>;
    };

    const getStatusBadge = (isActive: boolean, isDefault: boolean) => {
        if (isDefault) {
            return (
                <Badge variant="default" className="text-xs">
                    Padrão
                </Badge>
            );
        }
        return isActive ? (
            <Badge variant="secondary" className="text-xs">
                Ativo
            </Badge>
        ) : (
            <Badge variant="outline" className="text-xs">
                Inativo
            </Badge>
        );
    };

    const handleSearch = () => {
        router.get(
            route('petition-templates.index'),
            {
                search,
                category,
                benefit_type: benefitType,
                status,
            },
            {
                preserveState: true,
                replace: true,
            },
        );
    };

    const clearFilters = () => {
        setSearch('');
        setCategory('');
        setBenefitType('');
        setStatus('');
        router.get(route('petition-templates.index'));
    };

    const handleToggleActive = (template: PetitionTemplate) => {
        router.patch(
            route('petition-templates.toggle-active', template.id),
            {},
            {
                preserveScroll: true,
            },
        );
    };

    const handleDuplicate = (template: PetitionTemplate) => {
        router.post(
            route('petition-templates.duplicate', template.id),
            {},
            {
                preserveScroll: true,
            },
        );
    };

    const handleDelete = (template: PetitionTemplate) => {
        if (confirm('Tem certeza que deseja excluir este template?')) {
            router.delete(route('petition-templates.destroy', template.id));
        }
    };

    return (
        <AppLayout>
            <Head title="Templates de Petições" />

            <div className="flex h-full flex-1 flex-col gap-6 p-4 md:p-6">
                {/* Header */}
                <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h1 className="text-2xl md:text-3xl font-bold">
                            <Settings className="mr-2 inline-block" /> Templates de Petições
                        </h1>
                        <p className="text-muted-foreground text-sm md:text-base">Gerencie os templates para criação rápida de petições</p>
                    </div>
                    <div className="flex flex-col gap-2 sm:flex-row">
                        <Link href={route('petitions.index')}>
                            <Button variant="outline" className="w-full sm:w-auto">
                                <FileText className="mr-2 h-4 w-4" />
                                Ver Petições
                            </Button>
                        </Link>
                        <Link href={route('petition-templates.create')}>
                            <Button className="w-full sm:w-auto">
                                <Plus className="mr-2 h-4 w-4" />
                                Novo Template
                            </Button>
                        </Link>
                    </div>
                </div>

                {/* Stats Cards */}
                <div className="grid gap-4 grid-cols-2 md:grid-cols-4">
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-xs md:text-sm font-medium">Total de Templates</CardTitle>
                            <File className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-lg md:text-2xl font-bold">{templates.total}</div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-xs md:text-sm font-medium">Templates Ativos</CardTitle>
                            <Settings className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-lg md:text-2xl font-bold">
                                {templates.data.filter((t) => t.is_active).length}
                            </div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-xs md:text-sm font-medium">Templates Padrão</CardTitle>
                            <File className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-lg md:text-2xl font-bold">
                                {templates.data.filter((t) => t.is_default).length}
                            </div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-xs md:text-sm font-medium">Total de Usos</CardTitle>
                            <FileText className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-lg md:text-2xl font-bold">
                                {templates.data.reduce((sum, t) => sum + t.petitions_count, 0)}
                            </div>
                        </CardContent>
                    </Card>
                </div>

                {/* Filters */}
                <Card>
                    <CardHeader>
                        <CardTitle className="flex items-center gap-2 text-sm md:text-base">
                            <Filter className="h-4 w-4" />
                            Filtros
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div className="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-5">
                            <div>
                                <Input
                                    placeholder="Buscar templates..."
                                    value={search}
                                    onChange={(e) => setSearch(e.target.value)}
                                    className="w-full"
                                />
                            </div>
                            <div>
                                <select
                                    value={category}
                                    onChange={(e) => setCategory(e.target.value)}
                                    className="w-full rounded-md border border-gray-300 px-3 py-2 dark:border-gray-600 dark:bg-gray-700"
                                >
                                    <option value="">Todas as categorias</option>
                                    {categories.map((cat) => (
                                        <option key={cat.value} value={cat.value}>
                                            {cat.label}
                                        </option>
                                    ))}
                                </select>
                            </div>
                            <div>
                                <select
                                    value={benefitType}
                                    onChange={(e) => setBenefitType(e.target.value)}
                                    className="w-full rounded-md border border-gray-300 px-3 py-2 dark:border-gray-600 dark:bg-gray-700"
                                >
                                    <option value="">Todos os benefícios</option>
                                    {benefitTypes.map((type) => (
                                        <option key={type.value} value={type.value}>
                                            {type.label}
                                        </option>
                                    ))}
                                </select>
                            </div>
                            <div>
                                <select
                                    value={status}
                                    onChange={(e) => setStatus(e.target.value)}
                                    className="w-full rounded-md border border-gray-300 px-3 py-2 dark:border-gray-600 dark:bg-gray-700"
                                >
                                    <option value="">Todos os status</option>
                                    <option value="active">Ativos</option>
                                    <option value="inactive">Inativos</option>
                                    <option value="default">Padrão</option>
                                </select>
                            </div>
                            <div className="flex gap-2">
                                <Button onClick={handleSearch} className="flex-1">
                                    <Search className="mr-2 h-4 w-4" />
                                    Buscar
                                </Button>
                                <Button variant="outline" onClick={clearFilters}>
                                    Limpar
                                </Button>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                {/* Templates List */}
                <Card>
                    <CardHeader>
                        <CardTitle className="flex items-center gap-2 text-sm md:text-base">
                            <File className="h-4 w-4" />
                            Lista de Templates
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        {templates.data.length === 0 ? (
                            <div className="py-12 text-center">
                                <File className="mx-auto mb-4 h-16 w-16 text-muted-foreground" />
                                <h3 className="mb-2 text-lg font-medium">Nenhum template encontrado</h3>
                                <p className="mx-auto mb-6 max-w-md text-muted-foreground">
                                    Comece criando seu primeiro template para agilizar a criação de petições
                                </p>
                                <Link href={route('petition-templates.create')}>
                                    <Button>
                                        <Plus className="mr-2 h-4 w-4" />
                                        Criar Template
                                    </Button>
                                </Link>
                            </div>
                        ) : (
                            <div className="space-y-4">
                                {/* Desktop Table */}
                                <div className="hidden lg:block">
                                    <div className="overflow-x-auto">
                                        <Table>
                                            <TableHeader>
                                                <TableRow>
                                                    <TableHead>Nome</TableHead>
                                                    <TableHead>Categoria</TableHead>
                                                    <TableHead>Benefício</TableHead>
                                                    <TableHead>Status</TableHead>
                                                    <TableHead>Uso</TableHead>
                                                    <TableHead>Criado por</TableHead>
                                                    <TableHead>Data</TableHead>
                                                    <TableHead className="text-right">Ações</TableHead>
                                                </TableRow>
                                            </TableHeader>
                                            <TableBody>
                                                {templates.data.map((template) => (
                                                    <TableRow key={template.id}>
                                                        <TableCell className="font-medium">
                                                            <Link
                                                                href={route('petition-templates.show', template.id)}
                                                                className="hover:underline"
                                                            >
                                                                {template.name}
                                                            </Link>
                                                        </TableCell>
                                                        <TableCell>{getCategoryBadge(template.category)}</TableCell>
                                                        <TableCell>
                                                            {template.benefit_type ? (
                                                                <Badge variant="outline" className="text-xs">
                                                                    {template.benefit_type}
                                                                </Badge>
                                                            ) : (
                                                                <span className="text-muted-foreground">-</span>
                                                            )}
                                                        </TableCell>
                                                        <TableCell>{getStatusBadge(template.is_active, template.is_default)}</TableCell>
                                                        <TableCell>
                                                            <span className="text-sm text-muted-foreground">
                                                                {template.petitions_count} petições
                                                            </span>
                                                        </TableCell>
                                                        <TableCell>{template.creator.name}</TableCell>
                                                        <TableCell>{formatDate(template.created_at)}</TableCell>
                                                        <TableCell className="text-right">
                                                            <DropdownMenu>
                                                                <DropdownMenuTrigger asChild>
                                                                    <Button variant="outline" size="sm">
                                                                        <MoreHorizontal className="h-4 w-4" />
                                                                    </Button>
                                                                </DropdownMenuTrigger>
                                                                <DropdownMenuContent align="end">
                                                                    <DropdownMenuItem asChild>
                                                                        <Link href={route('petition-templates.show', template.id)}>
                                                                            <Eye className="mr-2 h-4 w-4" />
                                                                            Visualizar
                                                                        </Link>
                                                                    </DropdownMenuItem>
                                                                    <DropdownMenuItem asChild>
                                                                        <Link href={route('petition-templates.edit', template.id)}>
                                                                            <Edit className="mr-2 h-4 w-4" />
                                                                            Editar
                                                                        </Link>
                                                                    </DropdownMenuItem>
                                                                    <DropdownMenuItem onClick={() => handleDuplicate(template)}>
                                                                        <Copy className="mr-2 h-4 w-4" />
                                                                        Duplicar
                                                                    </DropdownMenuItem>
                                                                    <DropdownMenuItem onClick={() => handleToggleActive(template)}>
                                                                        <Settings className="mr-2 h-4 w-4" />
                                                                        {template.is_active ? 'Desativar' : 'Ativar'}
                                                                    </DropdownMenuItem>
                                                                    <DropdownMenuItem
                                                                        onClick={() => handleDelete(template)}
                                                                        className="text-red-600 dark:text-red-400"
                                                                    >
                                                                        <Trash2 className="mr-2 h-4 w-4" />
                                                                        Excluir
                                                                    </DropdownMenuItem>
                                                                </DropdownMenuContent>
                                                            </DropdownMenu>
                                                        </TableCell>
                                                    </TableRow>
                                                ))}
                                            </TableBody>
                                        </Table>
                                    </div>
                                </div>

                                {/* Mobile Cards */}
                                <div className="space-y-4 lg:hidden">
                                    {templates.data.map((template) => (
                                        <Card key={template.id} className="transition-shadow hover:shadow-md">
                                            <CardContent className="p-4">
                                                <div className="mb-3 flex items-start justify-between">
                                                    <div className="min-w-0 flex-1">
                                                        <Link
                                                            href={route('petition-templates.show', template.id)}
                                                            className="block truncate font-medium hover:underline"
                                                        >
                                                            {template.name}
                                                        </Link>
                                                        <p className="mt-1 text-sm text-muted-foreground">
                                                            {template.description || 'Sem descrição'}
                                                        </p>
                                                    </div>
                                                    {getStatusBadge(template.is_active, template.is_default)}
                                                </div>

                                                <div className="mb-3 flex items-center gap-2">
                                                    {getCategoryBadge(template.category)}
                                                    {template.benefit_type && (
                                                        <Badge variant="outline" className="text-xs">
                                                            {template.benefit_type}
                                                        </Badge>
                                                    )}
                                                </div>

                                                <div className="mb-3 flex items-center justify-between text-xs text-muted-foreground">
                                                    <span>{template.creator.name}</span>
                                                    <span>{formatDate(template.created_at)}</span>
                                                </div>

                                                <div className="flex items-center justify-between">
                                                    <span className="text-sm text-muted-foreground">
                                                        {template.petitions_count} petições
                                                    </span>
                                                    <div className="flex gap-1">
                                                        <Link href={route('petition-templates.show', template.id)}>
                                                            <Button variant="outline" size="sm">
                                                                <Eye className="h-4 w-4" />
                                                            </Button>
                                                        </Link>
                                                        <Link href={route('petition-templates.edit', template.id)}>
                                                            <Button variant="outline" size="sm">
                                                                <Edit className="h-4 w-4" />
                                                            </Button>
                                                        </Link>
                                                        <DropdownMenu>
                                                            <DropdownMenuTrigger asChild>
                                                                <Button variant="outline" size="sm">
                                                                    <MoreHorizontal className="h-4 w-4" />
                                                                </Button>
                                                            </DropdownMenuTrigger>
                                                            <DropdownMenuContent align="end">
                                                                <DropdownMenuItem onClick={() => handleDuplicate(template)}>
                                                                    <Copy className="mr-2 h-4 w-4" />
                                                                    Duplicar
                                                                </DropdownMenuItem>
                                                                <DropdownMenuItem onClick={() => handleToggleActive(template)}>
                                                                    <Settings className="mr-2 h-4 w-4" />
                                                                    {template.is_active ? 'Desativar' : 'Ativar'}
                                                                </DropdownMenuItem>
                                                                <DropdownMenuItem
                                                                    onClick={() => handleDelete(template)}
                                                                    className="text-red-600 dark:text-red-400"
                                                                >
                                                                    <Trash2 className="mr-2 h-4 w-4" />
                                                                    Excluir
                                                                </DropdownMenuItem>
                                                            </DropdownMenuContent>
                                                        </DropdownMenu>
                                                    </div>
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
