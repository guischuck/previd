# Nova API AdvBox - Implementação em PHP Puro

## Visão Geral

Esta implementação substitui a abordagem anterior baseada em Laravel MVC por uma solução mais direta em PHP puro para integração com a API do AdvBox. A nova abordagem elimina a complexidade de autenticação e simplifica o processo de criação de tarefas e movimentos.

## Arquivos Principais

### 1. `public/advbox_api.php`
Arquivo principal da API que gerencia todas as operações do AdvBox:

- **GET `/advbox_api.php/settings`** - Retorna usuários e tarefas
- **GET `/advbox_api.php/users`** - Retorna apenas usuários
- **GET `/advbox_api.php/tasks`** - Retorna apenas tarefas
- **GET `/advbox_api.php/lawsuits?protocol_number=123`** - Busca processo por protocolo
- **POST `/advbox_api.php/posts`** - Cria nova tarefa
- **POST `/advbox_api.php/movement`** - Cria novo movimento

### 2. `resources/js/components/modals/AdvboxTaskModal.tsx`
Modal React atualizado para usar a nova API:

- Removida complexidade de autenticação
- Simplificadas as chamadas de API
- Melhor tratamento de erros
- Interface mais limpa e responsiva

### 3. `public/test_advbox_new_api.php`
Arquivo de teste para verificar o funcionamento da API.

## Configuração

### Variáveis de Ambiente
Certifique-se de que as seguintes variáveis estão configuradas no arquivo `.env`:

```env
ADVBOX_API_KEY=sua_chave_api_aqui
ADVBOX_BASE_URL=https://app.advbox.com.br/api/v1
```

### Configuração do Laravel
A API também está configurada no arquivo `config/services.php`:

```php
'advbox' => [
    'api_key' => env('ADVBOX_API_KEY'),
    'base_url' => env('ADVBOX_BASE_URL', 'https://app.advbox.com.br/api/v1'),
],
```

## Funcionalidades

### 1. Busca de Configurações (`/settings`)
Retorna usuários e tarefas disponíveis no AdvBox em uma única chamada.

**Resposta:**
```json
{
    "success": true,
    "users": [
        {"id": 1, "name": "João Silva"},
        {"id": 2, "name": "Maria Santos"}
    ],
    "tasks": [
        {"id": 1, "name": "Análise de Documentos"},
        {"id": 2, "name": "Contato com Cliente"}
    ],
    "errors": []
}
```

### 2. Busca de Processo (`/lawsuits`)
Busca um processo no AdvBox pelo número do protocolo.

**Parâmetros:**
- `protocol_number` (obrigatório): Número do protocolo do processo

**Resposta:**
```json
{
    "success": true,
    "data": {
        "id": 123,
        "protocol_number": "123456789",
        "process_number": "0001234-56.2023.4.01.0000"
    }
}
```

### 3. Criação de Tarefa (`/posts`)
Cria uma nova tarefa no AdvBox.

**Campos obrigatórios:**
- `from`: ID do usuário responsável
- `guests`: Array com IDs dos usuários convidados
- `tasks_id`: ID do tipo de tarefa
- `lawsuits_id`: ID do processo

**Campos opcionais:**
- `comments`: Comentários sobre a tarefa
- `start_date`: Data de início (formato dd/mm/yyyy)
- `start_time`: Horário de início
- `end_date`: Data de fim
- `end_time`: Horário de fim
- `date_deadline`: Data limite
- `local`: Local da tarefa
- `urgent`: Se é urgente (boolean)
- `important`: Se é importante (boolean)
- `display_schedule`: Se deve exibir na agenda (boolean)

### 4. Criação de Movimento (`/movement`)
Cria um novo movimento manual no processo.

**Campos obrigatórios:**
- `lawsuit_id`: ID do processo
- `date`: Data do movimento (formato dd/mm/yyyy)
- `description`: Descrição do movimento

## Vantagens da Nova Implementação

### 1. Simplicidade
- Elimina a complexidade de autenticação com tokens
- Usa apenas a API key do AdvBox
- Menos dependências e camadas

### 2. Performance
- Menos overhead de framework
- Respostas mais rápidas
- Menos processamento desnecessário

### 3. Manutenibilidade
- Código mais direto e fácil de entender
- Menos arquivos envolvidos
- Debugging mais simples

### 4. Confiabilidade
- Menos pontos de falha
- Melhor tratamento de erros
- Logs mais claros

## Como Usar

### 1. No Modal de Andamentos
O modal já está configurado para usar a nova API. Quando o usuário clicar em "Adicionar no AdvBox":

1. O modal carrega usuários e tarefas via `/advbox_api.php/settings`
2. Busca o processo via `/advbox_api.php/lawsuits?protocol_number=XXX`
3. Permite criar tarefa via `/advbox_api.php/posts`
4. Permite criar movimento via `/advbox_api.php/movement`

### 2. Testando a API
Acesse `http://seu-dominio.com/test_advbox_new_api.php` para testar todas as funcionalidades.

### 3. Integração Direta
Para usar a API diretamente em outros componentes:

```javascript
// Buscar configurações
const settings = await axios.get('/advbox_api.php/settings');

// Buscar processo
const lawsuit = await axios.get('/advbox_api.php/lawsuits?protocol_number=123456789');

// Criar tarefa
const task = await axios.post('/advbox_api.php/posts', {
    from: "1",
    guests: ["1"],
    tasks_id: "1",
    lawsuits_id: "123",
    comments: "Nova tarefa"
});
```

## Tratamento de Erros

A API retorna respostas padronizadas:

**Sucesso:**
```json
{
    "success": true,
    "data": {...}
}
```

**Erro:**
```json
{
    "success": false,
    "error": "Descrição do erro"
}
```

## Logs e Debugging

A API registra logs detalhados para facilitar o debugging:

- Requisições para o AdvBox
- Respostas recebidas
- Erros encontrados
- Dados processados

## Migração da Implementação Anterior

### O que foi removido:
- Dependência de autenticação complexa
- Uso do AdvboxService do Laravel
- Rotas da API do Laravel para AdvBox
- Gerenciamento de tokens

### O que foi mantido:
- Interface do usuário
- Funcionalidades principais
- Validações de dados
- Tratamento de erros

### O que foi melhorado:
- Performance geral
- Simplicidade do código
- Confiabilidade
- Facilidade de manutenção

## Próximos Passos

1. **Testar a implementação** usando o arquivo de teste
2. **Monitorar logs** para identificar possíveis problemas
3. **Ajustar configurações** conforme necessário
4. **Documentar casos de uso específicos** da sua aplicação

## Suporte

Para dúvidas ou problemas com a implementação:

1. Verifique os logs do servidor
2. Teste usando o arquivo `test_advbox_new_api.php`
3. Confirme se a API key está configurada corretamente
4. Verifique se a API do AdvBox está acessível 