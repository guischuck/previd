import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Tabs, TabsList, TabsTrigger } from '@/components/ui/tabs';
import AppLayout from '@/layouts/app-layout';
import { Head, Link, router } from '@inertiajs/react';
import { Briefcase, Building2, CheckCircle, Clock, Search, User, FileText } from 'lucide-react';
import { useState } from 'react';

interface ColetasProps {
    cards: {
        totalVinculos: number;
        clientesAtivos: number;
        clientesFinalizados: number;
        empresasPendentes: number;
        empresasConcluidas: number;
        coletasAtrasadas: number;
    };
    tab: string;
    search: string;
    resultados: any[];
}

export default function Coletas({ cards, tab, search, resultados }: ColetasProps) {
    const [activeTab, setActiveTab] = useState(tab || 'clientes');
    const [searchTerm, setSearchTerm] = useState(search || '');

    const handleTabChange = (value: string) => {
        setActiveTab(value);
        setSearchTerm('');
        router.get('/coletas', { tab: value }, { preserveState: true });
    };

    const handleSearch = () => {
        router.get('/coletas', { tab: activeTab, search: searchTerm }, { preserveState: true });
    };

    const handleKeyPress = (e: React.KeyboardEvent) => {
        if (e.key === 'Enter') handleSearch();
    };

    return (
        <AppLayout>
            <div className="flex flex-col gap-6 p-6">
                <Head title="Central de Controle de Coletas - PrevidIA" />
                
                {/* Cabeçalho com Título e Botão */}
                <div className="flex items-center justify-between border-b pb-4">
                    <div>
                        <h1 className="text-2xl font-bold tracking-tight">Central de Controle de Coletas</h1>
                        <p className="text-sm text-muted-foreground">
                            Gerencie e acompanhe o progresso das coletas de documentos
                        </p>
                    </div>
                    <a 
                        href="https://drive.google.com/drive/folders/1Ix5i0f-y63NErv9IdmY4PP6O7fcAAZxr?usp=sharing" 
                        target="_blank" 
                        rel="noopener noreferrer"
                        className="transition-transform hover:scale-105"
                    >
                        <Button className="flex items-center gap-2 bg-primary/10 text-primary hover:bg-primary/20">
                            <FileText className="h-5 w-5" />
                            Acessar Banco de Laudos
                        </Button>
                    </a>
                </div>

                {/* Cards de Resumo */}
                <div className="grid grid-cols-2 gap-4 md:grid-cols-3 lg:grid-cols-6">
                    <Card>
                        <CardContent className="flex flex-col items-center justify-center py-6">
                            <span className="text-2xl font-bold">{cards.totalVinculos}</span>
                            <span className="mt-1 flex items-center gap-1 text-xs text-muted-foreground">
                                <Briefcase className="h-4 w-4" />
                                Total Vínculos
                            </span>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardContent className="flex flex-col items-center justify-center py-6">
                            <span className="text-2xl font-bold">{cards.clientesAtivos}</span>
                            <span className="mt-1 flex items-center gap-1 text-xs text-muted-foreground">
                                <User className="h-4 w-4" />
                                Clientes Ativos
                            </span>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardContent className="flex flex-col items-center justify-center py-6">
                            <span className="text-2xl font-bold">{cards.clientesFinalizados}</span>
                            <span className="mt-1 flex items-center gap-1 text-xs text-muted-foreground">
                                <User className="h-4 w-4" />
                                Clientes Finalizados
                            </span>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardContent className="flex flex-col items-center justify-center py-6">
                            <span className="text-2xl font-bold">{cards.empresasPendentes}</span>
                            <span className="mt-1 flex items-center gap-1 text-xs text-muted-foreground">
                                <Building2 className="h-4 w-4" />
                                Empresas Pendentes
                            </span>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardContent className="flex flex-col items-center justify-center py-6">
                            <span className="text-2xl font-bold">{cards.empresasConcluidas}</span>
                            <span className="mt-1 flex items-center gap-1 text-xs text-muted-foreground">
                                <CheckCircle className="h-4 w-4" />
                                Empresas Concluídas
                            </span>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardContent className="flex flex-col items-center justify-center py-6">
                            <span className="text-2xl font-bold text-red-600">{cards.coletasAtrasadas}</span>
                            <span className="mt-1 flex items-center gap-1 text-xs text-muted-foreground">
                                <Clock className="h-4 w-4 text-red-600" />
                                Coletas Atrasadas
                            </span>
                        </CardContent>
                    </Card>
                </div>

                {/* Barra de Busca com Abas */}
                <Card>
                    <CardHeader>
                        <CardTitle className="flex items-center gap-2">
                            <Search className="h-5 w-5" />
                            Buscar Informações
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <Tabs value={activeTab} onValueChange={handleTabChange} className="mb-2">
                            <TabsList>
                                <TabsTrigger value="clientes">
                                    <User className="mr-1 h-4 w-4" />
                                    Buscar Clientes
                                </TabsTrigger>
                                <TabsTrigger value="empresas">
                                    <Building2 className="mr-1 h-4 w-4" />
                                    Buscar Empresas
                                </TabsTrigger>
                                <TabsTrigger value="cargos">
                                    <Briefcase className="mr-1 h-4 w-4" />
                                    Buscar por Cargo
                                </TabsTrigger>
                            </TabsList>
                        </Tabs>
                        <div className="mt-2 flex items-center gap-2">
                            <Input
                                placeholder={
                                    activeTab === 'clientes' ? 'Nome ou CPF do cliente' : activeTab === 'empresas' ? 'Nome da empresa' : 'Cargo'
                                }
                                value={searchTerm}
                                onChange={(e) => setSearchTerm(e.target.value)}
                                onKeyPress={handleKeyPress}
                            />
                            <Button onClick={handleSearch} variant="default">
                                <Search className="h-4 w-4" />
                            </Button>
                        </div>
                    </CardContent>
                </Card>

                {/* Resultados */}
                {resultados && resultados.length > 0 && (
                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center justify-between">
                                <span>Resultados da Busca</span>
                                <span className="text-sm font-normal text-muted-foreground">
                                    {resultados.length} {resultados.length === 1 ? 'resultado encontrado' : 'resultados encontrados'}
                                </span>
                            </CardTitle>
                        </CardHeader>
                        <CardContent className="p-0">
                            <div className="divide-y divide-border">
                                {activeTab === 'clientes' &&
                                    resultados.map((cliente: any, index: number) => (
                                        <div
                                            key={cliente.id}
                                            className="flex items-center justify-between p-6 hover:bg-muted/50 transition-colors"
                                        >
                                            <div className="flex items-center space-x-4">
                                                <div className="flex h-10 w-10 items-center justify-center rounded-full bg-primary/10">
                                                    <User className="h-5 w-5 text-primary" />
                                                </div>
                                                <div className="space-y-1">
                                                    <h4 className="font-semibold text-foreground">{cliente.client_name}</h4>
                                                    <div className="flex items-center space-x-4 text-sm text-muted-foreground">
                                                        <span>CPF: {cliente.client_cpf}</span>
                                                        <span>•</span>
                                                        <span className="flex items-center gap-1">
                                                            <Briefcase className="h-3 w-3" />
                                                            {cliente.employment_relationships?.filter((v: any) => v.is_active).length || 0} vínculos pendentes
                                                        </span>
                                                        <span>•</span>
                                                        <span className="flex items-center gap-1">
                                                            <CheckCircle className="h-3 w-3" />
                                                            {cliente.employment_relationships?.filter((v: any) => !v.is_active).length || 0} concluídos
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                            <Link href={`/cases/${cliente.id}/vinculos`}>
                                                <Button variant="outline" size="sm">
                                                    Ver Vínculos
                                                </Button>
                                            </Link>
                                        </div>
                                    ))}
                                {activeTab === 'empresas' &&
                                    resultados.map((vinculo: any, index: number) => {
                                        const cliente = vinculo.legalCase || vinculo.legal_case;
                                        return (
                                            <div
                                                key={vinculo.id}
                                                className="flex items-center justify-between p-6 hover:bg-muted/50 transition-colors"
                                            >
                                                <div className="flex items-center space-x-4">
                                                    <div className="flex h-10 w-10 items-center justify-center rounded-full bg-blue-100">
                                                        <Building2 className="h-5 w-5 text-blue-600" />
                                                    </div>
                                                    <div className="space-y-1">
                                                        <h4 className="font-semibold text-foreground">{vinculo.employer_name}</h4>
                                                        <div className="flex items-center space-x-4 text-sm text-muted-foreground">
                                                            <span>Cliente: {cliente?.client_name || '-'}</span>
                                                            <span>•</span>
                                                            <span className={`flex items-center gap-1 ${
                                                                vinculo.is_active ? 'text-orange-600' : 'text-green-600'
                                                            }`}>
                                                                {vinculo.is_active ? (
                                                                    <><Clock className="h-3 w-3" /> Pendente</>
                                                                ) : (
                                                                    <><CheckCircle className="h-3 w-3" /> Concluído</>
                                                                )}
                                                            </span>
                                                            {vinculo.employer_cnpj && (
                                                                <>
                                                                    <span>•</span>
                                                                    <span>CNPJ: {vinculo.employer_cnpj}</span>
                                                                </>
                                                            )}
                                                        </div>
                                                    </div>
                                                </div>
                                                {cliente && (
                                                    <Link href={`/cases/${cliente.id}/vinculos`}>
                                                        <Button variant="outline" size="sm">
                                                            Ver Vínculos
                                                        </Button>
                                                    </Link>
                                                )}
                                            </div>
                                        );
                                    })}
                                {activeTab === 'cargos' &&
                                    resultados.map((vinculo: any, index: number) => {
                                        const cliente = vinculo.legalCase || vinculo.legal_case;
                                        return (
                                            <div
                                                key={vinculo.id}
                                                className="flex items-center justify-between p-6 hover:bg-muted/50 transition-colors"
                                            >
                                                <div className="flex items-center space-x-4">
                                                    <div className="flex h-10 w-10 items-center justify-center rounded-full bg-green-100">
                                                        <Briefcase className="h-5 w-5 text-green-600" />
                                                    </div>
                                                    <div className="space-y-1">
                                                        <h4 className="font-semibold text-foreground">{vinculo.cargo || 'Cargo não informado'}</h4>
                                                        <div className="flex items-center space-x-4 text-sm text-muted-foreground">
                                                            <span>Cliente: {cliente?.client_name || '-'}</span>
                                                            <span>•</span>
                                                            <span>Empresa: {vinculo.employer_name || '-'}</span>
                                                            <span>•</span>
                                                            <span className={`flex items-center gap-1 ${
                                                                vinculo.is_active ? 'text-orange-600' : 'text-green-600'
                                                            }`}>
                                                                {vinculo.is_active ? (
                                                                    <><Clock className="h-3 w-3" /> Pendente</>
                                                                ) : (
                                                                    <><CheckCircle className="h-3 w-3" /> Concluído</>
                                                                )}
                                                            </span>
                                                        </div>
                                                    </div>
                                                </div>
                                                {cliente && (
                                                    <Link href={`/cases/${cliente.id}/vinculos`}>
                                                        <Button variant="outline" size="sm">
                                                            Ver Vínculos
                                                        </Button>
                                                    </Link>
                                                )}
                                            </div>
                                        );
                                    })}
                            </div>
                        </CardContent>
                    </Card>
                )}

                {/* Estado vazio */}
                {(!resultados || resultados.length === 0) && searchTerm && (
                    <Card>
                        <CardContent className="py-12">
                            <div className="flex flex-col items-center justify-center text-center">
                                <Search className="mb-4 h-12 w-12 text-muted-foreground" />
                                <h3 className="mb-2 text-lg font-medium">Nenhum resultado encontrado</h3>
                                <p className="text-muted-foreground max-w-md">
                                    Não encontramos resultados para "{searchTerm}" na categoria {activeTab}. 
                                    Tente refinar sua busca ou alterar a categoria.
                                </p>
                            </div>
                        </CardContent>
                    </Card>
                )}
            </div>
        </AppLayout>
    );
}
