import { useForm } from '@inertiajs/react';
import { useState, useRef } from 'react';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Progress } from '@/components/ui/progress';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';
import AppLayout from '@/layouts/app-layout';
import { Head, Link } from '@inertiajs/react';
import { ArrowLeft, Upload, Loader2, CircleAlert, CircleCheck, User, Plus, FileText, CheckCircle } from 'lucide-react';

interface BenefitTypes {
    [key: string]: string;
}

interface CNISData {
    client_name?: string;
    client_cpf?: string;
    vinculos_empregaticios?: Array<{
        empregador: string;
        cnpj: string;
        data_inicio: string;
        data_fim: string;
        salario: string;
    }>;
}

interface CreateProps {
    benefitTypes: BenefitTypes;
}

export default function Create({ benefitTypes }: CreateProps) {
    const { data, setData, post, processing, errors } = useForm({
        client_name: '',
        client_cpf: '',
        benefit_type: '',
        notes: '',
        vinculos_empregaticios: [] as Array<{
            empregador: string;
            cnpj: string;
            data_inicio: string;
            data_fim: string;
            salario?: string;
        }>,
    });

    const [cnisFile, setCnisFile] = useState<File | null>(null);
    const [cnisData, setCnisData] = useState<CNISData | null>(null);
    const [isProcessingCnis, setIsProcessingCnis] = useState(false);
    const [uploadProgress, setUploadProgress] = useState(0);
    const [cnisError, setCnisError] = useState<string | null>(null);
    const [dragActive, setDragActive] = useState(false);
    const fileInputRef = useRef<HTMLInputElement>(null);

    const breadcrumbs = [
        { title: 'Casos', href: '/cases' },
        { title: 'Novo Caso', href: '#' },
    ];

    const handleDragEnter = (e: React.DragEvent) => {
        e.preventDefault();
        setDragActive(true);
    };

    const handleDragLeave = (e: React.DragEvent) => {
        e.preventDefault();
        setDragActive(false);
    };

    const handleDragOver = (e: React.DragEvent) => {
        e.preventDefault();
        setDragActive(false);
        const files = e.dataTransfer.files;
        if (files.length > 0 && files[0].type === 'application/pdf') {
            setCnisFile(files[0]);
            setCnisData(null); // Limpar dados anteriores
            setCnisError(null); // Limpar erros anteriores
        }
    };

    const handleFileInput = (e: React.ChangeEvent<HTMLInputElement>) => {
        const file = e.target.files?.[0];
        if (file && file.type === 'application/pdf') {
            setCnisFile(file);
            setCnisData(null); // Limpar dados anteriores
            setCnisError(null); // Limpar erros anteriores
        }
    };

    const handleClick = () => {
        fileInputRef.current?.click();
    };

    const processCnis = async () => {
        if (!cnisFile) return;

        setIsProcessingCnis(true);
        setUploadProgress(0);
        setCnisError(null);

        const progressInterval = setInterval(() => {
            setUploadProgress((prev) => (prev >= 90 ? prev : prev + Math.random() * 15));
        }, 200);

        try {
            const formData = new FormData();
            formData.append('cnis_file', cnisFile);

            const response = await fetch('/api/process-cnis', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                },
            });

            const result = await response.json();

            if (result.success && result.data) {
                setCnisData(result.data);
                setData({
                    ...data,
                    client_name: result.data.client_name || '',
                    client_cpf: result.data.client_cpf || '',
                    vinculos_empregaticios: result.data.vinculos_empregaticios || [],
                });
                setUploadProgress(100);
            } else {
                setCnisError(result.error || 'Erro ao processar o arquivo CNIS');
            }
        } catch (error) {
            setCnisError('Erro de conexão. Tente novamente.');
        } finally {
            clearInterval(progressInterval);
            setIsProcessingCnis(false);
        }
    };

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        console.log('Enviando dados:', data);
        post('/cases', {
            onSuccess: () => {
                console.log('Caso criado com sucesso!');
            },
            onError: (errors) => {
                console.error('Erro ao criar caso:', errors);
            },
        });
    };

    const addVinculo = () => {
        const newVinculo = {
            empregador: '',
            cnpj: '',
            data_inicio: '',
            data_fim: '',
        };
        const currentVinculos = Array.isArray(data.vinculos_empregaticios) ? data.vinculos_empregaticios : [];
        setData('vinculos_empregaticios', [...currentVinculos, newVinculo]);
    };

    const updateVinculo = (index: number, field: string, value: string) => {
        const vinculos = [...(Array.isArray(data.vinculos_empregaticios) ? data.vinculos_empregaticios : [])];
        vinculos[index] = { ...vinculos[index], [field]: value };
        setData('vinculos_empregaticios', vinculos);
    };

    const removeVinculo = (index: number) => {
        const vinculos = (Array.isArray(data.vinculos_empregaticios) ? data.vinculos_empregaticios : []).filter((_, i) => i !== index);
        setData('vinculos_empregaticios', vinculos);
    };

    const vinculos = Array.isArray(data.vinculos_empregaticios) ? data.vinculos_empregaticios : [];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Novo Caso - Sistema Jurídico" />
            <div className="container mx-auto px-6 py-8">
                <div className="mx-auto max-w-4xl">
                    <div className="mb-8 flex items-center justify-between">
                        <div>
                            <h1 className="text-3xl font-bold text-foreground">Criar Novo Caso</h1>
                            <p className="mt-2 text-muted-foreground">Faça upload do CNIS ou preencha manualmente os dados do cliente</p>
                        </div>
                        <Link href="/cases">
                            <Button variant="outline" className="gap-2">
                                <ArrowLeft className="h-4 w-4" />
                                Voltar
                            </Button>
                        </Link>
                    </div>

                    <div className="grid gap-8">
                        {/* CNIS Upload Section */}
                        <Card>
                            <CardHeader>
                                <CardTitle className="flex items-center gap-2">
                                    <Upload className="h-5 w-5" />
                                    Upload do CNIS (Opcional)
                                </CardTitle>
                                <CardDescription>Faça upload do arquivo PDF do CNIS para extração automática dos dados</CardDescription>
                            </CardHeader>
                            <CardContent className="space-y-6">
                                <div
                                    className={`relative cursor-pointer rounded-xl border-2 border-dashed p-8 text-center transition-all duration-300 ${
                                        dragActive
                                            ? 'border-blue-500 bg-blue-50 dark:bg-blue-950/30'
                                            : cnisFile
                                            ? 'border-green-500 bg-green-50 dark:bg-green-950/30'
                                            : 'border-gray-300 hover:border-gray-400'
                                    }`}
                                    onClick={handleClick}
                                    onDragEnter={handleDragEnter}
                                    onDragLeave={handleDragLeave}
                                    onDragOver={handleDragOver}
                                    onDrop={handleDragOver}
                                >
                                    <input
                                        ref={fileInputRef}
                                        type="file"
                                        accept=".pdf"
                                        onChange={handleFileInput}
                                        className="hidden"
                                    />

                                    {cnisFile ? (
                                        <div className="space-y-4" onClick={(e) => e.stopPropagation()}>
                                            <div className="flex items-center justify-center gap-2 text-green-600">
                                                <FileText className="h-8 w-8" />
                                                <div>
                                                    <p className="font-medium">{cnisFile.name}</p>
                                                    <p className="text-sm text-gray-500">{(cnisFile.size / 1024 / 1024).toFixed(2)} MB</p>
                                                </div>
                                            </div>

                                            {!isProcessingCnis && !cnisData && (
                                                <Button onClick={(e) => {
                                                    e.stopPropagation();
                                                    processCnis();
                                                }} disabled={isProcessingCnis}>
                                                    {isProcessingCnis ? (
                                                        <>
                                                            <Loader2 className="mr-2 h-4 w-4 animate-spin" />
                                                            Processando...
                                                        </>
                                                    ) : (
                                                        <>
                                                            <FileText className="mr-2 h-4 w-4" />
                                                            Extrair Dados
                                                        </>
                                                    )}
                                                </Button>
                                            )}
                                        </div>
                                    ) : (
                                        <div className="space-y-4">
                                            <Upload className="mx-auto h-12 w-12 text-muted-foreground" />
                                            <div>
                                                <h3 className="text-lg font-semibold">Arraste o arquivo CNIS aqui</h3>
                                                <p className="text-muted-foreground">ou clique para selecionar (apenas PDF)</p>
                                            </div>
                                        </div>
                                    )}
                                </div>

                                {/* Upload Progress */}
                                {isProcessingCnis && (
                                    <div className="space-y-3 rounded-lg bg-blue-50 p-4 dark:bg-blue-950/30">
                                        <div className="flex items-center gap-2">
                                            <Loader2 className="h-4 w-4 animate-spin" />
                                            <span className="text-sm font-medium">Processando arquivo CNIS...</span>
                                        </div>
                                        <Progress value={uploadProgress} className="h-2" />
                                        <p className="text-xs text-muted-foreground">
                                            Extraindo dados do cliente e vínculos empregatícios
                                        </p>
                                    </div>
                                )}

                                {/* Error Message */}
                                {cnisError && (
                                    <div className="rounded-lg bg-red-50 p-4 dark:bg-red-950/30">
                                        <div className="flex items-center gap-2">
                                            <CircleAlert className="h-4 w-4 text-red-500" />
                                            <p className="font-medium text-red-800 dark:text-red-200">Erro no processamento</p>
                                        </div>
                                        <p className="mt-1 text-sm text-red-700 dark:text-red-300">{cnisError}</p>
                                    </div>
                                )}

                                {/* Success Message */}
                                {cnisData && (
                                    <div className="rounded-lg bg-green-50 p-4 dark:bg-green-950/30">
                                        <div className="flex items-center gap-2">
                                            <CircleCheck className="h-4 w-4 text-green-500" />
                                            <p className="font-medium text-green-800 dark:text-green-200">Dados extraídos com sucesso!</p>
                                            <p className="mt-1 text-sm text-green-700 dark:text-green-300">
                                                {cnisData.vinculos_empregaticios?.length || 0} vínculos encontrados
                                            </p>
                                        </div>
                                    </div>
                                )}
                            </CardContent>
                        </Card>

                        {/* Form Section */}
                        <form onSubmit={handleSubmit} className="space-y-8">
                            {/* Client Data */}
                            <Card>
                                <CardHeader>
                                    <CardTitle className="flex items-center gap-2">
                                        <User className="h-5 w-5" />
                                        Dados do Cliente
                                    </CardTitle>
                                </CardHeader>
                                <CardContent className="grid gap-6 md:grid-cols-2">
                                    <div className="space-y-2">
                                        <Label htmlFor="client_name">Nome Completo *</Label>
                                        <Input
                                            id="client_name"
                                            value={String(data.client_name || '')}
                                            onChange={(e) => setData('client_name', e.target.value)}
                                            placeholder="Digite o nome completo do cliente"
                                            required
                                        />
                                        {errors.client_name && <p className="text-sm text-red-600">{errors.client_name}</p>}
                                    </div>

                                    <div className="space-y-2">
                                        <Label htmlFor="client_cpf">CPF *</Label>
                                        <Input
                                            id="client_cpf"
                                            value={String(data.client_cpf || '')}
                                            onChange={(e) => setData('client_cpf', e.target.value)}
                                            placeholder="000.000.000-00"
                                            required
                                        />
                                        {errors.client_cpf && <p className="text-sm text-red-600">{errors.client_cpf}</p>}
                                    </div>

                                    <div className="space-y-2">
                                        <Label htmlFor="benefit_type">Tipo de Benefício</Label>
                                        <Select value={data.benefit_type} onValueChange={(value) => setData('benefit_type', value)}>
                                            <SelectTrigger>
                                                <SelectValue placeholder="Selecione o tipo de benefício" />
                                            </SelectTrigger>
                                            <SelectContent>
                                                {Object.entries(benefitTypes).map(([key, value]) => (
                                                    <SelectItem key={key} value={key}>
                                                        {value}
                                                    </SelectItem>
                                                ))}
                                            </SelectContent>
                                        </Select>
                                        {errors.benefit_type && <p className="text-sm text-red-600">{errors.benefit_type}</p>}
                                    </div>

                                    <div className="space-y-2 md:col-span-2">
                                        <Label htmlFor="notes">Observações</Label>
                                        <Textarea
                                            id="notes"
                                            value={data.notes}
                                            onChange={(e) => setData('notes', e.target.value)}
                                            placeholder="Observações adicionais sobre o caso..."
                                            rows={4}
                                        />
                                        {errors.notes && <p className="text-sm text-red-600">{errors.notes}</p>}
                                    </div>
                                </CardContent>
                            </Card>

                            {/* Employment History */}
                            <Card>
                                <CardHeader>
                                    <div className="flex items-center justify-between">
                                        <CardTitle>Vínculos Empregatícios</CardTitle>
                                        <Button type="button" variant="outline" size="sm" onClick={addVinculo}>
                                            <Plus className="mr-2 h-4 w-4" />
                                            Adicionar Vínculo
                                        </Button>
                                    </div>
                                    <CardDescription>
                                        Adicione os vínculos empregatícios do cliente. Estes dados podem ser extraídos automaticamente do CNIS.
                                    </CardDescription>
                                </CardHeader>
                                <CardContent>
                                    {vinculos.length === 0 ? (
                                        <div className="py-8 text-center text-muted-foreground">
                                            <p>Nenhum vínculo empregatício adicionado.</p>
                                            <p>Faça upload do CNIS ou adicione manualmente.</p>
                                        </div>
                                    ) : (
                                        <div className="space-y-6">
                                            {vinculos.map((vinculo, index) => (
                                                <div key={index} className="rounded-lg border p-4">
                                                    <div className="mb-3 flex items-center justify-between">
                                                        <h4 className="font-medium">Vínculo {index + 1}</h4>
                                                        <Button
                                                            type="button"
                                                            variant="ghost"
                                                            size="sm"
                                                            onClick={() => removeVinculo(index)}
                                                            className="text-red-600 hover:text-red-700"
                                                        >
                                                            Remover
                                                        </Button>
                                                    </div>
                                                    <div className="grid gap-4 md:grid-cols-2">
                                                        <div className="space-y-2">
                                                            <Label>Empregador</Label>
                                                            <Input
                                                                value={vinculo.empregador}
                                                                onChange={(e) => updateVinculo(index, 'empregador', e.target.value)}
                                                                placeholder="Nome da empresa"
                                                            />
                                                        </div>
                                                        <div className="space-y-2">
                                                            <Label>CNPJ</Label>
                                                            <Input
                                                                value={vinculo.cnpj}
                                                                onChange={(e) => updateVinculo(index, 'cnpj', e.target.value)}
                                                                placeholder="00.000.000/0000-00"
                                                            />
                                                        </div>
                                                        <div className="space-y-2">
                                                            <Label>Data de Início</Label>
                                                            <Input
                                                                value={vinculo.data_inicio}
                                                                onChange={(e) => updateVinculo(index, 'data_inicio', e.target.value)}
                                                                placeholder="dd/mm/aaaa"
                                                            />
                                                        </div>
                                                        <div className="space-y-2">
                                                            <Label>Data de Fim</Label>
                                                            <Input
                                                                value={vinculo.data_fim}
                                                                onChange={(e) => updateVinculo(index, 'data_fim', e.target.value)}
                                                                placeholder="dd/mm/aaaa ou deixe em branco se ativo"
                                                            />
                                                        </div>
                                                    </div>
                                                </div>
                                            ))}
                                        </div>
                                    )}
                                </CardContent>
                            </Card>

                            {/* Submit Button */}
                            <div className="flex justify-end">
                                <Button type="submit" disabled={processing} className="min-w-32">
                                    {processing ? (
                                        <>
                                            <Loader2 className="h-4 w-4 animate-spin" />
                                            Criando...
                                        </>
                                    ) : (
                                        <>
                                            <CheckCircle className="h-4 w-4" />
                                            Criar Caso
                                        </>
                                    )}
                                </Button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}