import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, usePage } from '@inertiajs/react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { 
    HelpCircle, 
    Mail, 
    MessageCircle, 
    CheckCircle, 
    AlertCircle,
    LayoutGrid,
    Briefcase,
    Upload,
    Clock,
    FileText,
    BookOpen,
    GitBranch,
    Settings,
    MessageSquare,
    Chrome
} from 'lucide-react';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
    {
        title: 'Suporte',
        href: '/support',
    },
];

export default function SupportIndex() {
    const { flash } = usePage().props as any;

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Suporte" />
            <div className="flex h-full flex-1 flex-col gap-6 p-4 md:p-6">
                {/* Header */}
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-2xl md:text-3xl font-bold">Suporte</h1>
                        <p className="text-muted-foreground text-sm md:text-base">
                            Entre em contato conosco para obter ajuda
                        </p>
                    </div>
                </div>

                {/* Alertas */}
                {flash?.success && (
                    <Card className="border-green-200 bg-green-50 dark:border-green-800 dark:bg-green-900/20">
                        <CardContent className="p-4">
                            <div className="flex items-center gap-2">
                                <CheckCircle className="h-5 w-5 text-green-600" />
                                <p className="text-green-800 dark:text-green-200">{flash.success}</p>
                            </div>
                        </CardContent>
                    </Card>
                )}

                {flash?.error && (
                    <Card className="border-red-200 bg-red-50 dark:border-red-800 dark:bg-red-900/20">
                        <CardContent className="p-4">
                            <div className="flex items-center gap-2">
                                <AlertCircle className="h-5 w-5 text-red-600" />
                                <p className="text-red-800 dark:text-red-200">{flash.error}</p>
                            </div>
                        </CardContent>
                    </Card>
                )}

                {/* Informações de contato */}
                <Card className="max-w-xl">
                    <CardHeader>
                        <CardTitle className="flex items-center gap-2">
                            <HelpCircle className="h-5 w-5" />
                            Informações de Contato
                        </CardTitle>
                    </CardHeader>
                    <CardContent className="space-y-4">
                        <div className="flex items-center gap-3">
                            <Mail className="h-5 w-5 text-muted-foreground" />
                            <div>
                                <p className="font-medium">Email</p>
                                <p className="text-sm text-muted-foreground">suporte@previdia.com.br</p>
                            </div>
                        </div>
                        
                        <div className="flex items-center gap-3">
                            <MessageCircle className="h-5 w-5 text-muted-foreground" />
                            <div>
                                <p className="font-medium">Resposta</p>
                                <p className="text-sm text-muted-foreground">Em até 24 horas</p>
                            </div>
                        </div>

                        <div className="pt-4 border-t">
                            <h4 className="font-medium mb-2">Como podemos ajudar?</h4>
                            <ul className="text-sm text-muted-foreground space-y-1">
                                <li>• Dúvidas sobre funcionalidades</li>
                                <li>• Problemas técnicos</li>
                                <li>• Solicitações de melhorias</li>
                                <li>• Relatórios de bugs</li>
                                <li>• Dúvidas sobre planos</li>
                            </ul>
                        </div>
                    </CardContent>
                </Card>

                {/* Manual de Uso */}
                <div className="mt-8">
                    <h2 className="text-2xl font-bold mb-6">Manual de Uso - PrevidIA</h2>
                    
                    <div className="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                        {/* Dashboard */}
                        <Card>
                            <CardHeader>
                                <CardTitle className="flex items-center gap-2 text-lg">
                                    <LayoutGrid className="h-5 w-5 text-blue-500" />
                                    Dashboard
                                </CardTitle>
                            </CardHeader>
                            <CardContent className="space-y-3">
                                <p className="text-sm text-muted-foreground">
                                    Visão geral do sistema com estatísticas e acesso rápido às principais funcionalidades.
                                </p>
                                <div className="space-y-2">
                                    <h4 className="font-medium text-sm">Funcionalidades:</h4>
                                    <ul className="text-xs text-muted-foreground space-y-1">
                                        <li>• Estatísticas de casos</li>
                                        <li>• Ações rápidas</li>
                                        <li>• Processos INSS</li>
                                        <li>• Resumo de atividades</li>
                                    </ul>
                                </div>
                            </CardContent>
                        </Card>

                        {/* Casos */}
                        <Card>
                            <CardHeader>
                                <CardTitle className="flex items-center gap-2 text-lg">
                                    <Briefcase className="h-5 w-5 text-green-500" />
                                    Casos
                                </CardTitle>
                            </CardHeader>
                            <CardContent className="space-y-3">
                                <p className="text-sm text-muted-foreground">
                                    Gerenciamento completo de casos jurídicos com vínculos empregatícios e documentos.
                                </p>
                                <div className="space-y-2">
                                    <h4 className="font-medium text-sm">Funcionalidades:</h4>
                                    <ul className="text-xs text-muted-foreground space-y-1">
                                        <li>• Criar novos casos</li>
                                        <li>• Gerenciar vínculos</li>
                                        <li>• Upload de documentos</li>
                                        <li>• Acompanhar status</li>
                                        <li>• Workflows automatizados</li>
                                    </ul>
                                </div>
                            </CardContent>
                        </Card>

                        {/* Coletas */}
                        <Card>
                            <CardHeader>
                                <CardTitle className="flex items-center gap-2 text-lg">
                                    <Upload className="h-5 w-5 text-purple-500" />
                                    Coletas
                                </CardTitle>
                            </CardHeader>
                            <CardContent className="space-y-3">
                                <p className="text-sm text-muted-foreground">
                                    Sistema de coleta de documentos CNIS com processamento automático via IA.
                                </p>
                                <div className="space-y-2">
                                    <h4 className="font-medium text-sm">Funcionalidades:</h4>
                                    <ul className="text-xs text-muted-foreground space-y-1">
                                        <li>• Upload de CNIS</li>
                                        <li>• Extração automática</li>
                                        <li>• Validação de dados</li>
                                        <li>• Histórico de coletas</li>
                                    </ul>
                                </div>
                            </CardContent>
                        </Card>

                        {/* Andamentos */}
                        <Card>
                            <CardHeader>
                                <CardTitle className="flex items-center gap-2 text-lg">
                                    <Clock className="h-5 w-5 text-orange-500" />
                                    Andamentos
                                </CardTitle>
                            </CardHeader>
                            <CardContent className="space-y-3">
                                <p className="text-sm text-muted-foreground">
                                    Acompanhamento de mudanças de situação nos processos do INSS.
                                </p>
                                <div className="space-y-2">
                                    <h4 className="font-medium text-sm">Funcionalidades:</h4>
                                    <ul className="text-xs text-muted-foreground space-y-1">
                                        <li>• Histórico de mudanças</li>
                                        <li>• Notificações automáticas</li>
                                        <li>• Filtros por situação</li>
                                        <li>• Marcação de visualização</li>
                                    </ul>
                                </div>
                            </CardContent>
                        </Card>

                        {/* Processos INSS */}
                        <Card>
                            <CardHeader>
                                <CardTitle className="flex items-center gap-2 text-lg">
                                    <FileText className="h-5 w-5 text-indigo-500" />
                                    Processos INSS
                                </CardTitle>
                            </CardHeader>
                            <CardContent className="space-y-3">
                                <p className="text-sm text-muted-foreground">
                                    Gestão de processos do INSS com acompanhamento de prazos e situações.
                                </p>
                                <div className="space-y-2">
                                    <h4 className="font-medium text-sm">Funcionalidades:</h4>
                                    <ul className="text-xs text-muted-foreground space-y-1">
                                        <li>• Lista de processos</li>
                                        <li>• Filtros por status</li>
                                        <li>• Prazos de exigência</li>
                                        <li>• Link direto ao INSS</li>
                                    </ul>
                                </div>
                            </CardContent>
                        </Card>

                        {/* Petições */}
                        <Card>
                            <CardHeader>
                                <CardTitle className="flex items-center gap-2 text-lg">
                                    <BookOpen className="h-5 w-5 text-red-500" />
                                    Petições
                                </CardTitle>
                            </CardHeader>
                            <CardContent className="space-y-3">
                                <p className="text-sm text-muted-foreground">
                                    Criação de petições com assistência de IA e templates personalizáveis.
                                </p>
                                <div className="space-y-2">
                                    <h4 className="font-medium text-sm">Funcionalidades:</h4>
                                    <ul className="text-xs text-muted-foreground space-y-1">
                                        <li>• Geração com IA</li>
                                        <li>• Templates personalizados</li>
                                        <li>• Download em PDF</li>
                                        <li>• Histórico de petições</li>
                                    </ul>
                                </div>
                            </CardContent>
                        </Card>

                        {/* Workflows */}
                        <Card>
                            <CardHeader>
                                <CardTitle className="flex items-center gap-2 text-lg">
                                    <GitBranch className="h-5 w-5 text-teal-500" />
                                    Workflows
                                </CardTitle>
                            </CardHeader>
                            <CardContent className="space-y-3">
                                <p className="text-sm text-muted-foreground">
                                    Automação de tarefas e processos com workflows personalizáveis.
                                </p>
                                <div className="space-y-2">
                                    <h4 className="font-medium text-sm">Funcionalidades:</h4>
                                    <ul className="text-xs text-muted-foreground space-y-1">
                                        <li>• Templates de workflow</li>
                                        <li>• Tarefas automatizadas</li>
                                        <li>• Acompanhamento de progresso</li>
                                        <li>• Notificações</li>
                                    </ul>
                                </div>
                            </CardContent>
                        </Card>

                        {/* Chat */}
                        <Card>
                            <CardHeader>
                                <CardTitle className="flex items-center gap-2 text-lg">
                                    <MessageSquare className="h-5 w-5 text-pink-500" />
                                    Chat IA
                                </CardTitle>
                            </CardHeader>
                            <CardContent className="space-y-3">
                                <p className="text-sm text-muted-foreground">
                                    Assistente jurídico com IA para dúvidas e orientações.
                                </p>
                                <div className="space-y-2">
                                    <h4 className="font-medium text-sm">Funcionalidades:</h4>
                                    <ul className="text-xs text-muted-foreground space-y-1">
                                        <li>• Consultas jurídicas</li>
                                        <li>• Análise de casos</li>
                                        <li>• Orientações legais</li>
                                        <li>• Histórico de conversas</li>
                                    </ul>
                                </div>
                            </CardContent>
                        </Card>

                        {/* Configurações */}
                        <Card>
                            <CardHeader>
                                <CardTitle className="flex items-center gap-2 text-lg">
                                    <Settings className="h-5 w-5 text-gray-500" />
                                    Configurações
                                </CardTitle>
                            </CardHeader>
                            <CardContent className="space-y-3">
                                <p className="text-sm text-muted-foreground">
                                    Configurações do perfil e preferências do sistema.
                                </p>
                                <div className="space-y-2">
                                    <h4 className="font-medium text-sm">Funcionalidades:</h4>
                                    <ul className="text-xs text-muted-foreground space-y-1">
                                        <li>• Perfil do usuário</li>
                                        <li>• Preferências</li>
                                        <li>• Notificações</li>
                                        <li>• Segurança</li>
                                    </ul>
                                </div>
                            </CardContent>
                        </Card>
                    </div>

                    {/* Extensão do Navegador */}
                    <div className="mt-8">
                        <Card>
                            <CardHeader>
                                <CardTitle className="flex items-center gap-2">
                                    <Chrome className="h-5 w-5" />
                                    Extensão do Navegador
                                </CardTitle>
                            </CardHeader>
                            <CardContent>
                                <div className="space-y-6">
                                    <div>
                                        <h4 className="font-medium mb-3">Instalação da Extensão</h4>
                                        <p className="text-sm text-muted-foreground mb-4">
                                            Para automatizar a coleta de dados do Gerid, é necessário instalar nossa extensão compatível com Chrome e Edge.
                                        </p>
                                        <div className="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
                                            <h5 className="font-medium text-blue-800 dark:text-blue-200 mb-2">Passos para Instalação:</h5>
                                            <ol className="text-sm text-blue-700 dark:text-blue-300 space-y-2">
                                                <li>1. Baixe a extensão do link fornecido pela equipe</li>
                                                <li>2. Abra o Chrome/Edge e vá em Extensões (Ctrl+Shift+E)</li>
                                                <li>3. Ative o "Modo desenvolvedor"</li>
                                                <li>4. Clique em "Carregar sem compactação"</li>
                                                <li>5. Selecione a pasta da extensão baixada</li>
                                            </ol>
                                        </div>
                                    </div>

                                    <div>
                                        <h4 className="font-medium mb-3">Configuração da Chave API</h4>
                                        <div className="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-4">
                                            <h5 className="font-medium text-green-800 dark:text-green-200 mb-2">Como obter sua Chave API:</h5>
                                            <ol className="text-sm text-green-700 dark:text-green-300 space-y-2">
                                                <li>1. Acesse o PrevidIA</li>
                                                <li>2. Vá em "Configurações" no menu lateral</li>
                                                <li>3. Localize a seção "Chave API"</li>
                                                <li>4. Copie sua chave única</li>
                                                <li>5. Cole a chave na extensão do navegador</li>
                                            </ol>
                                        </div>
                                    </div>

                                    <div>
                                        <h4 className="font-medium mb-3">Como Usar a Extensão</h4>
                                        <div className="bg-orange-50 dark:bg-orange-900/20 border border-orange-200 dark:border-orange-800 rounded-lg p-4">
                                            <h5 className="font-medium text-orange-800 dark:text-orange-200 mb-2">Passos para Coleta Automática:</h5>
                                            <ol className="text-sm text-orange-700 dark:text-orange-300 space-y-2">
                                                <li>1. Abra o site do Gerid (gerid.inss.gov.br)</li>
                                                <li>2. Faça login com suas credenciais</li>
                                                <li>3. Clique no botão "Buscar"</li>
                                                <li>4. Selecione "500 processos por página"</li>
                                                <li>5. Clique em "Verificar" para iniciar a coleta</li>
                                                <li>6. A extensão irá coletar automaticamente os dados</li>
                                                <li>7. Os dados serão enviados para o PrevidIA</li>
                                            </ol>
                                        </div>
                                    </div>

                                    <div className="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-4">
                                        <h5 className="font-medium text-yellow-800 dark:text-yellow-200 mb-2">⚠️ Importante:</h5>
                                        <ul className="text-sm text-yellow-700 dark:text-yellow-300 space-y-1">
                                            <li>• Mantenha a aba do Gerid aberta durante a coleta</li>
                                            <li>• Não navegue para outras páginas durante o processo</li>
                                            <li>• A extensão funciona apenas no site oficial do Gerid</li>
                                            <li>• Em caso de erro, recarregue a página e tente novamente</li>
                                        </ul>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>
                    </div>

                    {/* Dicas de Uso */}
                    <div className="mt-8">
                        <Card>
                            <CardHeader>
                                <CardTitle className="flex items-center gap-2">
                                    <HelpCircle className="h-5 w-5" />
                                    Dicas de Uso
                                </CardTitle>
                            </CardHeader>
                            <CardContent>
                                <div className="grid gap-4 md:grid-cols-2">
                                    <div className="space-y-3">
                                        <h4 className="font-medium">Primeiros Passos</h4>
                                        <ul className="text-sm text-muted-foreground space-y-2">
                                            <li>• Comece criando um novo caso no menu "Casos"</li>
                                            <li>• Faça upload do CNIS na seção "Coletas"</li>
                                            <li>• Configure workflows para automatizar tarefas</li>
                                            <li>• Use o Chat IA para dúvidas jurídicas</li>
                                        </ul>
                                    </div>
                                    <div className="space-y-3">
                                        <h4 className="font-medium">Produtividade</h4>
                                        <ul className="text-sm text-muted-foreground space-y-2">
                                            <li>• Use filtros para encontrar informações rapidamente</li>
                                            <li>• Configure notificações para acompanhar mudanças</li>
                                            <li>• Utilize templates para padronizar documentos</li>
                                            <li>• Mantenha os dados sempre atualizados</li>
                                        </ul>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>
                    </div>
                </div>
            </div>
        </AppLayout>
    );
} 