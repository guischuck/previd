import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
import { Label } from '@/components/ui/label';
import { Progress } from '@/components/ui/progress';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router, useForm } from '@inertiajs/react';
import { AlertCircle, ArrowLeft, Check, Circle, Clock, Download, Edit, FileText, Upload, Users, X } from 'lucide-react';
import { useEffect, useRef, useState } from 'react';

interface EmploymentRelationship {
    id: number;
    employer_name: string;
    employer_cnpj: string;
    start_date: string;
    end_date: string | null;
    salary: number | null;
    is_active: boolean;
    notes: string;
    collection_attempts?: Array<{
        id: number;
        tentativa_num: number;
        endereco: string;
        rastreamento: string;
        data_envio: string;
        retorno: string;
        email: string;
        telefone: string;
    }>;
}

interface Case {
    id: number;
    case_number: string;
    client_name: string;
    client_cpf: string;
    benefit_type: string | null;
    status: string;
    description: string | null;
    notes: string | null;
    tasks?: Array<{
        id: number;
        title: string;
        description: string | null;
        status: string;
        priority: string;
        due_date: string | null;
        assigned_to: number | null;
        required_documents: string[] | null;
        order: number;
    }>;
    collection_progress?: {
        percentage: number;
        completed: number;
        total: number;
        status: string;
    };
    created_at: string;
    updated_at: string;
    created_by?: {
        id: number;
        name: string;
    };
    employment_relationships: EmploymentRelationship[];
}

interface ShowProps {
    case: Case;
    users: Array<{ id: number; name: string }>;
    benefitTypes: Record<string, string>;
}

interface Task {
    id: string;
    title: string;
    description: string;
    completed: boolean;
    priority: 'high' | 'medium' | 'low';
    required_documents?: string[];
    order?: number;
    status?: string;
    due_date?: string;
    assigned_to?: number;
}

interface Document {
    id: number;
    name: string;
    type: string;
    file_name: string;
    file_size: number;
    mime_type: string;
    created_at: string;
    uploaded_by: {
        id: number;
        name: string;
    };
}

// Função removida - agora as tarefas vêm do banco de dados

export default function Show({ case: case_, users, benefitTypes }: ShowProps) {
    const [tasks, setTasks] = useState<Task[]>(() => {
        // Carrega as tarefas do workflow vindas do banco de dados
        const caseTasks = (case_ as any).tasks;
        if (caseTasks && Array.isArray(caseTasks)) {
            return caseTasks.map((task: any) => ({
                id: task.id.toString(),
                title: task.title,
                description: task.description || '',
                completed: task.status === 'completed',
                priority: task.priority as 'high' | 'medium' | 'low',
                required_documents: task.required_documents || [],
                order: task.order || 0,
                status: task.status,
                due_date: task.due_date,
                assigned_to: task.assigned_to,
            }));
        }
        return [];
    });

    const [documents, setDocuments] = useState<Document[]>([]);
    const [uploading, setUploading] = useState(false);
    const [dragActive, setDragActive] = useState(false);
    const [savingNotes, setSavingNotes] = useState(false);
    const [notesSaved, setNotesSaved] = useState(false);
    const [loadingTasks, setLoadingTasks] = useState(false);
    const fileInputRef = useRef<HTMLInputElement>(null);

    const { data, setData, patch, processing } = useForm({
        notes: case_.notes || '',
        benefit_type: case_.benefit_type || '',
    });

    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: 'Dashboard',
            href: '/dashboard',
        },
        {
            title: 'Casos',
            href: '/cases',
        },
        {
            title: case_.case_number,
            href: `/cases/${case_.id}`,
        },
    ];

    const getStatusColor = (status: string) => {
        switch (status) {
            case 'pending':
                return 'bg-yellow-100 text-yellow-800';
            case 'analysis':
                return 'bg-blue-100 text-blue-800';
            case 'completed':
                return 'bg-green-100 text-green-800';
            case 'requirement':
                return 'bg-orange-100 text-orange-800';
            case 'rejected':
                return 'bg-red-100 text-red-800';
            default:
                return 'bg-gray-100 text-gray-800';
        }
    };

    const formatDate = (dateString: string | null) => {
        if (!dateString) return 'Não informado';
        return new Date(dateString).toLocaleDateString('pt-BR');
    };

    // Carregar documentos do caso e tarefas
    useEffect(() => {
        loadDocuments();
        loadTasks();
    }, []);

    // Recarregar tarefas quando o tipo de benefício mudar
    useEffect(() => {
        if (data.benefit_type) {
            loadTasks();
        }
    }, [data.benefit_type]);

    const loadDocuments = async () => {
        try {
            const response = await fetch(`/api/cases/${case_.id}/documents`);
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const data = await response.json();
            
            if (data.success && data.documents) {
                setDocuments(data.documents);
            } else {
                console.error('Erro na resposta da API:', data.error || 'Resposta inválida');
                setDocuments([]);
            }
        } catch (error) {
            console.error('Erro ao carregar documentos:', error);
            // Em caso de erro, define uma lista vazia para não quebrar a interface
            setDocuments([]);
        }
    };

    const loadTasks = async () => {
        setLoadingTasks(true);
        try {
            const response = await fetch(`/api/cases/${case_.id}/tasks`, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'include'
            });
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const result = await response.json();
            
            if (result.success && result.tasks) {
                const formattedTasks = result.tasks.map((task: any) => ({
                    id: task.id.toString(),
                    title: task.title,
                    description: task.description || '',
                    completed: task.status === 'completed',
                    priority: task.priority as 'high' | 'medium' | 'low',
                    required_documents: task.required_documents || [],
                    order: task.order || 0,
                    status: task.status,
                    due_date: task.due_date,
                    assigned_to: task.assigned_to,
                }));
                
                setTasks(formattedTasks);
                console.log(`✅ ${formattedTasks.length} tarefas carregadas com sucesso`);
            } else {
                console.error('Erro na resposta da API de tarefas:', result.error || 'Resposta inválida');
                setTasks([]);
            }
        } catch (error) {
            console.error('Erro ao carregar tarefas:', error);
            setTasks([]);
        } finally {
            setLoadingTasks(false);
        }
    };

    const toggleTask = async (taskId: string) => {
        try {
            const task = tasks.find((t) => t.id === taskId);
            if (!task) return;

            const newStatus = task.completed ? 'pending' : 'completed';

            const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            const response = await fetch(`/api/tasks/${taskId}`, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': token || '',
                },
                body: JSON.stringify({
                    status: newStatus,
                    completed_at: newStatus === 'completed' ? new Date().toISOString() : null,
                }),
            });

            if (response.ok) {
                // Atualiza o estado local
                const updatedTasks = tasks.map((t) => (t.id === taskId ? { ...t, completed: !t.completed, status: newStatus } : t));
                setTasks(updatedTasks);
            }
        } catch (error) {
            console.error('Erro ao atualizar tarefa:', error);
        }
    };

    const saveWorkflowTasks = async (updatedTasks: Task[]) => {
        try {
            const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            await fetch(`/api/cases/${case_.id}/workflow-tasks`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': token || '',
                },
                body: JSON.stringify({ workflow_tasks: updatedTasks }),
            });
        } catch (error) {
            console.error('Erro ao salvar tarefas do workflow:', error);
        }
    };

    const saveNotes = async () => {
        setSavingNotes(true);
        try {
            const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            const response = await fetch(`/api/cases/${case_.id}/notes`, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': token || '',
                },
                body: JSON.stringify({ notes: data.notes }),
            });

            if (response.ok) {
                const result = await response.json();
                // Mostrar feedback de sucesso
                setNotesSaved(true);
                setTimeout(() => setNotesSaved(false), 3000); // Remove após 3 segundos
                console.log(result.message);
            } else {
                console.error('Erro ao salvar anotações');
                alert('Erro ao salvar anotações. Tente novamente.');
            }
        } catch (error) {
            console.error('Erro ao salvar anotações:', error);
        } finally {
            setSavingNotes(false);
        }
    };

    const handleDrag = (e: React.DragEvent) => {
        e.preventDefault();
        e.stopPropagation();
        if (e.type === 'dragenter' || e.type === 'dragover') {
            setDragActive(true);
        } else if (e.type === 'dragleave') {
            setDragActive(false);
        }
    };

    const handleDrop = (e: React.DragEvent) => {
        e.preventDefault();
        e.stopPropagation();
        setDragActive(false);

        if (e.dataTransfer.files && e.dataTransfer.files[0]) {
            handleFiles(e.dataTransfer.files);
        }
    };

    const handleChange = (e: React.ChangeEvent<HTMLInputElement>) => {
        e.preventDefault();
        if (e.target.files && e.target.files[0]) {
            handleFiles(e.target.files);
        }
    };

    const handleFiles = async (files: FileList) => {
        setUploading(true);

        try {
            const formData = new FormData();
            Array.from(files).forEach((file) => {
                formData.append('files[]', file);
            });
            formData.append('type', 'other');
            formData.append('notes', 'Enviado via interface do caso');

            const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            const response = await fetch(`/api/cases/${case_.id}/upload-documents`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': token || '',
                },
                body: formData,
            });

            if (response.ok) {
                const data = await response.json();
                loadDocuments(); // Recarregar lista de documentos
                router.visit(`/cases/${case_.id}`, {
                    preserveState: true,
                    preserveScroll: true,
                    only: ['flash'],
                });
            } else {
                console.error('Erro no upload');
            }
        } catch (error) {
            console.error('Erro ao fazer upload:', error);
        } finally {
            setUploading(false);
        }
    };

    const deleteDocument = async (documentId: number) => {
        if (confirm('Tem certeza que deseja excluir este documento?')) {
            try {
                const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                const response = await fetch(`/api/documents/${documentId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': token || '',
                    },
                });

                if (response.ok) {
                    loadDocuments(); // Recarregar lista de documentos
                }
            } catch (error) {
                console.error('Erro ao deletar documento:', error);
            }
        }
    };

    const formatFileSize = (bytes: number) => {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    };

    // Estado para o progresso da coleta (iniciado com os dados do caso)
    const [collectionProgress, setCollectionProgress] = useState(case_.collection_progress || {
        percentage: 0,
        completed: 0,
        total: case_.employment_relationships.length,
        status: 'Sem vínculos',
    });

    // Estado para o status do caso
    const [caseStatus, setCaseStatus] = useState(case_.status);

    // Listener para atualizações de progresso em tempo real
    useEffect(() => {
        const handleProgressUpdate = (event: CustomEvent) => {
            const { caseId, progress, status } = event.detail;
            
            // Só atualiza se for o caso atual
            if (caseId === case_.id) {
                console.log('Atualizando progresso em tempo real:', progress);
                setCollectionProgress(progress);
                setCaseStatus(status);
            }
        };

        // Adicionar listener para eventos de atualização de progresso
        window.addEventListener('caseProgressUpdated' as any, handleProgressUpdate);

        // Verificar se há dados atualizados no localStorage
        const storedProgress = localStorage.getItem(`case_progress_${case_.id}`);
        if (storedProgress) {
            try {
                const parsedProgress = JSON.parse(storedProgress);
                const timeDiff = Date.now() - parsedProgress.timestamp;
                
                // Se os dados foram atualizados nos últimos 5 minutos, usar eles
                if (timeDiff < 5 * 60 * 1000) {
                    setCollectionProgress(parsedProgress.progress);
                    setCaseStatus(parsedProgress.status);
                }
            } catch (error) {
                console.error('Erro ao parsear dados do localStorage:', error);
            }
        }

        // Carregar tarefas automaticamente se houver um benefit_type definido
        if (case_.benefit_type && tasks.length === 0) {
            console.log('Carregando tarefas automaticamente para benefit_type:', case_.benefit_type);
            loadTasks();
        }

        // Cleanup do listener
        return () => {
            window.removeEventListener('caseProgressUpdated' as any, handleProgressUpdate);
        };
    }, [case_.id, case_.benefit_type]);

    const getPriorityIcon = (priority: string) => {
        switch (priority) {
            case 'high':
                return <AlertCircle className="h-4 w-4 text-red-500" />;
            case 'medium':
                return <Clock className="h-4 w-4 text-yellow-500" />;
            case 'low':
                return <Circle className="h-4 w-4 text-gray-400" />;
            default:
                return <Circle className="h-4 w-4 text-gray-400" />;
        }
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={`Caso ${case_.case_number} - Sistema Jurídico`} />
            <div className="flex h-full flex-1 flex-col gap-6 p-6">
                {/* Header */}
                <div className="flex items-center justify-between">
                    <div className="flex items-center space-x-4">
                        <Link href="/cases">
                            <Button variant="outline" size="sm">
                                <ArrowLeft className="mr-2 h-4 w-4" />
                                Voltar aos Casos
                            </Button>
                        </Link>
                    </div>
                    <div className="flex items-center space-x-2">
                        <Link href={`/cases/${case_.id}/vinculos`}>
                            <Button variant="outline">
                                <Users className="mr-2 h-4 w-4" />
                                Vínculos ({case_.employment_relationships.length})
                            </Button>
                        </Link>
                        <Link href={`/cases/${case_.id}/edit`}>
                            <Button>
                                <Edit className="mr-2 h-4 w-4" />
                                Editar Caso
                            </Button>
                        </Link>
                    </div>
                </div>

                {/* Case Info */}
                <div className="grid grid-cols-1 gap-6 lg:grid-cols-3">
                    {/* Main Info */}
                    <div className="space-y-6 lg:col-span-2">
                        <Card>
                            <CardHeader>
                                <CardTitle className="flex items-center space-x-2">
                                    <FileText className="h-5 w-5" />
                                    <span>Informações do Caso</span>
                                </CardTitle>
                            </CardHeader>
                            <CardContent className="space-y-4">
                                <div className="grid grid-cols-1 gap-4 md:grid-cols-2">
                                    <div>
                                        <p className="text-sm font-medium text-gray-600">Número do Caso</p>
                                        <p className="font-mono text-lg">{case_.case_number}</p>
                                    </div>
                                    <div>
                                        <p className="text-sm font-medium text-gray-600">Status</p>
                                        <Badge className={getStatusColor(caseStatus)}>
                                            {caseStatus === 'pendente'
                                                ? 'Pendente'
                                                : caseStatus === 'em_coleta'
                                                  ? 'Em Coleta'
                                                  : caseStatus === 'aguarda_peticao'
                                                    ? 'Aguarda Petição'
                                                    : caseStatus === 'protocolado'
                                                      ? 'Protocolado'
                                                      : caseStatus === 'concluido'
                                                        ? 'Concluído'
                                                        : caseStatus === 'rejeitado'
                                                          ? 'Rejeitado'
                                                          : caseStatus}
                                        </Badge>
                                    </div>
                                    <div>
                                        <p className="text-sm font-medium text-gray-600">Nome do Cliente</p>
                                        <p className="text-lg">{case_.client_name}</p>
                                    </div>
                                    <div>
                                        <p className="text-sm font-medium text-gray-600">CPF</p>
                                        <p className="text-lg">{case_.client_cpf}</p>
                                    </div>
                                    <div>
                                        <p className="text-sm font-medium text-gray-600">Tipo de Benefício</p>
                                        <p className="text-lg">
                                            {case_.benefit_type ? benefitTypes[case_.benefit_type] || case_.benefit_type : 'Não informado'}
                                        </p>
                                    </div>
                                    <div>
                                        <p className="text-sm font-medium text-gray-600">Criado em</p>
                                        <p className="text-lg">{formatDate(case_.created_at)}</p>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>

                        {/* Workflow de Tarefas */}
                        <Card>
                            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                                <div>
                                    <CardTitle>Workflow de Tarefas</CardTitle>
                                    <CardDescription>Tarefas recomendadas para o tipo de benefício selecionado</CardDescription>
                                </div>
                                <Button
                                    variant="outline"
                                    size="sm"
                                    onClick={loadTasks}
                                    disabled={loadingTasks}
                                    className="shrink-0"
                                >
                                    {loadingTasks ? '⏳ Carregando...' : '🔄 Recarregar'}
                                </Button>
                            </CardHeader>
                            <CardContent>
                                <div className="space-y-4">
                                    {/* Dropdown para selecionar benefício */}
                                    <div>
                                        <Label htmlFor="benefit_type">Tipo de Benefício</Label>
                                        <Select
                                            value={data.benefit_type || ''}
                                            onValueChange={async (value) => {
                                                setData('benefit_type', value || '');
                                                
                                                if (!value) return;
                                                
                                                console.log('Tipo de benefício selecionado:', value);
                                                setLoadingTasks(true);
                                                
                                                // Salva o tipo de benefício - o backend criará/recriará as tarefas automaticamente
                                                try {
                                                    await patch(`/cases/${case_.id}`, {
                                                        preserveScroll: true,
                                                        onSuccess: () => {
                                                            console.log('Tipo de benefício salvo, carregando tarefas...');
                                                            // Recarrega as tarefas após salvar
                                                            setTimeout(() => {
                                                                loadTasks();
                                                            }, 1000); // Aguarda 1s para garantir que o backend processou
                                                        },
                                                        onError: (errors) => {
                                                            console.error('Erro ao atualizar caso:', errors);
                                                            setLoadingTasks(false);
                                                        }
                                                    });
                                                } catch (error) {
                                                    console.error('Erro ao salvar tipo de benefício:', error);
                                                    setLoadingTasks(false);
                                                }
                                            }}
                                        >
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
                                    </div>

                                    {/* Indicador de carregamento */}
                                    {loadingTasks && (
                                        <div className="py-8 text-center text-blue-600">
                                            <div className="inline-flex items-center space-x-2">
                                                <div className="h-5 w-5 animate-spin rounded-full border-2 border-blue-600 border-t-transparent"></div>
                                                <span>Carregando tarefas...</span>
                                            </div>
                                        </div>
                                    )}

                                    {/* Lista de tarefas */}
                                    {data.benefit_type && tasks.length > 0 && !loadingTasks && (
                                        <div className="space-y-3">
                                            {tasks.map((task) => (
                                                <div key={task.id} className="flex items-start space-x-3 rounded-lg border bg-card p-4 shadow-sm">
                                                    <Checkbox checked={task.completed} onCheckedChange={() => toggleTask(task.id)} />
                                                    <div className="flex-1">
                                                        <div className="flex items-center space-x-2">
                                                            {getPriorityIcon(task.priority)}
                                                            <h4 className={`font-medium ${task.completed ? 'text-muted-foreground line-through' : ''}`}>
                                                                {task.title}
                                                            </h4>
                                                        </div>
                                                        <p className={`mt-1 text-sm text-muted-foreground ${task.completed ? 'line-through' : ''}`}>
                                                            {task.description}
                                                        </p>
                                                        {task.required_documents && task.required_documents.length > 0 && (
                                                            <div className="mt-2">
                                                                <p className="mb-1 text-xs font-medium text-muted-foreground">Documentos necessários:</p>
                                                                <div className="flex flex-wrap gap-1">
                                                                    {task.required_documents.map((doc, index) => (
                                                                        <Badge key={index} variant="secondary" className="flex items-center">
                                                                            {doc}
                                                                        </Badge>
                                                                    ))}
                                                                </div>
                                                            </div>
                                                        )}
                                                    </div>
                                                </div>
                                            ))}
                                        </div>
                                    )}

                                    {data.benefit_type && tasks.length === 0 && !loadingTasks && (
                                        <div className="py-8 text-center text-gray-500">
                                            <p>Nenhuma tarefa de workflow encontrada para este tipo de benefício.</p>
                                            <p className="mt-1 text-sm">
                                                Verifique se existe um template ativo para "{benefitTypes[data.benefit_type] || data.benefit_type}".
                                            </p>
                                        </div>
                                    )}
                                </div>
                            </CardContent>
                        </Card>

                        {/* Anotações */}
                        <Card>
                            <CardHeader>
                                <CardTitle>Anotações sobre o Caso</CardTitle>
                                <CardDescription>Adicione observações e notas importantes sobre o caso</CardDescription>
                            </CardHeader>
                            <CardContent>
                                <div className="space-y-4">
                                    <Textarea
                                        value={data.notes}
                                        onChange={(e) => setData('notes', e.target.value)}
                                        placeholder="Digite suas anotações sobre o caso..."
                                        rows={4}
                                    />
                                    <Button
                                        onClick={saveNotes}
                                        disabled={savingNotes}
                                        variant={notesSaved ? 'default' : 'default'}
                                        className={notesSaved ? 'bg-green-600 text-white hover:bg-green-700' : ''}
                                    >
                                        {savingNotes ? (
                                            'Salvando...'
                                        ) : notesSaved ? (
                                            <>
                                                <Check className="mr-2 h-4 w-4" />
                                                Salvo!
                                            </>
                                        ) : (
                                            'Salvar Anotações'
                                        )}
                                    </Button>
                                </div>
                            </CardContent>
                        </Card>

                        {case_.description && (
                            <Card>
                                <CardHeader>
                                    <CardTitle>Descrição</CardTitle>
                                </CardHeader>
                                <CardContent>
                                    <p className="whitespace-pre-wrap">{case_.description}</p>
                                </CardContent>
                            </Card>
                        )}
                    </div>

                    {/* Sidebar */}
                    <div className="space-y-6">
                        {/* Progresso da Coleta */}
                        <Card>
                            <CardHeader>
                                <CardTitle>Progresso da Coleta</CardTitle>
                                <CardDescription>Vínculos empregatícios coletados</CardDescription>
                            </CardHeader>
                            <CardContent>
                                <div className="space-y-4">
                                    <div className="flex items-center justify-between">
                                        <span className="text-sm font-medium">Progresso</span>
                                        <span className="text-sm text-gray-600">
                                            {collectionProgress.completed}/{collectionProgress.total}
                                        </span>
                                    </div>
                                    <Progress value={collectionProgress.percentage} className="w-full" />
                                    <div className="text-center">
                                        <p className="text-2xl font-bold text-blue-600">{Math.round(collectionProgress.percentage)}%</p>
                                        <p className="text-sm text-gray-600">{collectionProgress.status}</p>
                                    </div>
                                    <Link href={`/cases/${case_.id}/vinculos`}>
                                        <Button variant="outline" size="sm" className="w-full">
                                            Gerenciar Vínculos
                                        </Button>
                                    </Link>
                                </div>
                            </CardContent>
                        </Card>

                        {/* Upload de Documentos */}
                        <Card>
                            <CardHeader>
                                <CardTitle>Documentos ({documents.length})</CardTitle>
                                <CardDescription>Faça upload de documentos relacionados ao caso</CardDescription>
                            </CardHeader>
                            <CardContent>
                                <div className="space-y-4">
                                    {/* Área de Upload */}
                                    <div
                                        className={`rounded-lg border-2 border-dashed p-6 text-center transition-colors ${
                                            dragActive ? 'border-blue-400 bg-blue-50' : 'border-gray-300 hover:border-gray-400'
                                        }`}
                                        onDragEnter={handleDrag}
                                        onDragLeave={handleDrag}
                                        onDragOver={handleDrag}
                                        onDrop={handleDrop}
                                    >
                                        <input
                                            ref={fileInputRef}
                                            type="file"
                                            multiple
                                            onChange={handleChange}
                                            className="hidden"
                                            accept=".pdf,.doc,.docx,.jpg,.jpeg,.png"
                                        />
                                        <Upload className={`mx-auto mb-2 h-8 w-8 ${dragActive ? 'text-blue-500' : 'text-gray-400'}`} />
                                        <p className="mb-2 text-sm text-gray-600">
                                            {dragActive ? 'Solte os arquivos aqui' : 'Arraste e solte arquivos aqui ou clique para selecionar'}
                                        </p>
                                        <Button variant="outline" onClick={() => fileInputRef.current?.click()} disabled={uploading}>
                                            {uploading ? 'Enviando...' : 'Selecionar Arquivos'}
                                        </Button>
                                    </div>

                                    {/* Lista de Documentos */}
                                    {documents.length > 0 && (
                                        <div className="space-y-2">
                                            <h4 className="text-sm font-medium">Documentos Enviados:</h4>
                                            {documents.map((doc) => (
                                                <div key={doc.id} className="flex items-center justify-between rounded border p-2">
                                                    <div className="flex-1">
                                                        <p className="text-sm font-medium">{doc.file_name}</p>
                                                        <p className="text-xs text-gray-500">
                                                            {formatFileSize(doc.file_size)} • {new Date(doc.created_at).toLocaleDateString('pt-BR')}
                                                        </p>
                                                    </div>
                                                    <div className="flex items-center space-x-1">
                                                        <Button
                                                            variant="ghost"
                                                            size="sm"
                                                            onClick={() => window.open(`/documents/${doc.id}/download`, '_blank')}
                                                        >
                                                            <Download className="h-4 w-4" />
                                                        </Button>
                                                        <Button
                                                            variant="ghost"
                                                            size="sm"
                                                            onClick={() => deleteDocument(doc.id)}
                                                            className="text-red-500 hover:text-red-700"
                                                        >
                                                            <X className="h-4 w-4" />
                                                        </Button>
                                                    </div>
                                                </div>
                                            ))}
                                        </div>
                                    )}

                                    <Link href={`/documents/case/${case_.id}`}>
                                        <Button variant="outline" className="w-full">
                                            Ver Todos os Documentos
                                        </Button>
                                    </Link>
                                </div>
                            </CardContent>
                        </Card>
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}
