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
                        <CardContent className="flex flex-col items-center justify-center py-4">
                            <span className="text-2xl font-bold">{cards.totalVinculos}</span>
                            <span className="mt-1 flex items-center gap-1 text-xs text-muted-foreground">
                                <Briefcase className="h-4 w-4" />
                                Total Vínculos
                            </span>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardContent className="flex flex-col items-center justify-center py-4">
                            <span className="text-2xl font-bold">{cards.clientesAtivos}</span>
                            <span className="mt-1 flex items-center gap-1 text-xs text-muted-foreground">
                                <User className="h-4 w-4" />
                                Clientes Ativos
                            </span>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardContent className="flex flex-col items-center justify-center py-4">
                            <span className="text-2xl font-bold">{cards.clientesFinalizados}</span>
                            <span className="mt-1 flex items-center gap-1 text-xs text-muted-foreground">
                                <User className="h-4 w-4" />
                                Clientes Finalizados
                            </span>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardContent className="flex flex-col items-center justify-center py-4">
                            <span className="text-2xl font-bold">{cards.empresasPendentes}</span>
                            <span className="mt-1 flex items-center gap-1 text-xs text-muted-foreground">
                                <Building2 className="h-4 w-4" />
                                Empresas Pendentes
                            </span>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardContent className="flex flex-col items-center justify-center py-4">
                            <span className="text-2xl font-bold">{cards.empresasConcluidas}</span>
                            <span className="mt-1 flex items-center gap-1 text-xs text-muted-foreground">
                                <CheckCircle className="h-4 w-4" />
                                Empresas Concluídas
                            </span>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardContent className="flex flex-col items-center justify-center py-4">
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
                <Card>
                    <CardContent className="py-8">
    {resultados && resultados.length > 0 ? (
        <div className="grid grid-cols-1 md:grid-cols-2 gap-6 justify-center">
            {activeTab === 'clientes' &&
                resultados.map((cliente: any) => (
                    <div
                        key={cliente.id}
                        className="flex flex-col justify-between rounded-xl border shadow-lg bg-background/90 p-6 transition-transform hover:scale-[1.02] hover:shadow-xl min-w-0"
                    >
                        <div className="flex-1 mb-4">
                            <div className="text-xl font-bold text-primary mb-1 truncate">{cliente.client_name}</div>
                            <div className="text-sm text-muted-foreground mb-1">CPF: {cliente.client_cpf}</div>
                            <div className="text-xs text-muted-foreground">Vínculos pendentes: {cliente.employment_relationships?.filter((v: any) => !v.collected_at).length}</div>
                        </div>
                        <div className="flex justify-end">
                            <Link href={`/cases/${cliente.id}/vinculos`}>
                                <Button variant="outline" className="text-xs">Ver Vínculos</Button>
                            </Link>
                        </div>
                    </div>
                ))}
            {activeTab !== 'clientes' &&
                resultados.map((vinculo: any) => {
                    const cliente = vinculo.legalCase || vinculo.legal_case;
                    return (
                        <div
                            key={vinculo.id}
                            className="flex flex-col justify-between rounded-xl border shadow-lg bg-background/90 p-6 transition-transform hover:scale-[1.02] hover:shadow-xl min-w-0"
                        >
                            <div className="flex-1 mb-4">
                                <div className="text-xl font-bold text-primary mb-1 truncate">{vinculo.employer_name || vinculo.position}</div>
                                <div className="text-sm text-muted-foreground mb-1">Cliente: {cliente?.client_name || '-'}</div>
                                <div className="text-xs text-muted-foreground">Status: {vinculo.collected_at ? 'Concluído' : 'Pendente'}</div>
                            </div>
                            <div className="flex justify-end">
                                {cliente && (
                                    <Link href={`/cases/${cliente.id}/vinculos`}>
                                        <Button variant="outline" className="text-xs">Ver Vínculos</Button>
                                    </Link>
                                )}
                            </div>
                        </div>
                    );
                })}
        </div>
    ) : (
        <div className="flex flex-col items-center justify-center py-12">
            <Search className="mb-4 h-12 w-12 text-muted-foreground" />
            <h3 className="mb-2 text-lg font-medium">Nenhum resultado encontrado</h3>
            <p className="mb-4 text-muted-foreground">Tente refinar sua busca ou alterar os filtros/abas acima.</p>
        </div>
    )}
</CardContent>

                </Card>
            </div>
        </AppLayout>
    );
}
