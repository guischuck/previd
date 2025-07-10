#!/bin/bash

# Diretório do projeto
PROJECT_DIR="/var/www/previdia.com.br"

# Ativar ambiente virtual Python (se existir)
if [ -d "$PROJECT_DIR/venv" ]; then
    source "$PROJECT_DIR/venv/bin/activate"
fi

# Instalar dependências Python necessárias
pip install python-dotenv mysql-connector-python

# Executar o script de processamento
python "$PROJECT_DIR/scripts/process_inss_emails.py"

# Desativar ambiente virtual (se estiver ativo)
if [ -n "$VIRTUAL_ENV" ]; then
    deactivate
fi 