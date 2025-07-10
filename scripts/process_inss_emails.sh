#!/bin/bash

# Define o diretório do projeto
PROJECT_DIR="/var/www/previdia.com.br"

# Vai para o diretório do projeto
cd $PROJECT_DIR

# Executa o script Python
/usr/bin/python3 $PROJECT_DIR/scripts/process_inss_emails.py >> $PROJECT_DIR/storage/logs/cron_inss_emails.log 2>&1 