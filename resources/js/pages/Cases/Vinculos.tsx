import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/react';
import { ArrowLeft, Building, ChevronDown, ChevronUp, Copy, FileText, Plus } from 'lucide-react';
import { useEffect, useState } from 'react';
import axios from 'axios';

interface EmploymentRelationship {
    id: number;
    employer_name: string;
    employer_cnpj: string;
    start_date: string;
    end_date: string | null;
    salary: number | null;
    is_active: boolean;
    notes: string;
    created_at: string;
    cargo?: string;
    documentos?: string;
    observacoes?: string;
    status_empresa?: string;
}

interface Case {
    id: number;
    case_number: string;
    client_name: string;
    client_cpf: string;
    employment_relationships: EmploymentRelationship[];
}

interface VinculosProps {
    case: Case;
}

interface EditableDateProps {
    value: string | null;
    onChange: (val: string) => void;
}

interface EditableTextProps {
    value: string;
    onChange: (val: string) => void;
    placeholder?: string;
}

interface NewVinculoForm {
    employer_name: string;
    employer_cnpj: string;
    start_date: string;
    end_date: string;
    cargo: string;
    documentos: string;
    observacoes: string;
    status_empresa: string;
}

// Função para formatar datas no formato DD/MM/YYYY
const formatDate = (dateString: string | null) => {
    if (!dateString) return 'Em andamento';
    // Extrai apenas a parte da data (YYYY-MM-DD)
    const onlyDate = dateString.split('T')[0];
    const parts = onlyDate.split('-');
    if (parts.length !== 3) return dateString;
    return `${parts[2]}/${parts[1]}/${parts[0]}`;
};

// Função para converter de DD/MM/AAAA para YYYY-MM-DD
function parseDateBRtoISO(dateBR: string): string | null {
    const parts = dateBR.split('/');
    if (parts.length !== 3) return null;
    return `${parts[2]}-${parts[1]}-${parts[0]}`;
}

function EditableDate({ value, onChange }: EditableDateProps) {
    const [editing, setEditing] = useState(false);
    // Exibe sempre no formato DD/MM/AAAA
    const [val, setVal] = useState(value ? formatDate(value) : '');
    const [saving, setSaving] = useState(false);

    useEffect(() => {
        setVal(value ? formatDate(value) : '');
    }, [value]);

    const handleSave = async () => {
        if (val !== (value ? formatDate(value) : '')) {
            setSaving(true);
            // Converte para ISO antes de salvar
            const isoDate = parseDateBRtoISO(val);
            if (isoDate) {
                await onChange(isoDate);
            }
            setSaving(false);
        }
        setEditing(false);
    };

    return editing ? (
        <input
            type="text"
            value={val}
            placeholder="DD/MM/AAAA"
            onChange={(e) => setVal(e.target.value)}
            onBlur={handleSave}
            onKeyDown={(e) => e.key === 'Enter' && handleSave()}
            className={`rounded border px-2 py-1 ${saving ? 'opacity-50' : ''}`}
            disabled={saving}
            autoFocus
        />
    ) : (
        <span onClick={() => setEditing(true)} className="cursor-pointer text-blue-600 underline hover:text-blue-800 dark:text-blue-300">
            {value ? formatDate(value) : '---'}
        </span>
    );
}

function EditableText({ value, onChange, placeholder }: EditableTextProps) {
    const [editing, setEditing] = useState(false);
    const [val, setVal] = useState(value);
    const [saving, setSaving] = useState(false);

    useEffect(() => {
        setVal(value);
    }, [value]);

    const handleSave = async () => {
        if (val !== value) {
            setSaving(true);
            await onChange(val);
            setSaving(false);
        }
        setEditing(false);
    };

    return editing ? (
        <input
            type="text"
            value={val}
            placeholder={placeholder}
            onChange={(e) => setVal(e.target.value)}
            onBlur={handleSave}
            onKeyDown={(e) => e.key === 'Enter' && handleSave()}
            className={`w-full rounded border px-2 py-1 ${saving ? 'opacity-50' : ''}`}
            disabled={saving}
            autoFocus
        />
    ) : (
        <span onClick={() => setEditing(true)} className="cursor-pointer text-blue-600 underline hover:text-blue-800 dark:text-blue-300">
            {value || <span className="text-muted-foreground">{placeholder}</span>}
        </span>
    );
}

export default function Vinculos({ case: case_ }: VinculosProps) {
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
        {
            title: 'Vínculos Empregatícios',
            href: `/cases/${case_.id}/vinculos`,
        },
    ];

    const formatSalary = (salary: number | null) => {
        if (!salary) return 'Não informado';
        return new Intl.NumberFormat('pt-BR', {
            style: 'currency',
            currency: 'BRL',
        }).format(salary);
    };

    const formatCNPJ = (cnpj: string) => {
        if (!cnpj) return 'Não informado';
        // Remove caracteres não numéricos
        const cleanCNPJ = cnpj.replace(/\D/g, '');

        // Formata CNPJ: XX.XXX.XXX/XXXX-XX
        if (cleanCNPJ.length === 14) {
            return cleanCNPJ.replace(/^(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})$/, '$1.$2.$3/$4-$5');
        }

        return cnpj; // Retorna original se não conseguir formatar
    };

    const [employmentRelationships, setEmploymentRelationships] = useState(case_.employment_relationships);
    const [expanded, setExpanded] = useState<number | null>(null);
    const [modalOpen, setModalOpen] = useState(false);
    const [modalVinculo, setModalVinculo] = useState<EmploymentRelationship | null>(null);
    const [newVinculoModalOpen, setNewVinculoModalOpen] = useState(false);
    const [newVinculoForm, setNewVinculoForm] = useState<NewVinculoForm>({
        employer_name: '',
        employer_cnpj: '',
        start_date: '',
        end_date: '',
        cargo: '',
        documentos: '',
        observacoes: '',
        status_empresa: ''
    });
    const [tentativasData, setTentativasData] = useState<{ [key: string]: any }>({});

    // Carregar dados das tentativas existentes
    useEffect(() => {
        const loadTentativasData = async () => {
            try {
                for (const relationship of employmentRelationships) {
                    const response = await fetch(`/api/employment-relationships/${relationship.id}/tentativas`);
                    if (response.ok) {
                        const tentativas = await response.json();
                        tentativas.forEach((tentativa: any) => {
                            const fields = ['endereco', 'rastreamento', 'data_envio', 'retorno', 'email', 'telefone'];
                            fields.forEach((field) => {
                                if (tentativa[field]) {
                                    const key = `${relationship.id}_${tentativa.tentativa_num}_${field}`;
                                    setTentativasData((prev) => ({
                                        ...prev,
                                        [key]: tentativa[field],
                                    }));
                                }
                            });
                        });
                    }
                }
            } catch (error) {
                console.error('Erro ao carregar tentativas:', error);
            }
        };

        loadTentativasData();
    }, [employmentRelationships]);

    const handleDateChange = (idx: number, field: 'start_date' | 'end_date', value: string) => {
        const updatedRelationships = [...employmentRelationships];
        updatedRelationships[idx] = {
            ...updatedRelationships[idx],
            [field]: value,
        };
        setEmploymentRelationships(updatedRelationships);
    };

    const handleFieldChange = (idx: number, field: 'cargo' | 'documentos' | 'observacoes' | 'status_empresa', value: string) => {
        const updatedRelationships = [...employmentRelationships];
        updatedRelationships[idx] = {
            ...updatedRelationships[idx],
            [field]: value,
        };
        setEmploymentRelationships(updatedRelationships);
    };

    const toggleExpand = (idx: number) => {
        setExpanded(expanded === idx ? null : idx);
    };

    const toggleStatus = async (idx: number) => {
        const updated = [...employmentRelationships];
        const newStatus = !updated[idx].is_active;
        updated[idx] = {
            ...updated[idx],
            is_active: newStatus,
        };
        setEmploymentRelationships(updated);

        // Salvar no backend
        try {
            console.log('Enviando toggle status:', {
                id: updated[idx].id,
                is_active: newStatus,
            });

            const response = await fetch(`/api/employment-relationships/${updated[idx].id}`, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                },
                body: JSON.stringify({ is_active: newStatus }),
            });

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const result = await response.json();
            console.log('Status alterado com sucesso:', result);
            
            // Se recebemos informações de progresso do caso, emitir evento para atualizar outras páginas
            if (result.case_progress && result.case_id) {
                // Emitir evento customizado para notificar mudanças no progresso
                window.dispatchEvent(new CustomEvent('caseProgressUpdated', {
                    detail: {
                        caseId: result.case_id,
                        progress: result.case_progress,
                        status: result.case_status
                    }
                }));
                
                // Também armazenar no localStorage para persistência entre páginas
                localStorage.setItem(`case_progress_${result.case_id}`, JSON.stringify({
                    progress: result.case_progress,
                    status: result.case_status,
                    timestamp: Date.now()
                }));
                
                console.log('Progresso do caso atualizado:', result.case_progress);
            }
        } catch (error) {
            console.error('Erro ao salvar status:', error);
            alert('Erro ao salvar status. Tente novamente.');
            // Reverter mudança local em caso de erro
            const reverted = [...employmentRelationships];
            reverted[idx].is_active = !newStatus;
            setEmploymentRelationships(reverted);
        }
    };

    const removeVinculo = async (idx: number) => {
        try {
            const vinculo = employmentRelationships[idx];
            if (!vinculo || !vinculo.id) {
                console.error('Vínculo inválido ou sem ID');
                return;
            }
            
            console.log('Removendo vínculo:', vinculo);
            
            const response = await axios.delete(`/api/employment-relationships/${vinculo.id}`);
            console.log('Resposta da API ao remover vínculo:', response.data);
            
            if (response.data.success) {
                const updated = [...employmentRelationships];
                updated.splice(idx, 1);
                setEmploymentRelationships(updated);
                
                // Atualizar status do caso se necessário
                if (response.data.case_status) {
                    setCase(prev => ({
                        ...prev,
                        status: response.data.case_status,
                        collection_progress: response.data.case_progress
                    }));
                }
            } else {
                console.error('Erro ao remover vínculo:', response.data);
            }
        } catch (error) {
            console.error('Erro ao remover vínculo:', error);
        }
    };

    const openModal = (vinculo: EmploymentRelationship) => {
        setModalVinculo(vinculo);
        setModalOpen(true);
    };
    const closeModal = () => setModalOpen(false);

    const handleNewVinculoSubmit = async (e: React.FormEvent) => {
        e.preventDefault();
        try {
            console.log('Enviando novo vínculo:', newVinculoForm);
            const newRelationship = {
                case_id: case_.id,
                employer_name: newVinculoForm.employer_name,
                employer_cnpj: newVinculoForm.employer_cnpj,
                start_date: newVinculoForm.start_date,
                end_date: newVinculoForm.end_date,
                cargo: newVinculoForm.cargo,
                documentos: newVinculoForm.documentos,
                observacoes: newVinculoForm.observacoes,
                status_empresa: newVinculoForm.status_empresa,
                is_active: true
            };
            
            const response = await axios.post('/api/employment-relationships', newRelationship, {
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                }
            });

            if (response.data.data) {
                setEmploymentRelationships(prev => [...prev, response.data.data]);
                setNewVinculoModalOpen(false);
                setNewVinculoForm({
                    employer_name: '',
                    employer_cnpj: '',
                    start_date: '',
                    end_date: '',
                    cargo: '',
                    documentos: '',
                    observacoes: '',
                    status_empresa: ''
                });
            }
        } catch (error) {
            console.error('Erro ao criar vínculo:', error);
            console.error('Dados enviados:', newVinculoForm);
            if (axios.isAxiosError(error)) {
                console.error('Resposta do servidor:', error.response?.data);
            }
            alert('Erro ao criar novo vínculo. Tente novamente.');
        }
    };

    const saveField = async (idx: number, field: string, value: any) => {
        console.log('Salvando campo:', { field, value });
        const relationship = employmentRelationships[idx];
        try {
            // Atualizar estado local imediatamente
            const updatedRelationships = [...employmentRelationships];
            updatedRelationships[idx] = {
                ...updatedRelationships[idx],
                [field]: value
            };
            setEmploymentRelationships(updatedRelationships);

            // Enviar para o backend
            const response = await axios.patch(
                `/api/employment-relationships/${relationship.id}`,
                { [field]: value },
                {
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                    }
                }
            );

            console.log('Campo salvo com sucesso:', response.data);
        } catch (error) {
            console.error('Erro ao salvar campo:', error);
            console.error('Dados enviados:', { field, value });
            if (axios.isAxiosError(error)) {
                console.error('Resposta do servidor:', error.response?.data);
            }
            alert(`Erro ao salvar ${field}. Tente novamente.`);
        }
    };

    const saveTentativa = async (idx: number, tentativa: number, field: string, value: string) => {
        // Atualizar estado local
        const key = `${employmentRelationships[idx].id}_${tentativa}_${field}`;
        setTentativasData((prev) => ({
            ...prev,
            [key]: value,
        }));

        try {
            console.log('Salvando tentativa:', {
                employmentId: employmentRelationships[idx].id,
                tentativa,
                field,
                value,
                url: `/api/employment-relationships/${employmentRelationships[idx].id}/tentativas/${tentativa}`,
            });

            const response = await fetch(`/api/employment-relationships/${employmentRelationships[idx].id}/tentativas/${tentativa}`, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                },
                body: JSON.stringify({ [field]: value }),
            });

            if (!response.ok) {
                const errorText = await response.text();
                console.error('Erro HTTP:', response.status, errorText);
                throw new Error(`HTTP error! status: ${response.status} - ${errorText}`);
            }

            const result = await response.json();
            console.log('Tentativa salva com sucesso:', result);
        } catch (error) {
            console.error('Erro ao salvar tentativa:', error);
            const errorMessage = error instanceof Error ? error.message : 'Erro desconhecido';
            alert(`Erro ao salvar ${field} da tentativa ${tentativa}. Tente novamente. Erro: ${errorMessage}`);
        }
    };

    const getTentativaValue = (relationshipId: number, tentativa: number, field: string): string => {
        const key = `${relationshipId}_${tentativa}_${field}`;
        return tentativasData[key] || '';
    };

    const updateTentativaValue = (relationshipId: number, tentativa: number, field: string, value: string) => {
        const key = `${relationshipId}_${tentativa}_${field}`;
        setTentativasData((prev) => ({
            ...prev,
            [key]: value,
        }));
    };

    const gerarTextoCarta = (vinculo: EmploymentRelationship) => {
        return `NOTIFICAÇÃO EXTRAJUDICIAL REQUERIMENTO DE PPP E LTCAT

${vinculo.employer_name}
CNPJ: ${formatCNPJ(vinculo.employer_cnpj)}

${case_.client_name}, neste ato representado por seu advogado que este subscreve, vem, por meio deste, solicitar a emissão de PPP (Perfil Profissiográfico Previdenciário) com o respectivo LTCAT (Laudo Técnico das Condições Ambientais de Trabalho), para fins de instrução no processo de aposentadoria em face do Instituto Nacional do Seguro Social - INSS.

Vale salientar que o PPP somente é válido diante do preenchimento de todos os campos, principalmente aos que se referem à exposição dos agentes nocivos, indicação do responsável técnico pelos registros ambientais (Tema 208 - TNU), carimbo e assinatura do responsável da empresa. Ressalta-se, ainda, que o documento deve seguir o modelo estabelecido pela Instrução normativa nº 133/2022 e 128/2022.

Requer também que o PPP venha acompanhado de autorização que identifique o representante legal responsável pela assinatura do laudo ou a cópia do contrato social da empresa, quando o representante legal for o sócio da mesma.

Os documentos deverão ser encaminhados por e-mail para extrajudicial@koetzadvocacia.com.br

DADOS DO REQUERENTE:
Nome: ${case_.client_name}
CPF: ${case_.client_cpf}
Período: ${formatDate(vinculo.start_date)} a ${formatDate(vinculo.end_date)}

Certa de vossa atenção,
aguardo retorno. Atenciosamente,
Eduardo Koetz - OAB/SC 42.934`;
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={`Vínculos - ${case_.case_number} - Sistema Jurídico`} />
            <div className="flex h-full flex-1 flex-col gap-6 p-6">
                {/* Header */}
                <div className="flex items-center justify-between">
                    <div>
                        <div className="flex items-center space-x-4">
                            <Link href={`/cases/${case_.id}`}>
                                <Button variant="outline" size="sm">
                                    <ArrowLeft className="mr-2 h-4 w-4" />
                                    Voltar ao Caso
                                </Button>
                            </Link>
                            <Button variant="default" size="sm" onClick={() => setNewVinculoModalOpen(true)}>
                                <Plus className="mr-2 h-4 w-4" />
                                Novo Vínculo
                            </Button>
                        </div>
                        <h1 className="mt-4 text-3xl font-bold">Vínculos Empregatícios</h1>
                        <p className="text-muted-foreground">
                            Caso: {case_.case_number} - {case_.client_name}
                        </p>
                    </div>
                </div>

                {/* Case Info */}
                <Card>
                    <CardHeader>
                        <CardTitle className="flex items-center space-x-2">
                            <FileText className="h-5 w-5" />
                            <span>Informações do Cliente</span>
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div className="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <div>
                                <p className="text-sm font-medium text-gray-600">Nome do Cliente</p>
                                <p className="text-lg">{case_.client_name}</p>
                            </div>
                            <div>
                                <p className="text-sm font-medium text-gray-600">CPF</p>
                                <p className="text-lg">{case_.client_cpf}</p>
                            </div>
                            <div>
                                <p className="text-sm font-medium text-gray-600">Número do Caso</p>
                                <p className="font-mono text-lg">{case_.case_number}</p>
                            </div>
                            <div>
                                <p className="text-sm font-medium text-gray-600">Total de Vínculos</p>
                                <p className="text-lg font-bold text-blue-600">{case_.employment_relationships.length}</p>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                {/* Employment Relationships Table */}
                <Card>
                    <CardHeader>
                        <CardTitle>Lista de Vínculos Empregatícios</CardTitle>
                        <CardDescription>Edite os dados e eles serão salvos automaticamente</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div className="overflow-x-auto">
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead>Empregador</TableHead>
                                        <TableHead>Status Empresa</TableHead>
                                        <TableHead>Data Início</TableHead>
                                        <TableHead>Data Fim</TableHead>
                                        <TableHead>Cargo</TableHead>
                                        <TableHead>Documentos</TableHead>
                                        <TableHead>Observações</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    {employmentRelationships.map((relationship, idx) => (
                                        <TableRow key={relationship.id}>
                                            <TableCell>{relationship.employer_name}</TableCell>
                                            <TableCell>
                                                <select
                                                    className="flex h-9 w-full rounded-md border border-input bg-background px-3 py-1 text-sm shadow-sm transition-colors file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50"
                                                    value={relationship.status_empresa || ''}
                                                    onChange={(e) => saveField(idx, 'status_empresa', e.target.value)}
                                                >
                                                    <option value="">Selecione...</option>
                                                    <option value="Ativa">Ativa</option>
                                                    <option value="Baixada">Baixada</option>
                                                    <option value="Inapta">Inapta</option>
                                                </select>
                                            </TableCell>
                                            <TableCell>
                                                <EditableDate value={relationship.start_date} onChange={(val) => saveField(idx, 'start_date', val)} />
                                            </TableCell>
                                            <TableCell>
                                                <EditableDate value={relationship.end_date} onChange={(val) => saveField(idx, 'end_date', val)} />
                                            </TableCell>
                                            <TableCell>
                                                <EditableText
                                                    value={relationship.cargo || ''}
                                                    onChange={(val) => saveField(idx, 'cargo', val)}
                                                    placeholder="Cargo"
                                                />
                                            </TableCell>
                                            <TableCell>
                                                <EditableText
                                                    value={relationship.documentos || ''}
                                                    onChange={(val) => saveField(idx, 'documentos', val)}
                                                    placeholder="Documentos"
                                                />
                                            </TableCell>
                                            <TableCell className="relative">
                                                <div className="flex items-center gap-2">
                                                    <EditableText
                                                        value={relationship.observacoes || ''}
                                                        onChange={(val) => saveField(idx, 'observacoes', val)}
                                                        placeholder="Observações"
                                                    />
                                                    <div
                                                        className={`h-3 w-3 rounded-full ${!relationship.is_active ? 'bg-green-500' : 'bg-yellow-500'}`}
                                                        title={!relationship.is_active ? 'Concluído' : 'Pendente'}
                                                    />
                                                </div>
                                            </TableCell>
                                        </TableRow>
                                    ))}
                                </TableBody>
                            </Table>
                        </div>
                    </CardContent>
                </Card>

                {/* Employment Relationships Cards */}
                <Card>
                    <CardHeader>
                        <CardTitle className="flex items-center space-x-2">
                            <Building className="h-5 w-5" />
                            <span>Vínculos Empregatícios</span>
                        </CardTitle>
                        <CardDescription>Lista de todos os vínculos empregatícios extraídos do CNIS</CardDescription>
                    </CardHeader>
                    <CardContent>
                        {case_.employment_relationships.length > 0 ? (
                            <div className="overflow-x-auto">
                                {employmentRelationships.map((relationship, idx) => (
                                    <div key={relationship.id} className="mb-4 rounded-lg border bg-card shadow-sm">
                                        <div className="flex cursor-pointer items-center justify-between px-4 py-3" onClick={() => toggleExpand(idx)}>
                                            <span className="text-lg font-bold">{relationship.employer_name}</span>
                                            <div className="ml-auto flex gap-2">
                                                <Button
                                                    size="sm"
                                                    variant={relationship.is_active ? 'secondary' : 'default'}
                                                    className={
                                                        relationship.is_active
                                                            ? 'bg-yellow-100 text-yellow-800 hover:bg-yellow-200'
                                                            : 'bg-green-100 text-green-800 hover:bg-green-200'
                                                    }
                                                    onClick={(e) => {
                                                        e.stopPropagation();
                                                        toggleStatus(idx);
                                                    }}
                                                >
                                                    {relationship.is_active ? 'Pendente' : 'Concluído'}
                                                </Button>
                                                <Button
                                                    size="sm"
                                                    variant="outline"
                                                    className="border-blue-300 text-blue-700 hover:bg-blue-50"
                                                    onClick={(e) => {
                                                        e.stopPropagation();
                                                        openModal(relationship);
                                                    }}
                                                >
                                                    Gerar Carta
                                                </Button>
                                                <Button
                                                    size="sm"
                                                    variant="destructive"
                                                    onClick={(e) => {
                                                        e.stopPropagation();
                                                        removeVinculo(idx);
                                                    }}
                                                >
                                                    Remover
                                                </Button>
                                                <span>{expanded === idx ? <ChevronUp /> : <ChevronDown />}</span>
                                            </div>
                                        </div>
                                        {expanded === idx && (
                                            <div className="border-t bg-muted p-4">
                                                <div className="mb-2 font-semibold">Tentativas de Contato</div>
                                                <div className="grid grid-cols-1 gap-4 md:grid-cols-3">
                                                    {[1, 2, 3].map((tentativa) => (
                                                        <div key={tentativa} className="rounded-lg border bg-background p-4">
                                                            <div className="mb-2 font-medium">Tentativa {tentativa}</div>
                                                            <div className="mb-2">
                                                                <label className="mb-1 block text-sm font-medium">Endereço</label>
                                                                <input
                                                                    type="text"
                                                                    className="w-full rounded border px-2 py-1"
                                                                    placeholder="Rua, número, cidade..."
                                                                    value={getTentativaValue(relationship.id, tentativa, 'endereco')}
                                                                    onChange={(e) =>
                                                                        updateTentativaValue(relationship.id, tentativa, 'endereco', e.target.value)
                                                                    }
                                                                    onBlur={(e) => saveTentativa(idx, tentativa, 'endereco', e.target.value)}
                                                                />
                                                            </div>
                                                            <div className="mb-2">
                                                                <label className="mb-1 block text-sm font-medium">Cód. Rastreio</label>
                                                                <input
                                                                    type="text"
                                                                    className="w-full rounded border px-2 py-1"
                                                                    placeholder="XX123456789BR"
                                                                    value={getTentativaValue(relationship.id, tentativa, 'rastreamento')}
                                                                    onChange={(e) =>
                                                                        updateTentativaValue(
                                                                            relationship.id,
                                                                            tentativa,
                                                                            'rastreamento',
                                                                            e.target.value,
                                                                        )
                                                                    }
                                                                    onBlur={(e) => saveTentativa(idx, tentativa, 'rastreamento', e.target.value)}
                                                                />
                                                            </div>
                                                            <div className="mb-2">
                                                                <label className="mb-1 block text-sm font-medium">Data de Envio</label>
                                                                <input
                                                                    type="date"
                                                                    className="w-full rounded border px-2 py-1"
                                                                    value={getTentativaValue(relationship.id, tentativa, 'data_envio')}
                                                                    onChange={(e) =>
                                                                        updateTentativaValue(relationship.id, tentativa, 'data_envio', e.target.value)
                                                                    }
                                                                    onBlur={(e) => saveTentativa(idx, tentativa, 'data_envio', e.target.value)}
                                                                />
                                                            </div>
                                                            <div className="mb-2">
                                                                <label className="mb-1 block text-sm font-medium">Retorno</label>
                                                                <select
                                                                    className="w-full rounded border px-2 py-1"
                                                                    value={getTentativaValue(relationship.id, tentativa, 'retorno') || 'Aguardando'}
                                                                    onChange={(e) => {
                                                                        updateTentativaValue(relationship.id, tentativa, 'retorno', e.target.value);
                                                                        saveTentativa(idx, tentativa, 'retorno', e.target.value);
                                                                    }}
                                                                >
                                                                    <option value="Aguardando">Aguardando</option>
                                                                    <option value="Recebido">Recebido</option>
                                                                    <option value="Devolvido">Devolvido</option>
                                                                    <option value="Não Localizado">Não Localizado</option>
                                                                </select>
                                                            </div>
                                                            <div className="mb-2">
                                                                <label className="mb-1 block text-sm font-medium">Email</label>
                                                                <input
                                                                    type="email"
                                                                    className="w-full rounded border px-2 py-1"
                                                                    placeholder="contato@empresa.com"
                                                                    value={getTentativaValue(relationship.id, tentativa, 'email')}
                                                                    onChange={(e) =>
                                                                        updateTentativaValue(relationship.id, tentativa, 'email', e.target.value)
                                                                    }
                                                                    onBlur={(e) => saveTentativa(idx, tentativa, 'email', e.target.value)}
                                                                />
                                                            </div>
                                                            <div>
                                                                <label className="mb-1 block text-sm font-medium">Telefone</label>
                                                                <input
                                                                    type="text"
                                                                    className="w-full rounded border px-2 py-1"
                                                                    placeholder="(XX) XXXXX-XXXX"
                                                                    value={getTentativaValue(relationship.id, tentativa, 'telefone')}
                                                                    onChange={(e) =>
                                                                        updateTentativaValue(relationship.id, tentativa, 'telefone', e.target.value)
                                                                    }
                                                                    onBlur={(e) => saveTentativa(idx, tentativa, 'telefone', e.target.value)}
                                                                />
                                                            </div>
                                                        </div>
                                                    ))}
                                                </div>
                                            </div>
                                        )}
                                    </div>
                                ))}
                            </div>
                        ) : (
                            <div className="py-12 text-center">
                                <Building className="mx-auto mb-4 h-12 w-12 text-muted-foreground" />
                                <h3 className="mb-2 text-lg font-medium">Nenhum vínculo encontrado</h3>
                                <p className="text-muted-foreground">Este caso ainda não possui vínculos empregatícios cadastrados.</p>
                            </div>
                        )}
                    </CardContent>
                </Card>

                {/* Modal de Gerar Carta */}
                <Dialog open={modalOpen} onOpenChange={setModalOpen}>
                    <DialogContent className="max-h-[90vh] w-full max-w-2xl overflow-y-auto md:max-w-4xl">
                        <DialogHeader>
                            <DialogTitle className="text-lg md:text-xl">Notificação Extrajudicial - Requerimento de PPP e LTCAT</DialogTitle>
                            <DialogDescription className="text-sm md:text-base">
                                Carta para: <b>{modalVinculo?.employer_name}</b> - CNPJ: <b>{formatCNPJ(modalVinculo?.employer_cnpj || '')}</b>
                            </DialogDescription>
                        </DialogHeader>
                        <div className="my-4 rounded-lg border bg-card text-foreground p-4 font-mono text-xs leading-relaxed md:p-6 md:text-sm">
                            <div className="mb-4 text-center md:mb-6">
                                <h2 className="mb-2 text-base font-bold md:text-lg">NOTIFICAÇÃO EXTRAJUDICIAL REQUERIMENTO DE PPP E LTCAT</h2>
                            </div>

                            <div className="mb-4">
                                <p className="font-bold">{modalVinculo?.employer_name}</p>
                                <p>CNPJ: {formatCNPJ(modalVinculo?.employer_cnpj || '')}</p>
                            </div>

                            <div className="mb-6 space-y-4 text-justify">
                                <p>
                                    <strong>{case_.client_name}</strong>, neste ato representado por seu advogado que este subscreve, vem, por meio
                                    deste, solicitar a emissão de PPP (Perfil Profissiográfico Previdenciário) com o respectivo LTCAT (Laudo Técnico
                                    das Condições Ambientais de Trabalho), para fins de instrução no processo de aposentadoria em face do Instituto
                                    Nacional do Seguro Social - INSS.
                                </p>

                                <p>
                                    Vale salientar que o PPP somente é válido diante do preenchimento de todos os campos, principalmente aos que se
                                    referem à exposição dos agentes nocivos, indicação do responsável técnico pelos registros ambientais (Tema 208 -
                                    TNU), carimbo e assinatura do responsável da empresa. Ressalta-se, ainda, que o documento deve seguir o modelo
                                    estabelecido pela Instrução normativa nº 133/2022 e 128/2022.
                                </p>

                                <p>
                                    Requer também que o PPP venha acompanhado de autorização que identifique o representante legal responsável pela
                                    assinatura do laudo ou a cópia do contrato social da empresa, quando o representante legal for o sócio da mesma.
                                </p>

                                <p>
                                    Os documentos deverão ser encaminhados por e-mail para <strong>extrajudicial@koetzadvocacia.com.br</strong>
                                </p>
                            </div>

                            <div className="mb-6">
                                <p className="mb-2 font-bold">DADOS DO REQUERENTE:</p>
                                <p>
                                    <strong>Nome:</strong> {case_.client_name}
                                </p>
                                <p>
                                    <strong>CPF:</strong> {case_.client_cpf}
                                </p>
                                <p>
                                    <strong>Período:</strong>{' '}
                                    {modalVinculo ? `${formatDate(modalVinculo.start_date)} a ${formatDate(modalVinculo.end_date)}` : ''}
                                </p>
                            </div>

                            <div className="mt-8">
                                <p className="mb-2">Certa de vossa atenção,</p>
                                <p className="mb-4">aguardo retorno. Atenciosamente,</p>
                                <p className="font-bold">Eduardo Koetz - OAB/SC 42.934</p>
                            </div>
                        </div>
                        <DialogFooter className="flex flex-col gap-2 sm:flex-row">
                            <Button
                                variant="outline"
                                className="w-full sm:w-auto"
                                onClick={async () => {
                                    if (modalVinculo) {
                                        try {
                                            const cartaContent = gerarTextoCarta(modalVinculo);
                                            await navigator.clipboard.writeText(cartaContent);
                                            alert('Carta copiada para a área de transferência!');
                                        } catch (error) {
                                            // Fallback para navegadores que não suportam clipboard API
                                            const textArea = document.createElement('textarea');
                                            textArea.value = gerarTextoCarta(modalVinculo);
                                            document.body.appendChild(textArea);
                                            textArea.select();
                                            document.execCommand('copy');
                                            document.body.removeChild(textArea);
                                            alert('Carta copiada para a área de transferência!');
                                        }
                                    }
                                }}
                            >
                                <Copy className="mr-2 h-4 w-4" />
                                Copiar Texto
                            </Button>
                            <Button onClick={closeModal} className="w-full sm:w-auto">
                                Fechar
                            </Button>
                        </DialogFooter>
                    </DialogContent>
                </Dialog>
            </div>

            {/* Modal de Novo Vínculo */}
            <Dialog open={newVinculoModalOpen} onOpenChange={setNewVinculoModalOpen}>
                <DialogContent className="sm:max-w-[600px]">
                    <form onSubmit={handleNewVinculoSubmit}>
                        <DialogHeader>
                            <DialogTitle>Novo Vínculo Empregatício</DialogTitle>
                            <DialogDescription>
                                Preencha os dados do novo vínculo empregatício.
                            </DialogDescription>
                        </DialogHeader>

                        <div className="grid gap-4 py-4">
                        <div className="grid grid-cols-2 gap-4">
                            <div className="space-y-2">
                                <Label htmlFor="employer_name">Nome do Empregador</Label>
                                <Input
                                    id="employer_name"
                                    value={newVinculoForm.employer_name}
                                    onChange={(e) => setNewVinculoForm(prev => ({ ...prev, employer_name: e.target.value }))}
                                />
                            </div>
                            <div className="space-y-2">
                                <Label htmlFor="employer_cnpj">CNPJ</Label>
                                <Input
                                    id="employer_cnpj"
                                    value={newVinculoForm.employer_cnpj}
                                    onChange={(e) => setNewVinculoForm(prev => ({ ...prev, employer_cnpj: e.target.value }))}
                                />
                            </div>
                        </div>

                        <div className="grid grid-cols-2 gap-4">
                            <div className="space-y-2">
                                <Label htmlFor="start_date">Data Início</Label>
                                <Input
                                    id="start_date"
                                    type="date"
                                    value={newVinculoForm.start_date}
                                    onChange={(e) => setNewVinculoForm(prev => ({ ...prev, start_date: e.target.value }))}
                                />
                            </div>
                            <div className="space-y-2">
                                <Label htmlFor="end_date">Data Fim</Label>
                                <Input
                                    id="end_date"
                                    type="date"
                                    value={newVinculoForm.end_date}
                                    onChange={(e) => setNewVinculoForm(prev => ({ ...prev, end_date: e.target.value }))}
                                />
                            </div>
                        </div>

                        <div className="space-y-2">
                            <Label htmlFor="cargo">Cargo</Label>
                            <Input
                                id="cargo"
                                value={newVinculoForm.cargo}
                                onChange={(e) => setNewVinculoForm(prev => ({ ...prev, cargo: e.target.value }))}
                            />
                        </div>

                        <div className="space-y-2">
                            <Label htmlFor="status_empresa">Status da Empresa</Label>
                            <select
                                id="status_empresa"
                                className="flex h-9 w-full rounded-md border border-input bg-background px-3 py-1 text-sm shadow-sm transition-colors file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50"
                                value={newVinculoForm.status_empresa}
                                onChange={(e) => setNewVinculoForm(prev => ({ ...prev, status_empresa: e.target.value }))}
                            >
                                <option value="">Selecione...</option>
                                <option value="Ativa">Ativa</option>
                                <option value="Baixada">Baixada</option>
                                <option value="Inapta">Inapta</option>
                            </select>
                        </div>

                        <div className="space-y-2">
                            <Label htmlFor="documentos">Documentos</Label>
                            <Input
                                id="documentos"
                                value={newVinculoForm.documentos}
                                onChange={(e) => setNewVinculoForm(prev => ({ ...prev, documentos: e.target.value }))}
                            />
                        </div>

                        <div className="space-y-2">
                            <Label htmlFor="observacoes">Observações</Label>
                            <Input
                                id="observacoes"
                                value={newVinculoForm.observacoes}
                                onChange={(e) => setNewVinculoForm(prev => ({ ...prev, observacoes: e.target.value }))}
                            />
                        </div>
                    </div>

                    <DialogFooter>
                        <Button type="button" variant="outline" onClick={() => setNewVinculoModalOpen(false)}>
                            Cancelar
                        </Button>
                        <Button type="submit">
                            Salvar
                        </Button>
                    </DialogFooter>
                    </form>
                </DialogContent>
            </Dialog>
        </AppLayout>
    );
}
