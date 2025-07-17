import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import AppLayout from '@/layouts/app-layout';
import { Head } from '@inertiajs/react';
import React, { useEffect, useRef, useState } from 'react';

interface Client {
    id: number;
    name: string;
}

interface Message {
    sender: 'user' | 'chatgpt';
    text: string;
}

// Componente de indicador de digitação
const TypingIndicator = () => (
    <div className="flex justify-start mb-3 w-full">
        <div className="max-w-[95vw] rounded-2xl px-4 py-3 shadow-md md:max-w-[75%] rounded-bl-md bg-white dark:bg-slate-700 text-slate-800 dark:text-slate-100 border border-slate-200 dark:border-slate-600 shadow-slate-200 dark:shadow-slate-900/20">
            <div className="flex items-center space-x-1">
                <div className="text-slate-500 dark:text-slate-400 text-sm">IA está digitando</div>
                <div className="flex space-x-1">
                    <div className="w-2 h-2 bg-slate-400 rounded-full animate-bounce" style={{animationDelay: '0ms'}}></div>
                    <div className="w-2 h-2 bg-slate-400 rounded-full animate-bounce" style={{animationDelay: '150ms'}}></div>
                    <div className="w-2 h-2 bg-slate-400 rounded-full animate-bounce" style={{animationDelay: '300ms'}}></div>
                </div>
            </div>
        </div>
    </div>
);

// Função para converter markdown básico em HTML
const parseMarkdown = (text: string): string => {
    return (
        text
            // Negrito: **texto** -> <strong>texto</strong>
            .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
            // Itálico: *texto* -> <em>texto</em>
            .replace(/(?<!\*)\*([^*]+?)\*(?!\*)/g, '<em>$1</em>')
            // Lista com - no início da linha
            .replace(/^- (.+)$/gm, '<li class="ml-4 list-disc">$1</li>')
            // Lista numerada
            .replace(/^\d+\. (.+)$/gm, '<li class="ml-4 list-decimal">$1</li>')
            // Envolver listas consecutivas em <ul>
            .replace(/((?:<li class="ml-4 list-disc.*?<\/li>\s*)+)/gs, '<ul class="list-disc ml-4 space-y-1 my-2">$1</ul>')
            // Envolver listas numeradas consecutivas em <ol>
            .replace(/((?:<li class="ml-4 list-decimal.*?<\/li>\s*)+)/gs, '<ol class="list-decimal ml-4 space-y-1 my-2">$1</ol>')
            // Quebras de linha duplas -> parágrafos
            .replace(/\n\n/g, '</p><p class="mb-2">')
            // Quebras de linha simples -> <br>
            .replace(/\n/g, '<br />')
            // Envolver em parágrafo se não começar com tag HTML
            .replace(/^(?!<[uo]l|<li|<p)/gm, '<p class="mb-2">')
            // Fechar parágrafo no final se necessário
            .replace(/([^>])$/g, '$1</p>')
    );
};

export default function Chat() {
  const [clients, setClients] = useState<Client[]>([]);
  const [clientSearch, setClientSearch] = useState('');
  const [selectedClient, setSelectedClient] = useState<Client | null>(null);
  const [messages, setMessages] = useState<Message[]>([]);
  const [loadingHistory, setLoadingHistory] = useState(false);
    const [input, setInput] = useState('');
    const [loading, setLoading] = useState(false);
    const [isTyping, setIsTyping] = useState(false);
    const messagesEndRef = useRef<HTMLDivElement>(null);
    const inputRef = useRef<HTMLInputElement>(null);

    // Buscar clientes reais
  useEffect(() => {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    fetch('/api/clients', {
      credentials: 'include',
      headers: {
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': csrfToken || '',
      },
    })
      .then((res) => {
        if (!res.ok) {
          throw new Error('Erro ao buscar clientes');
        }
        return res.json();
      })
      .then((data) => {
        console.log('Clientes carregados:', data);
        setClients(data);
      })
      .catch((error) => {
        console.error('Erro ao carregar clientes:', error);
        setClients([]);
      });
  }, []);

  // Carregar histórico ao selecionar cliente
  useEffect(() => {
    const loadChatHistory = async () => {
      if (!selectedClient) {
        setMessages([]);
        return;
      }

      setLoadingHistory(true);
      try {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        console.log('Carregando histórico para case_id:', selectedClient.id);
        
        const response = await fetch(`/api/chat-messages?case_id=${selectedClient.id}`, {
          credentials: 'include',
          headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': csrfToken || '',
            'Accept': 'application/json',
          },
        });

        console.log('Response status:', response.status);
        console.log('Response headers:', Object.fromEntries(response.headers.entries()));
        
        if (!response.ok) {
          const errorText = await response.text();
          console.error('Erro na resposta:', response.status, errorText);
          throw new Error(`Erro ao carregar histórico: ${response.status}`);
        }
        
        const history = await response.json();
        console.log('Histórico carregado:', history);
        
        setMessages(history.map((msg: any) => ({
          sender: msg.sender === 'assistant' ? 'chatgpt' : 'user',
          text: msg.content
        })));
      } catch (error) {
        console.error('Erro ao carregar histórico:', error);
      } finally {
        setLoadingHistory(false);
      }
    };

    loadChatHistory();
  }, [selectedClient]);

    // Filtrar clientes baseado na busca
    const filteredClients = (() => {
        if (!clientSearch.trim()) return [];
        const searchTerm = clientSearch.toLowerCase().trim();
        return clients.filter((client) => {
            const clientName = (client.name || '').toLowerCase();
            return clientName.includes(searchTerm);
        });
    })();

    // Scroll automático para a última mensagem
    useEffect(() => {
        messagesEndRef.current?.scrollIntoView({ behavior: 'smooth' });
    }, [messages]);

    const sendMessage = async () => {
        if (!input.trim()) return;

        setMessages((prev) => [...prev, { sender: 'user', text: input }]);
        const currentInput = input;
        setInput('');
        setLoading(true);
        setIsTyping(true);

        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

            const res = await fetch('/api/ai-chat', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrfToken || '',
                },
                credentials: 'include',
                body: JSON.stringify({
                    client_id: selectedClient?.id || null,
                    message: currentInput,
                }),
            });

            if (!res.ok) {
                throw new Error(`Erro HTTP: ${res.status}`);
            }

            const data = await res.json();

            if (data.success) {
                setMessages((prev) => [...prev, { sender: 'chatgpt', text: data.response || 'Sem resposta.' }]);
            } else {
                setMessages((prev) => [...prev, { sender: 'chatgpt', text: data.response || 'Erro ao processar mensagem.' }]);
            }
        } catch (e) {
            console.error('Erro no chat:', e);
            setMessages((prev) => [
                ...prev,
                { sender: 'chatgpt', text: 'Erro ao conectar com o servidor. Verifique sua conexão e tente novamente.' },
            ]);
        }
        setLoading(false);
        setIsTyping(false);
    };

    const handleInputKeyDown = (e: React.KeyboardEvent<HTMLInputElement>) => {
        if (e.key === 'Enter') sendMessage();
    };

    // Resetar estados ao trocar de cliente
    useEffect(() => {
        setInput('');
        setIsTyping(false);
        inputRef.current?.focus();
    }, [selectedClient]);

    return (
        <AppLayout>
            <Head title="Chat com ChatGPT" />
            <div className="flex h-full w-full flex-col overflow-hidden bg-background">
                {/* Topo: seleção de cliente */}
                <div className="flex flex-col gap-2 border-b border-slate-200 dark:border-slate-700 bg-white/80 dark:bg-slate-800/80 backdrop-blur-sm px-2 py-3 md:px-6 md:py-4">
                    <div className="text-lg font-semibold text-slate-800 dark:text-slate-200">Chat com IA Previdenciária</div>
                    {!selectedClient ? (
                        <div className="relative">
                            <Input
                                placeholder="Busque o cliente pelo nome..."
                                value={clientSearch}
                                onChange={(e) => setClientSearch(e.target.value)}
                                className="pr-2"
                                autoFocus
                            />
                            {/* Dropdown de clientes */}
                            {clientSearch.trim() && filteredClients.length > 0 && (
                                <div className="absolute top-full right-0 left-0 z-10 max-h-48 overflow-y-auto rounded-md border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 shadow-lg">
                                    {filteredClients.map((c) => (
                                        <button
                                            key={c.id}
                                            className="w-full border-b border-slate-100 dark:border-slate-700 px-3 py-2 text-left text-slate-800 dark:text-slate-200 last:border-b-0 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors"
                                            onClick={() => {
                                                setSelectedClient(c);
                                                setClientSearch('');
                                            }}
                                        >
                                            {c.name}
                                        </button>
                                    ))}
                                </div>
                            )}
                        </div>
                    ) : (
                        <div className="flex items-center gap-2">
                            <span className="font-medium text-emerald-600 dark:text-emerald-400">{selectedClient.name}</span>
                            <Button size="sm" variant="outline" onClick={() => setSelectedClient(null)}>
                                Trocar cliente
                            </Button>
                        </div>
                    )}
                </div>
                {/* Área de mensagens */}
                <div className="flex min-h-[300px] flex-1 flex-col overflow-y-auto bg-gradient-to-b from-slate-50 to-slate-100 dark:from-slate-900 dark:to-slate-800 px-1 py-2 md:px-6 md:py-4">
                    {!loadingHistory && messages.length === 0 ? (
                        <div className="flex min-h-[60vh] w-full flex-1 items-center justify-center">
                            <div className="mx-2 flex w-full max-w-xl flex-col items-center gap-4 rounded-xl border bg-white/80 dark:bg-slate-800/80 backdrop-blur-sm p-4 shadow-lg md:p-8">
                                <div className="mb-2 rounded-full bg-emerald-100 p-3 text-emerald-600 dark:bg-emerald-900/30 dark:text-emerald-400">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="none" viewBox="0 0 24 24">
                                        <path
                                            fill="currentColor"
                                            d="M12 2a10 10 0 1 0 10 10A10.011 10.011 0 0 0 12 2Zm0 18.75A8.75 8.75 0 1 1 20.75 12 8.76 8.76 0 0 1 12 20.75Zm0-13.5a1.25 1.25 0 1 1-1.25 1.25A1.25 1.25 0 0 1 12 7.25Zm1.25 8.5a1.25 1.25 0 0 1-2.5 0v-3.5a1.25 1.25 0 0 1 2.5 0Z"
                                        />
                                    </svg>
                                </div>
                                <div className="text-center text-lg font-semibold text-slate-800 dark:text-slate-200">
                                    Olá! Sou sua assistente previdenciária especializada. Posso ajudar com:
                                </div>
                                <ul className="mx-auto w-full max-w-xs list-disc space-y-1 pl-5 text-left text-base text-slate-700 dark:text-slate-300">
                                    <li>Análise de casos previdenciários</li>
                                    <li>Interpretação de documentos CNIS</li>
                                    <li>Estratégias para benefícios</li>
                                    <li>Dúvidas sobre vínculos empregatícios</li>
                                    <li>Orientações sobre coleta de documentos</li>
                                </ul>
                                <div className="mt-2 w-full text-center text-sm text-slate-600 dark:text-slate-400">
                                    <b>💡 Dica:</b> Use a busca acima para selecionar um cliente e obter respostas mais precisas!
                                </div>
                            </div>
                        </div>
      ) : loadingHistory ? (
        <div className="flex min-h-[60vh] w-full flex-1 items-center justify-center">
          <div className="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-emerald-500"></div>
        </div>
      ) : (
        <>
                            {messages.map((msg, idx) => (
                                <div key={idx} className={`flex ${msg.sender === 'user' ? 'justify-end' : 'justify-start'} mb-3 w-full`}>
                                    <div
                                        className={`max-w-[95vw] rounded-2xl px-4 py-3 shadow-md md:max-w-[75%] ${
                                            msg.sender === 'user'
                                                ? 'rounded-br-md bg-emerald-500 text-white shadow-emerald-200 dark:shadow-emerald-900/20'
                                                : 'rounded-bl-md bg-white dark:bg-slate-700 text-slate-800 dark:text-slate-100 border border-slate-200 dark:border-slate-600 shadow-slate-200 dark:shadow-slate-900/20'
                                        }`}
                                    >
                                        {msg.sender === 'chatgpt' ? (
                                            <div
                                                className="markdown-content prose prose-sm max-w-none text-slate-800 dark:text-slate-100 [&_strong]:text-slate-900 [&_strong]:dark:text-slate-50 [&_em]:text-slate-700 [&_em]:dark:text-slate-200 [&_code]:bg-slate-100 [&_code]:dark:bg-slate-600 [&_code]:text-slate-800 [&_code]:dark:text-slate-200 [&_code]:px-1 [&_code]:py-0.5 [&_code]:rounded [&_pre]:bg-slate-100 [&_pre]:dark:bg-slate-600 [&_pre]:text-slate-800 [&_pre]:dark:text-slate-200 [&_pre]:p-3 [&_pre]:rounded-lg [&_ul]:text-slate-800 [&_ul]:dark:text-slate-100 [&_ol]:text-slate-800 [&_ol]:dark:text-slate-100 [&_li]:text-slate-800 [&_li]:dark:text-slate-100 [&_p]:text-slate-800 [&_p]:dark:text-slate-100"
                                                dangerouslySetInnerHTML={{
                                                    __html: parseMarkdown(msg.text),
                                                }}
                                            />
                                        ) : (
                                            <div className="whitespace-pre-wrap text-white">{msg.text}</div>
                                        )}
                                    </div>
                                </div>
                            ))}
                            {isTyping && <TypingIndicator />}
                        </>
                    )}
                    <div ref={messagesEndRef} />
                </div>
                {/* Input fixo */}
                <div className="flex gap-2 border-t border-slate-200 dark:border-slate-700 bg-white/80 dark:bg-slate-800/80 backdrop-blur-sm px-1 py-3 md:px-6 md:py-4">
                    <Input
                        ref={inputRef}
                        value={input}
                        onChange={(e) => setInput(e.target.value)}
                        onKeyDown={handleInputKeyDown}
                        placeholder="Digite sua mensagem..."
                        disabled={loading}
                        className="min-w-0 flex-1"
                        autoFocus
                    />
                    <Button onClick={sendMessage} disabled={!input.trim() || loading} className="shrink-0">
                        {loading ? 'Enviando...' : 'Enviar'}
                    </Button>
                </div>
            </div>
        </AppLayout>
    );
}
