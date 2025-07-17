import React, { useState, useEffect } from 'react';
import axios from 'axios';
import { 
    Dialog, 
    DialogContent, 
    DialogHeader, 
    DialogTitle, 
    DialogDescription 
} from "@/components/ui/dialog";
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Loader2, Users, FileText, Calendar, AlertCircle } from 'lucide-react';
import { toast } from 'react-toastify';

interface AdvboxTaskModalProps {
    isOpen: boolean;
    onClose: () => void;
    andamento: any;
}

interface User {
    id: number;
    name: string;
}

interface Task {
    id: number;
    name: string;
}

interface Lawsuit {
    id: number;
    process_number?: string;
    protocol_number?: string;
}

export default function AdvboxTaskModal({ 
    isOpen, 
    onClose, 
    andamento 
}: AdvboxTaskModalProps) {
    const [users, setUsers] = useState<User[]>([]);
    const [tasks, setTasks] = useState<Task[]>([]);
    const [lawsuit, setLawsuit] = useState<Lawsuit | null>(null);
    const [loading, setLoading] = useState(false);
    const [error, setError] = useState<string | null>(null);

    // Form state
    const [selectedUsers, setSelectedUsers] = useState<number[]>([]);
    const [selectedTask, setSelectedTask] = useState<string>('');
    const [taskComments, setTaskComments] = useState<string>('');
    const [taskDate, setTaskDate] = useState<string>('');
    const [taskDeadline, setTaskDeadline] = useState<string>('');

    useEffect(() => {
        if (isOpen && andamento) {
            setError(null);
            fetchData();
        }
    }, [isOpen, andamento]);

    const fetchData = async () => {
        setLoading(true);
        try {
            console.log('Fetching data for andamento:', andamento);
            
            // Fetch settings (users and tasks) from the new PHP API
            console.log('Fetching settings...');
            const settingsResponse = await axios.get('/advbox_api.php?endpoint=settings');
            console.log('Settings response:', settingsResponse.data);
            
            if (settingsResponse.data.success) {
                setUsers(settingsResponse.data.users || []);
                setTasks(settingsResponse.data.tasks || []);
                console.log('Users loaded:', settingsResponse.data.users?.length || 0);
                console.log('Tasks loaded:', settingsResponse.data.tasks?.length || 0);
            } else {
                console.error('Settings fetch failed:', settingsResponse.data);
                setError('Erro ao carregar configurações: ' + (settingsResponse.data.errors?.join(', ') || 'Erro desconhecido'));
                setUsers([]);
                setTasks([]);
            }

            // Fetch lawsuit by protocol number
            if (andamento.processo?.protocolo) {
                console.log('Fetching lawsuit for protocol:', andamento.processo.protocolo);
                const lawsuitResponse = await axios.get(`/advbox_api.php?endpoint=lawsuits&protocol_number=${andamento.processo.protocolo}`);
                console.log('Lawsuit response:', lawsuitResponse.data);
                
                if (lawsuitResponse.data.success) {
                    setLawsuit(lawsuitResponse.data.data);
                    console.log('Lawsuit found:', lawsuitResponse.data.data);
                } else {
                    console.warn('Processo não encontrado no AdvBox:', lawsuitResponse.data.error);
                    setLawsuit(null);
                }
            }
        } catch (error: any) {
            console.error('Error fetching data:', error);
            console.error('Error details:', error.response?.data);
            setError(error.response?.data?.error || 'Erro ao carregar dados');
            setUsers([]);
            setTasks([]);
        } finally {
            setLoading(false);
        }
    };

    const handleCreateTask = async () => {
        console.log('handleCreateTask called');
        console.log('lawsuit:', lawsuit);
        console.log('selectedTask:', selectedTask);
        console.log('selectedUsers:', selectedUsers);
        
        if (!lawsuit) {
            console.error('Lawsuit not found');
            toast.error('Processo não encontrado no AdvBox');
            return;
        }

        if (!selectedTask || selectedUsers.length === 0) {
            console.error('Missing task or users:', { selectedTask, selectedUsers });
            toast.error('Selecione uma tarefa e pelo menos um usuário');
            return;
        }

        // Formatar datas
        const formatDate = (date: string) => {
            if (!date) return '';
            const [year, month, day] = date.split('-');
            return `${day}/${month}/${year}`;
        };

        console.log('Starting task creation...');
        setLoading(true);
        try {
            const taskData = {
                from: selectedUsers[0].toString(),
                guests: selectedUsers.map(String),
                tasks_id: selectedTask,
                lawsuits_id: lawsuit.id.toString(),
                comments: taskComments || `Tarefa para o processo ${lawsuit.protocol_number}`,
                start_date: formatDate(taskDate) || formatDate(new Date().toISOString().split('T')[0]),
                start_time: '09:00',
                end_date: formatDate(taskDate) || formatDate(new Date().toISOString().split('T')[0]),
                end_time: '17:00',
                date_deadline: formatDate(taskDeadline),
                date: formatDate(new Date().toISOString().split('T')[0]),
                local: '',
                urgent: false,
                important: false,
                display_schedule: true,
                folder: `Processo ${lawsuit.protocol_number}`,
                protocol_number: lawsuit.protocol_number,
                process_number: lawsuit.process_number
            };

            console.log('Task data to be sent:', taskData);

            const response = await axios.post('/advbox_api.php?endpoint=posts', {
                data: taskData
            }, {
                headers: {
                    'Content-Type': 'application/json'
                }
            });

            console.log('API Response:', response.data);

            if (response.data.success) {
                console.log('Task created successfully');
                toast.success('Tarefa criada com sucesso no AdvBox');
                onClose();
            } else {
                console.error('Task creation failed:', response.data);
                toast.error('Erro ao criar tarefa no AdvBox: ' + response.data.error);
            }
        } catch (error: any) {
            console.error('Error creating task:', error);
            console.error('Error details:', error.response?.data);
            toast.error(error.response?.data?.error || 'Erro ao criar tarefa');
        } finally {
            console.log('Task creation finished');
            setLoading(false);
        }
    };

    const handleCreateMovement = async () => {
        if (!lawsuit) {
            toast.error('Processo não encontrado no AdvBox');
            return;
        }

        // Formatar datas
        const formatDate = (date: string) => {
            if (!date) return '';
            const [year, month, day] = date.split('-');
            return `${day}/${month}/${year}`;
        };

        console.log('Starting movement creation...');
        setLoading(true);
        try {
            const movementData = {
                lawsuit_id: lawsuit.id,
                date: formatDate(taskDate) || formatDate(new Date().toISOString().split('T')[0]),
                description: taskComments || `Movimento para o processo ${lawsuit.protocol_number}`,
                type: 'MANUAL'
            };

            console.log('Movement data to be sent:', movementData);

            const response = await axios.post('/advbox_api.php?endpoint=movement', {
                data: movementData
            }, {
                headers: {
                    'Content-Type': 'application/json'
                }
            });

            console.log('API Response:', response.data);

            if (response.data.success) {
                console.log('Movement created successfully');
                toast.success('Movimento criado com sucesso no AdvBox');
                onClose();
            } else {
                console.error('Movement creation failed:', response.data);
                toast.error('Erro ao criar movimento no AdvBox: ' + response.data.error);
            }
        } catch (error: any) {
            console.error('Error creating movement:', error);
            console.error('Error details:', error.response?.data);
            toast.error(error.response?.data?.error || 'Erro ao criar movimento');
        } finally {
            console.log('Movement creation finished');
            setLoading(false);
        }
    };

    // Se houver erro, mostrar tela de erro
    if (error) {
        return (
            <Dialog open={isOpen} onOpenChange={onClose}>
                <DialogContent>
                    <div className="flex flex-col items-center justify-center gap-4 py-8">
                        <AlertCircle className="h-12 w-12 text-red-500" />
                        <div className="text-center">
                            <p className="text-lg font-medium text-red-600">Erro de Conexão</p>
                            <p className="text-sm text-muted-foreground">
                                {error}
                            </p>
                            <div className="flex justify-center gap-4 mt-4">
                                <Button 
                                    onClick={() => {
                                        setError(null);
                                        fetchData();
                                    }}
                                >
                                    Tentar Novamente
                                </Button>
                                <Button 
                                    variant="outline"
                                    onClick={onClose}
                                >
                                    Fechar
                                </Button>
                            </div>
                        </div>
                    </div>
                </DialogContent>
            </Dialog>
        );
    }

    return (
        <Dialog open={isOpen} onOpenChange={onClose}>
            <DialogContent className="max-w-2xl">
                <DialogHeader>
                    <DialogTitle>Adicionar no AdvBox</DialogTitle>
                    <DialogDescription>
                        Adicione uma tarefa ou movimento para o processo {andamento.processo?.protocolo}
                    </DialogDescription>
                </DialogHeader>

                {loading ? (
                    <div className="flex justify-center items-center h-64">
                        <Loader2 className="h-8 w-8 animate-spin text-muted-foreground" />
                    </div>
                ) : (
                    <div className="space-y-4">
                        {/* Processo Details */}
                        <div className="grid grid-cols-2 gap-4 bg-muted/20 p-4 rounded-lg">
                            <div>
                                <div className="text-sm font-medium text-muted-foreground">Nome</div>
                                <div>{andamento.processo?.nome}</div>
                            </div>
                            <div>
                                <div className="text-sm font-medium text-muted-foreground">Protocolo</div>
                                <div>{andamento.processo?.protocolo}</div>
                            </div>
                        </div>

                        {/* Status do Processo no AdvBox */}
                        <div className="p-3 rounded-lg border">
                            <div className="text-sm font-medium text-muted-foreground mb-1">
                                Status no AdvBox
                            </div>
                            {lawsuit ? (
                                <div className="text-green-600 text-sm">
                                    ✓ Processo encontrado (ID: {lawsuit.id})
                                </div>
                            ) : (
                                <div className="text-orange-600 text-sm">
                                    ⚠ Processo não encontrado no AdvBox
                                </div>
                            )}
                        </div>

                        {/* Users Multiselect */}
                        <div>
                            <label className="flex items-center gap-2 mb-2 text-sm font-medium">
                                <Users className="h-4 w-4" /> Usuários
                            </label>
                            <Select 
                                value={selectedUsers.length > 0 ? selectedUsers[0].toString() : ''} 
                                onValueChange={(value: string) => {
                                    // Para simplificar, vamos usar apenas um usuário por enquanto
                                    setSelectedUsers([parseInt(value)]);
                                }}
                                disabled={users.length === 0}
                            >
                                <SelectTrigger>
                                    <SelectValue placeholder={users.length === 0 ? "Nenhum usuário disponível" : "Selecione um usuário"} />
                                </SelectTrigger>
                                <SelectContent>
                                    {users.length === 0 ? (
                                        <div className="p-2 text-sm text-muted-foreground">
                                            Não há usuários disponíveis
                                        </div>
                                    ) : (
                                        users.map((user) => (
                                            <SelectItem 
                                                key={user.id} 
                                                value={user.id.toString()}
                                            >
                                                {user.name}
                                            </SelectItem>
                                        ))
                                    )}
                                </SelectContent>
                            </Select>
                        </div>

                        {/* Task or Movement Type */}
                        <div>
                            <label className="flex items-center gap-2 mb-2 text-sm font-medium">
                                <FileText className="h-4 w-4" /> Tipo
                            </label>
                            <Select 
                                value={selectedTask} 
                                onValueChange={setSelectedTask}
                                disabled={tasks.length === 0}
                            >
                                <SelectTrigger>
                                    <SelectValue placeholder={tasks.length === 0 ? "Nenhuma tarefa disponível" : "Selecione uma tarefa"} />
                                </SelectTrigger>
                                <SelectContent>
                                    {tasks.length === 0 ? (
                                        <div className="p-2 text-sm text-muted-foreground">
                                            Não há tarefas disponíveis
                                        </div>
                                    ) : (
                                        tasks.map((task) => (
                                            <SelectItem 
                                                key={task.id} 
                                                value={task.id.toString()}
                                            >
                                                {task.name}
                                            </SelectItem>
                                        ))
                                    )}
                                </SelectContent>
                            </Select>
                        </div>

                        {/* Comments */}
                        <div>
                            <label className="mb-2 block text-sm font-medium">Comentários</label>
                            <Input 
                                placeholder="Adicione comentários sobre a tarefa ou movimento"
                                value={taskComments}
                                onChange={(e) => setTaskComments(e.target.value)}
                            />
                        </div>

                        {/* Dates */}
                        <div className="grid grid-cols-2 gap-4">
                            <div>
                                <label className="flex items-center gap-2 mb-2 text-sm font-medium">
                                    <Calendar className="h-4 w-4" /> Data
                                </label>
                                <Input 
                                    type="date" 
                                    value={taskDate}
                                    onChange={(e) => setTaskDate(e.target.value)}
                                />
                            </div>
                            <div>
                                <label className="flex items-center gap-2 mb-2 text-sm font-medium">
                                    <Calendar className="h-4 w-4" /> Prazo
                                </label>
                                <Input 
                                    type="date" 
                                    value={taskDeadline}
                                    onChange={(e) => setTaskDeadline(e.target.value)}
                                />
                            </div>
                        </div>

                        {/* Actions */}
                        <div className="flex justify-end gap-2">
                            <Button 
                                variant="outline" 
                                onClick={onClose}
                                disabled={loading}
                            >
                                Cancelar
                            </Button>
                            <Button 
                                onClick={handleCreateTask}
                                disabled={loading || !lawsuit}
                                title={!lawsuit ? "Processo não encontrado no AdvBox" : ""}
                            >
                                {loading ? (
                                    <Loader2 className="h-4 w-4 animate-spin mr-2" />
                                ) : null}
                                Criar Tarefa
                            </Button>
                            <Button 
                                variant="secondary"
                                onClick={handleCreateMovement}
                                disabled={loading || !lawsuit}
                                title={!lawsuit ? "Processo não encontrado no AdvBox" : ""}
                            >
                                {loading ? (
                                    <Loader2 className="h-4 w-4 animate-spin mr-2" />
                                ) : null}
                                Criar Movimento
                            </Button>
                        </div>
                    </div>
                )}
            </DialogContent>
        </Dialog>
    );
}