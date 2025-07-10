<?php

$title = 'Política de Privacidade - PrevidIA';
$description = 'Política de privacidade da extensão Chrome para sincronização de processos previdenciários';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title) ?></title>
    <meta name="description" content="<?= htmlspecialchars($description) ?>">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f8f9fa;
        }

        .container {
            max-width: 900px;
            margin: 0 auto;
            background: white;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            min-height: 100vh;
        }

        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 3rem 2rem;
            text-align: center;
        }

        .header h1 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .subtitle {
            font-size: 1.1rem;
            opacity: 0.9;
            font-weight: 300;
        }

        .content {
            padding: 2rem;
        }

        .section {
            margin-bottom: 2.5rem;
        }

        .section h2 {
            color: #2c3e50;
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 1rem;
            border-bottom: 2px solid #667eea;
            padding-bottom: 0.5rem;
        }

        .section h3 {
            color: #34495e;
            font-size: 1.2rem;
            font-weight: 600;
            margin: 1.5rem 0 0.75rem 0;
        }

        .section p {
            margin-bottom: 1rem;
            text-align: justify;
        }

        .section ul {
            margin: 1rem 0 1rem 2rem;
            list-style-type: disc;
        }

        .section li {
            margin-bottom: 0.5rem;
            line-height: 1.5;
        }

        .contact-info {
            background: #f8f9fa;
            padding: 1.5rem;
            border-radius: 8px;
            border-left: 4px solid #667eea;
            margin: 1rem 0;
        }

        .contact-info p {
            margin-bottom: 0.5rem;
        }

        .footer {
            background: #2c3e50;
            color: white;
            padding: 2rem;
            text-align: center;
            font-size: 0.9rem;
        }

        .footer p {
            margin-bottom: 0.5rem;
        }

        strong {
            color: #2c3e50;
            font-weight: 600;
        }

        @media (max-width: 768px) {
            .header {
                padding: 2rem 1rem;
            }

            .header h1 {
                font-size: 2rem;
            }

            .content {
                padding: 1.5rem;
            }

            .footer {
                padding: 1.5rem;
            }

            .section ul {
                margin-left: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <header class="header">
            <h1>Política de Privacidade</h1>
            <p class="subtitle">Extensão Chrome - Sistema de Sincronização de Processos</p>
        </header>

        <main class="content">
            <section class="section">
                <h2>1. Informações Gerais</h2>
                <p>Esta política de privacidade descreve como a extensão Chrome de sincronização de processos previdenciários coleta, utiliza e protege suas informações pessoais.</p>
                <p><strong>Última atualização:</strong> <?= date('d/m/Y') ?></p>
            </section>

            <section class="section">
                <h2>2. Dados Coletados</h2>
                <h3>2.1 Dados de Processos</h3>
                <ul>
                    <li>Número de protocolo dos processos</li>
                    <li>CPF do beneficiário</li>
                    <li>Nome do beneficiário</li>
                    <li>Tipo de serviço/benefício</li>
                    <li>Situação atual do processo</li>
                    <li>Datas de protocolo e última atualização</li>
                </ul>

                <h3>2.2 Dados Técnicos</h3>
                <ul>
                    <li>API Key da empresa</li>
                    <li>ID da empresa</li>
                    <li>Logs de sincronização</li>
                    <li>Timestamps de operações</li>
                </ul>
            </section>

            <section class="section">
                <h2>3. Finalidade do Tratamento</h2>
                <p>Os dados coletados são utilizados exclusivamente para:</p>
                <ul>
                    <li>Sincronização de informações de processos previdenciários</li>
                    <li>Controle de mudanças de situação dos processos</li>
                    <li>Manutenção do histórico de atualizações</li>
                    <li>Identificação e autenticação da empresa usuária</li>
                    <li>Geração de relatórios e estatísticas</li>
                </ul>
            </section>

            <section class="section">
                <h2>4. Base Legal</h2>
                <p>O tratamento dos dados pessoais está fundamentado nas seguintes bases legais da LGPD:</p>
                <ul>
                    <li><strong>Execução de contrato:</strong> Para prestação do serviço de sincronização contratado</li>
                    <li><strong>Interesse legítimo:</strong> Para melhoria e otimização do serviço</li>
                    <li><strong>Cumprimento de obrigação legal:</strong> Quando exigido por lei</li>
                </ul>
            </section>

            <section class="section">
                <h2>5. Compartilhamento de Dados</h2>
                <p>Seus dados não são compartilhados com terceiros, exceto:</p>
                <ul>
                    <li>Quando exigido por determinação legal ou ordem judicial</li>
                    <li>Para prestadores de serviços essenciais (hosting, infraestrutura), sempre com contratos de confidencialidade</li>
                    <li>Em caso de fusão, aquisição ou venda da empresa, mediante notificação prévia</li>
                </ul>
            </section>

            <section class="section">
                <h2>6. Segurança dos Dados</h2>
                <h3>6.1 Medidas Técnicas</h3>
                <ul>
                    <li>Criptografia de dados em trânsito (HTTPS/TLS)</li>
                    <li>Autenticação via API Key única por empresa</li>
                    <li>Validação e sanitização de dados recebidos</li>
                    <li>Controle de acesso baseado em permissões</li>
                    <li>Logs de auditoria de todas as operações</li>
                </ul>

                <h3>6.2 Medidas Administrativas</h3>
                <ul>
                    <li>Acesso restrito aos dados por pessoal autorizado</li>
                    <li>Treinamento regular da equipe sobre proteção de dados</li>
                    <li>Políticas internas de segurança da informação</li>
                    <li>Monitoramento contínuo de vulnerabilidades</li>
                </ul>
            </section>

            <section class="section">
                <h2>7. Retenção de Dados</h2>
                <p>Os dados são mantidos pelo período necessário para:</p>
                <ul>
                    <li>Prestação do serviço contratado</li>
                    <li>Cumprimento de obrigações legais (mínimo 5 anos)</li>
                    <li>Exercício de direitos em processos judiciais</li>
                </ul>
                <p>Após esse período, os dados são anonimizados ou excluídos de forma segura.</p>
            </section>

            <section class="section">
                <h2>8. Seus Direitos</h2>
                <p>Conforme a LGPD, você tem direito a:</p>
                <ul>
                    <li><strong>Confirmação e acesso:</strong> Saber se seus dados estão sendo tratados e acessá-los</li>
                    <li><strong>Correção:</strong> Corrigir dados incompletos, inexatos ou desatualizados</li>
                    <li><strong>Anonimização ou exclusão:</strong> Solicitar anonimização ou exclusão dos dados</li>
                    <li><strong>Portabilidade:</strong> Receber seus dados em formato estruturado</li>
                    <li><strong>Eliminação:</strong> Solicitar eliminação dos dados tratados com base no consentimento</li>
                    <li><strong>Revogação:</strong> Revogar consentimento a qualquer tempo</li>
                    <li><strong>Oposição:</strong> Opor-se ao tratamento realizado com base no interesse legítimo</li>
                </ul>
            </section>

            <section class="section">
                <h2>9. Transferência Internacional</h2>
                <p>Os dados são processados e armazenados exclusivamente em território brasileiro, em conformidade com a legislação nacional de proteção de dados.</p>
            </section>

            <section class="section">
                <h2>10. Cookies e Tecnologias Similares</h2>
                <p>A extensão utiliza tecnologias de armazenamento local apenas para:</p>
                <ul>
                    <li>Manter configurações de funcionamento</li>
                    <li>Armazenar temporariamente dados para sincronização</li>
                    <li>Garantir o funcionamento adequado da extensão</li>
                </ul>
            </section>

            <section class="section">
                <h2>11. Alterações na Política</h2>
                <p>Esta política pode ser alterada periodicamente. Mudanças significativas serão comunicadas através:</p>
                <ul>
                    <li>Atualização da data no topo desta página</li>
                    <li>Notificação via email para o responsável da empresa</li>
                    <li>Aviso na interface da extensão, quando aplicável</li>
                </ul>
            </section>

            <section class="section">
                <h2>12. Contato</h2>
                <p>Para exercer seus direitos ou esclarecer dúvidas sobre esta política, entre em contato:</p>
                <div class="contact-info">
                    <p><strong>Email:</strong> privacidade@previdia.com.br</p>
                    <p><strong>Telefone:</strong> (49) 991677823</p>
                </div>
                <p><strong>Encarregado de Dados (DPO):</strong> Guilherme Schuck dos Santos</p>
                <p><strong>Email do DPO:</strong> dpo@previdia.com.br</p>
            </section>

            <section class="section">
                <h2>13. Autoridade de Controle</h2>
                <p>Em caso de não resolução de questões relacionadas ao tratamento de dados pessoais, você pode contatar a Autoridade Nacional de Proteção de Dados (ANPD):</p>
                <div class="contact-info">
                    <p><strong>Site:</strong> www.gov.br/anpd</p>
                    <p><strong>Email:</strong> comunicacao@anpd.gov.br</p>
                </div>
            </section>
        </main>

        <footer class="footer">
            <p>&copy; <?= date('Y') ?> Previdia - Sistema de Sincronização de Processos. Todos os direitos reservados.</p>
            <p>Esta política está em conformidade com a Lei Geral de Proteção de Dados (LGPD) - Lei nº 13.709/2018</p>
        </footer>
    </div>
</body>
</html>