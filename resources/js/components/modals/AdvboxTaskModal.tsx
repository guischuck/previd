import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from "@/components/ui/dialog";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Textarea } from "@/components/ui/textarea";
import { Switch } from "@/components/ui/switch";
import { useState } from "react";
import axios from "axios";
import { toast } from "react-toastify";
import { format } from 'date-fns';
import { ptBR } from 'date-fns/locale';

interface AdvboxTaskModalProps {
    isOpen: boolean;
    onClose: () => void;
    andamento: {
        id: number;
        processo: {
            protocolo: string;
            nome: string;
            cpf: string;
            servico: string;
        };
        situacao_anterior: string;
        situacao_atual: string;
    };
}

interface FormErrors {
    from?: string;
    guests?: string;
    tasks_id?: string;
    start_date?: string;
    [key: string]: string | undefined;
}

export default function AdvboxTaskModal({ isOpen, onClose, andamento }: AdvboxTaskModalProps) {
    const [loading, setLoading] = useState(false);
    const [errors, setErrors] = useState<FormErrors>({});
    const [formData, setFormData] = useState({
        from: '', // ID do usuário que está criando (obrigatório)
        guests: [] as number[], // Lista de IDs dos convidados (obrigatório)
        tasks_id: '', // ID da tarefa relacionada (obrigatório)
        comments: `Atualização de situação do processo\n\nCliente: ${andamento.processo.nome}\nCPF: ${andamento.processo.cpf}\nServiço: ${andamento.processo.servico}\nSituação Anterior: ${andamento.situacao_anterior || 'N/A'}\nNova Situação: ${andamento.situacao_atual}\nProtocolo INSS: ${andamento.processo.protocolo}`,
        start_date: format(new Date(), 'dd/MM/yyyy'),
        start_time: format(new Date(), 'HH:mm'),
        end_date: format(new Date(), 'dd/MM/yyyy'),
        end_time: format(new Date(Date.now() + 3600000), 'HH:mm'),
        date_deadline: format(new Date(Date.now() + 7 * 24 * 3600000), 'dd/MM/yyyy'),
        date: format(new Date(), 'dd/MM/yyyy'),
        local: '',
        urgent: andamento.situacao_atual === 'EXIGÊNCIA',
        important: true,
        display_schedule: true,
        folder: andamento.processo.servico || 'Geral',
        guest_input: ''
    });

    const validateForm = (): boolean => {
        const newErrors: FormErrors = {};

        // Validar campos obrigatórios
        if (!formData.from) {
            newErrors.from = 'ID do usuário é obrigatório';
        }

        if (formData.guests.length === 0) {
            newErrors.guests = 'Adicione pelo menos um convidado';
        }

        if (!formData.tasks_id) {
            newErrors.tasks_id = 'ID da tarefa é obrigatório';
        }

        // Validar formato das datas (DD/MM/YYYY)
        const dateRegex = /^(0[1-9]|[12][0-9]|3[01])\/(0[1-9]|1[012])\/\d{4}$/;
        if (!dateRegex.test(formData.start_date)) {
            newErrors.start_date = 'Data inválida. Use o formato DD/MM/YYYY';
        }

        // Validar formato das horas (HH:MM)
        const timeRegex = /^([01][0-9]|2[0-3]):[0-5][0-9]$/;
        if (formData.start_time && !timeRegex.test(formData.start_time)) {
            newErrors.start_time = 'Hora inválida. Use o formato HH:MM';
        }

        setErrors(newErrors);
        return Object.keys(newErrors).length === 0;
    };

    const handleSubmit = async () => {
        if (!validateForm()) {
            toast.error('Por favor, corrija os erros no formulário');
            return;
        }

        try {
            setLoading(true);
            const response = await axios.post(`/andamentos/${andamento.id}/adicionar-advbox`, formData);
            
            if (response.data.success) {
                toast.success(response.data.message);
                onClose();
            } else {
                toast.error(response.data.error);
            }
        } catch (error: any) {
            toast.error(error.response?.data?.error || 'Erro ao adicionar no AdvBox');
        } finally {
            setLoading(false);
        }
    };

    const addGuest = () => {
        if (formData.guest_input.trim()) {
            const guestId = parseInt(formData.guest_input);
            if (!isNaN(guestId) && !formData.guests.includes(guestId)) {
                setFormData({
                    ...formData,
                    guests: [...formData.guests, guestId],
                    guest_input: ''
                });
                // Limpar erro de guests quando adicionar um
                if (errors.guests) {
                    setErrors({ ...errors, guests: undefined });
                }
            }
        }
    };

    const removeGuest = (guestId: number) => {
        setFormData({
            ...formData,
            guests: formData.guests.filter(id => id !== guestId)
        });
    };

    return (
        <Dialog open={isOpen} onOpenChange={onClose}>
            <DialogContent className="sm:max-w-[700px] max-h-[80vh] overflow-y-auto">
                <DialogHeader>
                    <DialogTitle>Adicionar Tarefa no AdvBox</DialogTitle>
                    <DialogDescription>
                        Preencha os dados para criar uma nova tarefa no AdvBox
                    </DialogDescription>
                </DialogHeader>

                <div className="grid gap-4 py-4">
                    {/* Campos obrigatórios de identificação */}
                    <div className="grid grid-cols-2 gap-4">
                        <div className="grid gap-2">
                            <Label htmlFor="from" className="flex items-center">
                                ID do Usuário
                                <span className="text-red-500 ml-1">*</span>
                            </Label>
                            <Input
                                id="from"
                                value={formData.from}
                                onChange={(e) => {
                                    setFormData({ ...formData, from: e.target.value });
                                    if (errors.from) setErrors({ ...errors, from: undefined });
                                }}
                                placeholder="Ex: 12345"
                                className={errors.from ? 'border-red-500' : ''}
                            />
                            {errors.from && <p className="text-sm text-red-500">{errors.from}</p>}
                        </div>
                        <div className="grid gap-2">
                            <Label htmlFor="tasks_id" className="flex items-center">
                                ID da Tarefa
                                <span className="text-red-500 ml-1">*</span>
                            </Label>
                            <Input
                                id="tasks_id"
                                value={formData.tasks_id}
                                onChange={(e) => {
                                    setFormData({ ...formData, tasks_id: e.target.value });
                                    if (errors.tasks_id) setErrors({ ...errors, tasks_id: undefined });
                                }}
                                placeholder="Ex: 98765"
                                className={errors.tasks_id ? 'border-red-500' : ''}
                            />
                            {errors.tasks_id && <p className="text-sm text-red-500">{errors.tasks_id}</p>}
                        </div>
                    </div>

                    {/* Convidados (Obrigatório) */}
                    <div className="grid gap-2">
                        <Label className="flex items-center">
                            Convidados
                            <span className="text-red-500 ml-1">*</span>
                        </Label>
                        <div className="flex gap-2">
                            <Input
                                value={formData.guest_input}
                                onChange={(e) => setFormData({ ...formData, guest_input: e.target.value })}
                                placeholder="ID do convidado"
                                type="number"
                                className={errors.guests ? 'border-red-500' : ''}
                            />
                            <Button type="button" variant="outline" onClick={addGuest}>
                                Adicionar
                            </Button>
                        </div>
                        {errors.guests && <p className="text-sm text-red-500">{errors.guests}</p>}
                        {formData.guests.length > 0 && (
                            <div className="flex flex-wrap gap-2 mt-2">
                                {formData.guests.map((guestId) => (
                                    <div key={guestId} className="flex items-center gap-1 bg-gray-100 px-2 py-1 rounded">
                                        <span>{guestId}</span>
                                        <Button
                                            type="button"
                                            variant="ghost"
                                            size="sm"
                                            onClick={() => removeGuest(guestId)}
                                            className="h-4 w-4 p-0"
                                        >
                                            ×
                                        </Button>
                                    </div>
                                ))}
                            </div>
                        )}
                    </div>

                    {/* Comentários */}
                    <div className="grid gap-2">
                        <Label htmlFor="comments">Comentários</Label>
                        <Textarea
                            id="comments"
                            value={formData.comments}
                            onChange={(e) => setFormData({ ...formData, comments: e.target.value })}
                            rows={6}
                        />
                    </div>

                    {/* Datas e horários */}
                    <div className="grid grid-cols-2 gap-4">
                        <div className="grid gap-2">
                            <Label htmlFor="start_date" className="flex items-center">
                                Data Início
                                <span className="text-red-500 ml-1">*</span>
                            </Label>
                            <Input
                                id="start_date"
                                type="text"
                                value={formData.start_date}
                                onChange={(e) => {
                                    setFormData({ ...formData, start_date: e.target.value });
                                    if (errors.start_date) setErrors({ ...errors, start_date: undefined });
                                }}
                                placeholder="DD/MM/YYYY"
                                className={errors.start_date ? 'border-red-500' : ''}
                            />
                            {errors.start_date && <p className="text-sm text-red-500">{errors.start_date}</p>}
                        </div>
                        <div className="grid gap-2">
                            <Label htmlFor="start_time">Hora Início</Label>
                            <Input
                                id="start_time"
                                type="text"
                                value={formData.start_time}
                                onChange={(e) => {
                                    setFormData({ ...formData, start_time: e.target.value });
                                    if (errors.start_time) setErrors({ ...errors, start_time: undefined });
                                }}
                                placeholder="HH:MM"
                                className={errors.start_time ? 'border-red-500' : ''}
                            />
                            {errors.start_time && <p className="text-sm text-red-500">{errors.start_time}</p>}
                        </div>
                    </div>

                    <div className="grid grid-cols-2 gap-4">
                        <div className="grid gap-2">
                            <Label htmlFor="end_date">Data Fim</Label>
                            <Input
                                id="end_date"
                                type="text"
                                value={formData.end_date}
                                onChange={(e) => setFormData({ ...formData, end_date: e.target.value })}
                                placeholder="DD/MM/YYYY"
                            />
                        </div>
                        <div className="grid gap-2">
                            <Label htmlFor="end_time">Hora Fim</Label>
                            <Input
                                id="end_time"
                                type="text"
                                value={formData.end_time}
                                onChange={(e) => setFormData({ ...formData, end_time: e.target.value })}
                                placeholder="HH:MM"
                            />
                        </div>
                    </div>

                    <div className="grid grid-cols-2 gap-4">
                        <div className="grid gap-2">
                            <Label htmlFor="date_deadline">Data Limite</Label>
                            <Input
                                id="date_deadline"
                                type="text"
                                value={formData.date_deadline}
                                onChange={(e) => setFormData({ ...formData, date_deadline: e.target.value })}
                                placeholder="DD/MM/YYYY"
                            />
                        </div>
                        <div className="grid gap-2">
                            <Label htmlFor="date">Data</Label>
                            <Input
                                id="date"
                                type="text"
                                value={formData.date}
                                onChange={(e) => setFormData({ ...formData, date: e.target.value })}
                                placeholder="DD/MM/YYYY"
                            />
                        </div>
                    </div>

                    {/* Local e Pasta */}
                    <div className="grid grid-cols-2 gap-4">
                        <div className="grid gap-2">
                            <Label htmlFor="local">Local</Label>
                            <Input
                                id="local"
                                value={formData.local}
                                onChange={(e) => setFormData({ ...formData, local: e.target.value })}
                                placeholder="Ex: Sala de reuniões - 3º andar"
                            />
                        </div>
                        <div className="grid gap-2">
                            <Label htmlFor="folder">Pasta</Label>
                            <Input
                                id="folder"
                                value={formData.folder}
                                onChange={(e) => setFormData({ ...formData, folder: e.target.value })}
                                placeholder="Ex: Pasta 123"
                            />
                        </div>
                    </div>

                    {/* Switches */}
                    <div className="flex items-center space-x-6">
                        <div className="flex items-center space-x-2">
                            <Switch
                                id="urgent"
                                checked={formData.urgent}
                                onCheckedChange={(checked) => setFormData({ ...formData, urgent: checked })}
                            />
                            <Label htmlFor="urgent">Urgente</Label>
                        </div>

                        <div className="flex items-center space-x-2">
                            <Switch
                                id="important"
                                checked={formData.important}
                                onCheckedChange={(checked) => setFormData({ ...formData, important: checked })}
                            />
                            <Label htmlFor="important">Importante</Label>
                        </div>

                        <div className="flex items-center space-x-2">
                            <Switch
                                id="display_schedule"
                                checked={formData.display_schedule}
                                onCheckedChange={(checked) => setFormData({ ...formData, display_schedule: checked })}
                            />
                            <Label htmlFor="display_schedule">Exibir no Calendário</Label>
                        </div>
                    </div>
                </div>

                <DialogFooter>
                    <Button variant="outline" onClick={onClose}>
                        Cancelar
                    </Button>
                    <Button onClick={handleSubmit} disabled={loading}>
                        {loading ? 'Criando...' : 'Criar Tarefa'}
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    );
} 