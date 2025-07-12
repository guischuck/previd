<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Suporte - PrevidIA</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
        }
    </script>
    <link rel="stylesheet" href="css/dashboard.css">
</head>
<body class="bg-gray-50 dark:bg-gray-900 transition-colors duration-300">
    <div class="min-h-screen">
        <nav class="bg-white dark:bg-gray-800 shadow-sm border-b border-gray-200 dark:border-gray-700">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex items-center">
                        <div class="w-8 h-8 bg-gradient-to-r from-blue-600 to-purple-600 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                            </svg>
                        </div>
                        <span class="ml-2 text-xl font-bold text-gray-900 dark:text-white">Sistema INSS</span>
                    </div>
                    <div class="flex items-center space-x-4">
                        <a href="dashboard.html" class="text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white px-3 py-2 rounded-md text-sm font-medium">Dashboard</a>
                        <a href="compras.php" class="text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white px-3 py-2 rounded-md text-sm font-medium">Planos</a>
                        <button id="darkModeToggle" class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                            <svg class="w-5 h-5 text-gray-600 dark:text-gray-300 hidden dark:block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path>
                            </svg>
                            <svg class="w-5 h-5 text-gray-600 block dark:hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </nav>

        <main class="max-w-7xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h1 class="text-4xl font-bold text-gray-900 dark:text-white mb-4">Central de Suporte</h1>
                <p class="text-xl text-gray-600 dark:text-gray-300 max-w-3xl mx-auto">Estamos aqui para ajudar você a aproveitar ao máximo o Sistema INSS</p>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-12">
                <div class="lg:col-span-2">
                    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-200 dark:border-gray-700 p-8">
                        <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">Fale Conosco</h2>
                        <form class="space-y-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label for="nome" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Nome Completo</label>
                                    <input type="text" id="nome" name="nome" required class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:text-white">
                                </div>
                                <div>
                                    <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">E-mail</label>
                                    <input type="email" id="email" name="email" required class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:text-white">
                                </div>
                            </div>
                            <div>
                                <label for="assunto" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Assunto</label>
                                <select id="assunto" name="assunto" required class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:text-white">
                                    <option value="">Selecione um assunto</option>
                                    <option value="tecnico">Problema Técnico</option>
                                    <option value="billing">Faturamento</option>
                                    <option value="feature">Solicitação de Funcionalidade</option>
                                    <option value="training">Treinamento</option>
                                    <option value="other">Outros</option>
                                </select>
                            </div>
                            <div>
                                <label for="mensagem" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Mensagem</label>
                                <textarea id="mensagem" name="mensagem" rows="6" required class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:text-white" placeholder="Descreva detalhadamente sua dúvida ou problema..."></textarea>
                            </div>
                            <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-6 rounded-lg transition-colors">
                                Enviar Mensagem
                            </button>
                        </form>
                    </div>
                </div>

                <div class="space-y-6">
                    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-200 dark:border-gray-700 p-6">
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">Contato Direto</h3>
                        <div class="space-y-4">
                            <div class="flex items-center">
                                <div class="w-10 h-10 bg-blue-100 dark:bg-blue-900/20 rounded-lg flex items-center justify-center mr-3">
                                    <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <p class="font-medium text-gray-900 dark:text-white">E-mail</p>
                                    <p class="text-sm text-gray-600 dark:text-gray-300">guisdsantos@gmail.com</p>
                                </div>
                            </div>
                            <div class="flex items-center">
                                <div class="w-10 h-10 bg-green-100 dark:bg-green-900/20 rounded-lg flex items-center justify-center mr-3">
                                    <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="flex items-center">
                                <div class="w-10 h-10 bg-purple-100 dark:bg-purple-900/20 rounded-lg flex items-center justify-center mr-3">
                                    <svg class="w-5 h-5 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <p class="font-medium text-gray-900 dark:text-white">WhatsApp</p>
                                    <p class="text-sm text-gray-600 dark:text-gray-300">(49) 99167-7823</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-200 dark:border-gray-700 p-6">
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">Horário de Atendimento</h3>
                        <div class="space-y-2 text-sm text-gray-600 dark:text-gray-300">
                            <p><span class="font-medium">Segunda a Sexta:</span> 09h às 18h</p>
                            <p><span class="font-medium">Sábado:</span> Fechado</p>
                            <p><span class="font-medium">Domingo:</span> Fechado</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 mb-12">
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-200 dark:border-gray-700 p-6">
                    <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900/20 rounded-lg flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-2">Documentação</h3>
                    <p class="text-gray-600 dark:text-gray-300 mb-4">Guias completos e tutoriais para usar todas as funcionalidades do sistema.</p>
                    <a href="#" class="text-blue-600 dark:text-blue-400 font-medium hover:underline">Acessar Documentação →</a>
                </div>

                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-200 dark:border-gray-700 p-6">
                    <div class="w-12 h-12 bg-green-100 dark:bg-green-900/20 rounded-lg flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-2">Tutoriais em Vídeo</h3>
                    <p class="text-gray-600 dark:text-gray-300 mb-4">Aprenda assistindo nossos vídeos explicativos passo a passo.</p>
                    <a href="#" class="text-blue-600 dark:text-blue-400 font-medium hover:underline">Ver Vídeos →</a>
                </div>

                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-200 dark:border-gray-700 p-6">
                    <div class="w-12 h-12 bg-purple-100 dark:bg-purple-900/20 rounded-lg flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-2">FAQ</h3>
                    <p class="text-gray-600 dark:text-gray-300 mb-4">Respostas para as perguntas mais frequentes dos nossos usuários.</p>
                    <a href="#" class="text-blue-600 dark:text-blue-400 font-medium hover:underline">Ver FAQ →</a>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-200 dark:border-gray-700 p-8">
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-6 text-center">Perguntas Frequentes</h2>
                <div class="space-y-4">
                    <div class="border border-gray-200 dark:border-gray-600 rounded-lg">
                        <button class="w-full px-6 py-4 text-left font-medium text-gray-900 dark:text-white hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none" onclick="toggleFaq(1)">
                            Como funciona o sistema de análise CNIS?
                            <svg class="w-5 h-5 float-right mt-0.5 transform transition-transform faq-icon-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                        <div class="faq-content-1 hidden px-6 pb-4">
                            <p class="text-gray-600 dark:text-gray-300">Nossa IA analisa automaticamente os dados do CNIS identificando períodos de contribuição, vínculos empregatícios e possíveis inconsistências. O sistema calcula o tempo total de contribuição e sugere a melhor estratégia para cada caso.</p>
                        </div>
                    </div>

                    <div class="border border-gray-200 dark:border-gray-600 rounded-lg">
                        <button class="w-full px-6 py-4 text-left font-medium text-gray-900 dark:text-white hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none" onclick="toggleFaq(2)">
                            Posso cancelar minha assinatura a qualquer momento?
                            <svg class="w-5 h-5 float-right mt-0.5 transform transition-transform faq-icon-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                        <div class="faq-content-2 hidden px-6 pb-4">
                            <p class="text-gray-600 dark:text-gray-300">Sim, você pode cancelar sua assinatura a qualquer momento sem taxas de cancelamento. Oferecemos 30 dias de garantia para reembolso total caso não fique satisfeito.</p>
                        </div>
                    </div>

                    <div class="border border-gray-200 dark:border-gray-600 rounded-lg">
                        <button class="w-full px-6 py-4 text-left font-medium text-gray-900 dark:text-white hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none" onclick="toggleFaq(3)">
                            Como funciona a geração automática de petições?
                            <svg class="w-5 h-5 float-right mt-0.5 transform transition-transform faq-icon-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                        <div class="faq-content-3 hidden px-6 pb-4">
                            <p class="text-gray-600 dark:text-gray-300">Com base na análise dos dados do cliente e na jurisprudência atualizada, nossa IA gera petições personalizadas com fundamentação legal sólida, economizando horas de trabalho e garantindo qualidade técnica.</p>
                        </div>
                    </div>

                    <div class="border border-gray-200 dark:border-gray-600 rounded-lg">
                        <button class="w-full px-6 py-4 text-left font-medium text-gray-900 dark:text-white hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none" onclick="toggleFaq(4)">
                            Os dados dos clientes ficam seguros?
                            <svg class="w-5 h-5 float-right mt-0.5 transform transition-transform faq-icon-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                        <div class="faq-content-4 hidden px-6 pb-4">
                            <p class="text-gray-600 dark:text-gray-300">Sim, utilizamos criptografia de ponta a ponta e seguimos rigorosamente a LGPD. Todos os dados são armazenados em servidores seguros no Brasil e nunca são compartilhados com terceiros.</p>
                        </div>
                    </div>
                </div>
            </div>
        </main>

        <footer class="bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700">
            <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
                <div class="text-center">
                    <p class="text-gray-600 dark:text-gray-300">&copy; 2025 Sistema INSS. Todos os direitos reservados.</p>
                    <div class="mt-4 space-x-4">
                        <a href="politica.php" class="text-blue-600 dark:text-blue-400 hover:underline">Política de Privacidade</a>
                        <a href="#" class="text-blue-600 dark:text-blue-400 hover:underline">Termos de Uso</a>
                        <a href="compras.php" class="text-blue-600 dark:text-blue-400 hover:underline">Planos</a>
                    </div>
                </div>
            </div>
        </footer>
    </div>

    <script>
        document.getElementById('darkModeToggle').addEventListener('click', function() {
            document.documentElement.classList.toggle('dark');
            localStorage.setItem('darkMode', document.documentElement.classList.contains('dark'));
        });

        if (localStorage.getItem('darkMode') === 'true') {
            document.documentElement.classList.add('dark');
        }

        function toggleFaq(index) {
            const content = document.querySelector(`.faq-content-${index}`);
            const icon = document.querySelector(`.faq-icon-${index}`);
            
            content.classList.toggle('hidden');
            icon.classList.toggle('rotate-180');
        }
    </script>
</body>
</html>