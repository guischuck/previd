import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import AppLayout from '@/layouts/app-layout';
import { Head, Link, useForm } from '@inertiajs/react';
import { ArrowLeft, Eye, Plus, Save } from 'lucide-react';
import React, { useState } from 'react';

interface Props {
    categories: Array<{ value: string; label: string }>;
    benefitTypes: Array<{ value: string; label: string }>;
    canManageGlobal: boolean;
}

export default function TemplateCreate({ categories, benefitTypes, canManageGlobal }: Props) {
    const [previewContent, setPreviewContent] = useState('');
    const [showPreview, setShowPreview] = useState(false);

    const { data, setData, post, processing, errors } = useForm<{
        name: string;
        category: string;
        benefit_type: string;
        description: string;
        content: string;
        variables: string[];
        sections: string[];
        is_active: boolean;
        is_default: boolean;
        is_global: boolean;
    }>({
        name: '',
        category: '',
        benefit_type: '',
        description: '',
        content: '',
        variables: [],
        sections: [],
        is_active: true,
        is_default: false,
        is_global: false,
    });

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        post(route('petition-templates.store'));
    };

    const handlePreview = () => {
        setPreviewContent(data.content);
        setShowPreview(true);
    };

    const extractVariables = (content: string) => {
        const regex = /\{\{([^}]+)\}\}/g;
        const variables: string[] = [];
        let match;
        while ((match = regex.exec(content)) !== null) {
            if (!variables.includes(match[1].trim())) {
                variables.push(match[1].trim());
            }
        }
        setData('variables', variables);
    };

    const handleContentChange = (value: string) => {
        setData('content', value);
        extractVariables(value);
    };

    const breadcrumbs = [
        { title: 'Templates', href: '/petition-templates' },
        { title: 'Novo Template', href: '#' },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Criar Template de Petição" />

            <div className="space-y-4 p-4 sm:space-y-6 sm:p-6">
                {/* Header */}
                <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <div className="mb-2 flex items-center gap-2">
                            <Link href={route('petition-templates.index')}>
                                <Button variant="outline" size="sm">
                                    <ArrowLeft className="mr-2 h-4 w-4" />
                                    Voltar
                                </Button>
                            </Link>
                        </div>
                        <h1 className="text-xl font-semibold text-gray-900 sm:text-2xl dark:text-white">
                            <Plus className="mr-2 inline-block" /> Criar Template de Petição
                        </h1>
                        <p className="mt-1 text-sm text-gray-600 dark:text-gray-400">
                            Crie um novo template reutilizável para suas petições
                        </p>
                    </div>
                    <div className="flex flex-col gap-2 sm:flex-row">
                        <Button type="button" variant="outline" onClick={handlePreview} disabled={!data.content}>
                            <Eye className="mr-2 h-4 w-4" />
                            Preview
                        </Button>
                    </div>
                </div>

                <form onSubmit={handleSubmit} className="space-y-6">
                    <div className="grid grid-cols-1 gap-6 lg:grid-cols-2">
                        {/* Informações Básicas */}
                        <Card>
                            <CardHeader>
                                <CardTitle>Informações Básicas</CardTitle>
                            </CardHeader>
                            <CardContent className="space-y-4">
                                <div>
                                    <Label htmlFor="name">Nome do Template *</Label>
                                    <Input
                                        id="name"
                                        value={data.name}
                                        onChange={(e) => setData('name', e.target.value)}
                                        placeholder="Ex: Recurso de Aposentadoria por Idade"
                                        className={errors.name ? 'border-red-500' : ''}
                                        required
                                    />
                                    {errors.name && <p className="mt-1 text-sm text-red-500">{errors.name}</p>}
                                </div>

                                <div>
                                    <Label htmlFor="category">Categoria *</Label>
                                    <select
                                        id="category"
                                        value={data.category}
                                        onChange={(e) => setData('category', e.target.value)}
                                        className={`w-full rounded-md border px-3 py-2 dark:border-gray-600 dark:bg-gray-700 ${errors.category ? 'border-red-500' : 'border-gray-300'}`}
                                        required
                                    >
                                        <option value="">Selecione uma categoria</option>
                                        {categories && categories.map((cat) => (
                                            <option key={cat.value} value={cat.value}>
                                                {cat.label}
                                            </option>
                                        ))}
                                    </select>
                                    {errors.category && <p className="mt-1 text-sm text-red-500">{errors.category}</p>}
                                </div>

                                <div>
                                    <Label htmlFor="benefit_type">Tipo de Benefício (Opcional)</Label>
                                    <select
                                        id="benefit_type"
                                        value={data.benefit_type}
                                        onChange={(e) => setData('benefit_type', e.target.value)}
                                        className="w-full rounded-md border border-gray-300 px-3 py-2 dark:border-gray-600 dark:bg-gray-700"
                                    >
                                        <option value="">Selecione um tipo de benefício</option>
                                        {benefitTypes && benefitTypes.map((type) => (
                                            <option key={type.value} value={type.value}>
                                                {type.label}
                                            </option>
                                        ))}
                                    </select>
                                    {errors.benefit_type && <p className="mt-1 text-sm text-red-500">{errors.benefit_type}</p>}
                                </div>

                                <div>
                                    <Label htmlFor="description">Descrição</Label>
                                    <Textarea
                                        id="description"
                                        value={data.description}
                                        onChange={(e) => setData('description', e.target.value)}
                                        placeholder="Descreva quando usar este template..."
                                        rows={3}
                                    />
                                    {errors.description && <p className="mt-1 text-sm text-red-500">{errors.description}</p>}
                                </div>

                                <div className="space-y-2">
                                    <div className="flex items-center gap-2">
                                        <input
                                            type="checkbox"
                                            id="is_active"
                                            checked={!!data.is_active}
                                            onChange={(e) => setData('is_active', e.target.checked)}
                                            className="rounded"
                                        />
                                        <Label htmlFor="is_active">Template ativo</Label>
                                    </div>

                                    <div className="flex items-center gap-2">
                                        <input
                                            type="checkbox"
                                            id="is_default"
                                            checked={!!data.is_default}
                                            onChange={(e) => setData('is_default', e.target.checked)}
                                            className="rounded"
                                        />
                                        <Label htmlFor="is_default">Template padrão</Label>
                                    </div>

                                    {canManageGlobal && (
                                        <div className="flex items-center gap-2">
                                            <input
                                                type="checkbox"
                                                id="is_global"
                                                checked={!!data.is_global}
                                                onChange={(e) => setData('is_global', e.target.checked)}
                                                className="rounded"
                                            />
                                            <Label htmlFor="is_global">Template global (disponível para todas as empresas)</Label>
                                        </div>
                                    )}
                                </div>
                            </CardContent>
                        </Card>

                        {/* Variáveis Detectadas */}
                        {data.variables.length > 0 && (
                            <Card>
                                <CardHeader>
                                    <CardTitle>Variáveis Detectadas</CardTitle>
                                </CardHeader>
                                <CardContent>
                                    <div className="space-y-2">
                                        <p className="text-sm text-gray-600 dark:text-gray-400">
                                            Use estas variáveis no conteúdo: <code>{'{{nome_variavel}}'}</code>
                                        </p>
                                        <div className="flex flex-wrap gap-2">
                                            {data.variables.map((variable, index) => (
                                                <span
                                                    key={index}
                                                    className="inline-flex items-center rounded-md bg-blue-50 px-2 py-1 text-xs font-medium text-blue-700 ring-1 ring-inset ring-blue-700/10 dark:bg-blue-400/10 dark:text-blue-400 dark:ring-blue-400/30"
                                                >
                                                    {variable}
                                                </span>
                                            ))}
                                        </div>
                                    </div>
                                </CardContent>
                            </Card>
                        )}

                        {/* Dicas de Uso */}
                        <Card>
                            <CardHeader>
                                <CardTitle>Dicas de Uso</CardTitle>
                            </CardHeader>
                            <CardContent>
                                <div className="space-y-2 text-sm text-gray-600 dark:text-gray-400">
                                    <p>• Use <code>{'{{nome_cliente}}'}</code> para inserir o nome do cliente</p>
                                    <p>• Use <code>{'{{cpf_cliente}}'}</code> para inserir o CPF</p>
                                    <p>• Use <code>{'{{numero_caso}}'}</code> para inserir o número do caso</p>
                                    <p>• Use <code>{'{{tipo_beneficio}}'}</code> para inserir o tipo de benefício</p>
                                    <p>• Use <code>{'{{data_hoje}}'}</code> para inserir a data atual</p>
                                </div>
                            </CardContent>
                        </Card>
                    </div>

                    {/* Conteúdo do Template */}
                    <Card>
                        <CardHeader>
                            <CardTitle>Conteúdo do Template *</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div>
                                <Textarea
                                    value={data.content}
                                    onChange={(e) => handleContentChange(e.target.value)}
                                    placeholder={`Digite o conteúdo do template aqui... 

Exemplo:
EXCELENTÍSSIMO SENHOR JUIZ FEDERAL DA {{vara}} VARA FEDERAL DE {{cidade}}

{{nome_cliente}}, brasileiro, {{estado_civil}}, portador do CPF {{cpf_cliente}}, por intermédio de seu advogado que esta subscreve, vem, respeitosamente, à presença de Vossa Excelência, propor a presente

AÇÃO PREVIDENCIÁRIA

em face do INSTITUTO NACIONAL DO SEGURO SOCIAL - INSS, autarquia federal com sede em Brasília/DF...`}
                                    rows={15}
                                    className={`font-mono text-sm ${errors.content ? 'border-red-500' : ''}`}
                                    required
                                />
                                {errors.content && <p className="mt-1 text-sm text-red-500">{errors.content}</p>}
                            </div>
                        </CardContent>
                    </Card>

                    {/* Botões de Ação */}
                    <div className="flex flex-col gap-2 sm:flex-row sm:justify-end">
                        <Link href={route('petition-templates.index')}>
                            <Button type="button" variant="outline" className="w-full sm:w-auto">
                                Cancelar
                            </Button>
                        </Link>
                        <Button type="submit" disabled={processing} className="w-full sm:w-auto">
                            {processing ? (
                                <>
                                    <div className="mr-2 h-4 w-4 animate-spin rounded-full border-2 border-solid border-current border-r-transparent" />
                                    Criando...
                                </>
                            ) : (
                                <>
                                    <Save className="mr-2 h-4 w-4" />
                                    Criar Template
                                </>
                            )}
                        </Button>
                    </div>
                </form>

                {/* Modal de Preview */}
                {showPreview && (
                    <div className="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50" onClick={() => setShowPreview(false)}>
                        <div className="max-h-[80vh] w-full max-w-4xl overflow-auto rounded-lg bg-white p-6 dark:bg-gray-800" onClick={(e) => e.stopPropagation()}>
                            <div className="mb-4 flex items-center justify-between">
                                <h3 className="text-lg font-semibold">Preview do Template</h3>
                                <Button variant="outline" size="sm" onClick={() => setShowPreview(false)}>
                                    Fechar
                                </Button>
                            </div>
                            <div className="prose max-w-none whitespace-pre-wrap rounded border bg-gray-50 p-4 font-mono text-sm dark:bg-gray-900">
                                {previewContent}
                            </div>
                        </div>
                    </div>
                )}
            </div>
        </AppLayout>
    );
}
