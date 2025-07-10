#!/usr/bin/env python3
"""
Extrator de dados do CNIS usando Python - Versão Simplificada
Usa apenas bibliotecas básicas do Python para extrair informações do CNIS
"""

import sys
import json
import re
import argparse
from pathlib import Path
from typing import Dict, List, Any, Optional
import logging

# Configuração de logging
logging.basicConfig(level=logging.INFO)
logger = logging.getLogger(__name__)

class CNISExtractorSimple:
    """Classe para extração de dados do CNIS usando Python básico"""
    
    def __init__(self):
        """Inicializa o extrator"""
        logger.info("CNIS Extractor Simple inicializado")
    
    def extract_text_from_pdf(self, pdf_path: str) -> str:
        """Extrai texto do PDF usando métodos básicos"""
        try:
            # Tenta usar PyPDF2 se disponível
            try:
                import PyPDF2
                with open(pdf_path, 'rb') as file:
                    reader = PyPDF2.PdfReader(file)
                    text = ""
                    for page in reader.pages:
                        text += page.extract_text() + "\n"
                logger.info("Texto extraído com PyPDF2")
                return text
            except ImportError:
                logger.warning("PyPDF2 não encontrado, tentando pdfplumber")
            
            # Tenta usar pdfplumber se disponível
            try:
                import pdfplumber
                text = ""
                with pdfplumber.open(pdf_path) as pdf:
                    for page in pdf.pages:
                        page_text = page.extract_text()
                        if page_text:
                            text += page_text + "\n"
                logger.info("Texto extraído com pdfplumber")
                return text
            except ImportError:
                logger.warning("pdfplumber não encontrado")
            
            # Se nenhuma biblioteca estiver disponível, retorna erro
            raise ImportError("Nenhuma biblioteca de PDF encontrada. Instale PyPDF2 ou pdfplumber")
            
        except Exception as e:
            logger.error(f"Erro ao extrair texto do PDF: {e}")
            return ""
    
    def extract_personal_data(self, text: str) -> Dict[str, str]:
        """Extrai dados pessoais do texto"""
        personal_data = {}
        
        # Padrões para CPF
        cpf_patterns = [
            r'CPF[:\s]*(\d{3}\.\d{3}\.\d{3}-\d{2})',
            r'NIT[:\s]*\d+\.\d+\.\d+\-\d+\s+CPF[:\s]*(\d{3}\.\d{3}\.\d{3}-\d{2})',
        ]
        
        for pattern in cpf_patterns:
            match = re.search(pattern, text)
            if match:
                personal_data['cpf'] = match.group(1)
                break
        
        # Padrões para nome - usando a estrutura específica do CNIS
        nome_patterns = [
            # Padrão específico: NIT + CPF + Nome
            r'NIT[:\s]*[\d\.-]+\s+CPF[:\s]*\d{3}\.\d{3}\.\d{3}-\d{2}\s+Nome[:\s]*([A-ZÁÊÇÕ][A-ZÁÊÇÕa-záêçõ\s]+?)(?:\s+Data\s+de\s+nascimento|$)',
        ]
        
        for pattern in nome_patterns:
            match = re.search(pattern, text, re.IGNORECASE)
            if match:
                nome = match.group(1).strip()
                # Limpa caracteres indesejados
                nome = re.sub(r'[0-9\-\_\.\(\)\[\]]', '', nome)
                nome = re.sub(r'\s+', ' ', nome).strip()
                
                if len(nome) > 5 and ' ' in nome:
                    personal_data['nome'] = nome.title()
                    break
        
        # Padrões para data de nascimento
        nasc_patterns = [
            r'Data\s+de\s+nascimento[:\s]*(\d{2}/\d{2}/\d{4})',
        ]
        
        for pattern in nasc_patterns:
            match = re.search(pattern, text, re.IGNORECASE)
            if match:
                personal_data['data_nascimento'] = match.group(1)
                break
        
        return personal_data
    
    def extract_employment_data(self, text: str) -> List[Dict[str, str]]:
        """Extrai dados de vínculos empregatícios"""
        employments = []
        
        # Identifica datas que devem ser excluídas
        exclude_dates = set()
        
        # Data de nascimento
        nasc_match = re.search(r'Data\s+de\s+nascimento[:\s]*(\d{2}/\d{2}/\d{4})', text, re.IGNORECASE)
        if nasc_match:
            exclude_dates.add(nasc_match.group(1))
            logger.info(f"Data de nascimento identificada: {nasc_match.group(1)}")
        
        # Datas de geração do relatório
        report_dates = re.findall(r'(\d{2}/\d{2}/\d{4})\s+\d{2}:\d{2}:\d{2}', text)
        for date in report_dates:
            exclude_dates.add(date)
            logger.info(f"Data de geração do relatório identificada: {date}")
        
        # Divide o texto em seções de vínculos
        sections = self.split_into_employment_sections(text)
        
        for section in sections:
            employment = self.extract_employment_from_section(section, exclude_dates)
            if employment and employment.get('empregador'):
                employments.append(employment)
        
        return employments
    
    def split_into_employment_sections(self, text: str) -> List[str]:
        """Divide o texto em seções de vínculos empregatícios"""
        sections = []
        lines = text.split('\n')
        current_section = []
        
        for i, line in enumerate(lines):
            line = line.strip()
            
            # Identifica início de uma seção de vínculo (AMPLIADO para capturar todos)
            is_start_of_section = (
                # Padrão: número + CNPJ completo + nome da empresa
                re.match(r'^\d+\s+\d{2}\.\d{3}\.\d{3}/\d{4}-\d{2}\s+[A-Z]', line) or
                # Padrão: número + CNPJ incompleto (apenas 8 primeiros dígitos) + nome da empresa
                re.match(r'^\d+\s+\d{2}\.\d{3}\.\d{3}\s+[A-Z]', line) or
                # Padrão: número + "Indeterminado" + nome da empresa
                re.match(r'^\d+\s+Indeterminado\s+[A-Z]', line) or
                # Padrão: número + CNPJ + nome truncado (sem "Empregado")
                re.match(r'^\d+\s+\d{2}\.\d{3}\.\d{3}/\d{4}-\d{2}[A-Z]', line) or
                # Padrão: número + AGRUPAMENTO DE CONTRATANTES (vínculo real)
                re.match(r'^\d+\s+AGRUPAMENTO\s+DE\s+CONTRATANTES/COOPERATIVAS\s+Contribuinte\s+Individual', line)
            )
            
            if is_start_of_section:
                # Salva a seção anterior se existir
                if current_section:
                    sections.append('\n'.join(current_section))
                current_section = [line]
            elif current_section:
                current_section.append(line)
                
                # Identifica fim da seção
                if (re.match(r'^Relações\s+Previdenciárias', line) or
                    re.match(r'^Valores\s+Consolidados', line) or
                    re.match(r'^Legenda', line) or
                    re.match(r'^TOTAIS', line) or
                    # Nova seção começando
                    re.match(r'^\d+\s+\d{2}\.\d{3}\.\d{3}', line) or
                    re.match(r'^\d+\s+Indeterminado\s+', line) or
                    re.match(r'^\d+\s+AGRUPAMENTO\s+DE\s+CONTRATANTES/COOPERATIVAS\s+Contribuinte\s+Individual', line)):
                    
                    if (re.match(r'^\d+\s+\d{2}\.\d{3}\.\d{3}', line) or
                        re.match(r'^\d+\s+Indeterminado\s+', line) or
                        re.match(r'^\d+\s+AGRUPAMENTO\s+DE\s+CONTRATANTES/COOPERATIVAS\s+Contribuinte\s+Individual', line)):
                        # Nova seção começando, remove esta linha da atual
                        current_section.pop()
                        sections.append('\n'.join(current_section))
                        current_section = [line]
                    else:
                        sections.append('\n'.join(current_section))
                        current_section = []
        
        # Adiciona a última seção se existir
        if current_section:
            sections.append('\n'.join(current_section))
        
        return sections
    
    def extract_employment_from_section(self, section: str, exclude_dates: set) -> Optional[Dict[str, str]]:
        """Extrai dados de um vínculo empregatício de uma seção"""
        employment = {
            'empregador': '',
            'cnpj': '',
            'data_inicio': '',
            'data_fim': ''
        }
        
        lines = section.split('\n')
        
        # Extrai informações básicas da primeira linha
        first_line = lines[0].strip() if lines else ""
        
        # Padrão para linha com CNPJ completo e nome da empresa
        emp_match = re.match(r'^\d+\s+(\d{2}\.\d{3}\.\d{3}/\d{4}-\d{2})\s+(.+?)(?:Empregado|Contribuinte)', first_line)
        if emp_match:
            employment['cnpj'] = emp_match.group(1)
            empregador = emp_match.group(2).strip()
            employment['empregador'] = empregador
        
        # Padrão para linha com CNPJ incompleto (apenas 8 primeiros dígitos)
        if not employment['empregador']:
            emp_match = re.match(r'^\d+\s+(\d{2}\.\d{3}\.\d{3})\s+(.+?)(?:Empregado|Contribuinte)', first_line)
            if emp_match:
                employment['cnpj'] = emp_match.group(1)
                empregador = emp_match.group(2).strip()
                employment['empregador'] = empregador
        
        # Padrão para linha com "Indeterminado"
        if not employment['empregador']:
            emp_match = re.match(r'^\d+\s+Indeterminado\s+(.+?)(?:Empregado|Contribuinte)', first_line)
            if emp_match:
                employment['cnpj'] = 'Indeterminado'
                empregador = emp_match.group(1).strip()
                employment['empregador'] = empregador
        
        # Padrão para linha truncada (CNPJ + nome sem espaço)
        if not employment['empregador']:
            emp_match = re.match(r'^\d+\s+(\d{2}\.\d{3}\.\d{3}/\d{4}-\d{2})([A-Z].+)', first_line)
            if emp_match:
                employment['cnpj'] = emp_match.group(1)
                empregador = emp_match.group(2).strip()
                employment['empregador'] = empregador
        
        # Padrão para AGRUPAMENTO DE CONTRATANTES/COOPERATIVAS
        agrup_match = re.match(r'^\d+\s+AGRUPAMENTO\s+DE\s+CONTRATANTES/COOPERATIVAS\s+Contribuinte\s+Individual\s+(\d{2}/\d{2}/\d{4})\s+(\d{2}/\d{2}/\d{4})', first_line)
        if agrup_match:
            employment['empregador'] = "AGRUPAMENTO DE CONTRATANTES"
            employment['data_inicio'] = agrup_match.group(1)
            employment['data_fim'] = agrup_match.group(2)
            
            # Procura CNPJ nas linhas subsequentes
            for line in lines[1:]:
                cnpj_match = re.search(r'(\d{2}\.\d{3}\.\d{3}(?:/\d{4}-\d{2})?)', line)
                if cnpj_match:
                    employment['cnpj'] = cnpj_match.group(1)
                    break
        
        # Se não é agrupamento, extrai datas de vínculo da segunda linha
        if not agrup_match and len(lines) > 1:
            second_line = lines[1].strip()
            
            # Padrão: Público + data início + data fim + outras informações
            date_match = re.search(r'Público\s*(\d{2}/\d{2}/\d{4})\s+(\d{2}/\d{2}/\d{4}|\d{2}/\d{4})', second_line)
            if date_match:
                data_inicio = date_match.group(1)
                data_fim_raw = date_match.group(2)
                
                # Filtra datas que devem ser excluídas
                if data_inicio not in exclude_dates:
                    employment['data_inicio'] = data_inicio
                
                # Se a data fim é apenas MM/YYYY, converte para DD/MM/YYYY
                if re.match(r'^\d{2}/\d{4}$', data_fim_raw):
                    data_fim = self.convert_month_year_to_full_date(data_fim_raw)
                else:
                    data_fim = data_fim_raw
                    
                if data_fim not in exclude_dates:
                    employment['data_fim'] = data_fim
            else:
                # Padrão alternativo: apenas data de início
                date_match = re.search(r'Público\s*(\d{2}/\d{2}/\d{4})', second_line)
                if date_match:
                    data_inicio = date_match.group(1)
                    if data_inicio not in exclude_dates:
                        employment['data_inicio'] = data_inicio
                        employment['data_fim'] = ''  # Vínculo ativo, sem data fim
        
        # Se não encontrou as datas, procura em outras linhas (apenas para vínculos normais)
        if not employment['data_inicio'] and not agrup_match:
            for line in lines[1:]:
                line = line.strip()
                
                # Procura por padrões de datas
                dates_in_line = re.findall(r'\d{2}/\d{2}/\d{4}', line)
                
                # Filtra datas que devem ser excluídas
                valid_dates = [d for d in dates_in_line if d not in exclude_dates]
                
                if len(valid_dates) >= 2:
                    employment['data_inicio'] = valid_dates[0]
                    employment['data_fim'] = valid_dates[1]
                    break
                elif len(valid_dates) == 1:
                    employment['data_inicio'] = valid_dates[0]
                    employment['data_fim'] = ''
                    break
        
        # Validação final: se não tem empregador, não é um vínculo válido
        if not employment['empregador']:
            return None
        
        # Filtro para excluir itens que NÃO são vínculos empregatícios
        empregador_upper = employment['empregador'].upper()
        if (re.search(r'AUXILIO\s+DOENCA|APOSENTADORIA|BENEFICIO', empregador_upper) or
            re.search(r'^\d+\s*-\s*', empregador_upper)):
            return None
        
        # Limpa empregador
        employment['empregador'] = employment['empregador'].strip()
        
        # Para vínculos sem data fim, garantir que está em branco
        if not employment['data_fim']:
            employment['data_fim'] = ''
        
        return employment
    
    def convert_month_year_to_full_date(self, month_year: str) -> str:
        """Converte MM/YYYY para DD/MM/YYYY (último dia do mês)"""
        import datetime
        
        match = re.match(r'(\d{2})/(\d{4})', month_year)
        if match:
            try:
                month = int(match.group(1))
                year = int(match.group(2))
                
                # Validação do mês
                if month < 1 or month > 12:
                    logger.warning(f"Mês inválido: {month} em {month_year}")
                    return month_year
                
                # Último dia do mês
                if month == 12:
                    last_day = datetime.date(year + 1, 1, 1) - datetime.timedelta(days=1)
                else:
                    last_day = datetime.date(year, month + 1, 1) - datetime.timedelta(days=1)
                
                return last_day.strftime('%d/%m/%Y')
            except (ValueError, OverflowError) as e:
                logger.warning(f"Erro ao converter data {month_year}: {e}")
                return month_year
        
        return month_year
    
    def process_cnis(self, pdf_path: str) -> Dict[str, Any]:
        """Processa o arquivo CNIS e extrai todos os dados"""
        try:
            logger.info(f"Processando arquivo: {pdf_path}")
            
            # Extrai texto do PDF
            text = self.extract_text_from_pdf(pdf_path)
            
            if not text.strip():
                return {
                    'success': False,
                    'error': 'Não foi possível extrair texto do PDF'
                }
            
            # Extrai dados
            personal_data = self.extract_personal_data(text)
            employment_data = self.extract_employment_data(text)
            
            # Mapeia os dados para o formato esperado
            result_data = {
                'client_name': personal_data.get('nome', ''),
                'client_cpf': personal_data.get('cpf', ''),
                'vinculos_empregaticios': employment_data
            }
            
            result = {
                'success': True,
                'data': result_data,
                'text_length': len(text)
            }
            
            logger.info(f"Extraídos {len(employment_data)} vínculos empregatícios")
            logger.info(f"Nome do cliente: {result_data['client_name']}")
            return result
            
        except Exception as e:
            logger.error(f"Erro no processamento: {e}")
            return {
                'success': False,
                'error': str(e)
            }

def main():
    """Função principal para execução via linha de comando"""
    parser = argparse.ArgumentParser(description='Extrator de dados do CNIS - Versão Simplificada')
    parser.add_argument('pdf_path', help='Caminho para o arquivo PDF do CNIS')
    parser.add_argument('--output', help='Arquivo de saída JSON (opcional)')
    
    args = parser.parse_args()
    
    # Verifica se o arquivo existe
    if not Path(args.pdf_path).exists():
        print(f"Erro: Arquivo não encontrado - {args.pdf_path}")
        sys.exit(1)
    
    # Processa o CNIS
    extractor = CNISExtractorSimple()
    result = extractor.process_cnis(args.pdf_path)
    
    # Saída
    if args.output:
        with open(args.output, 'w', encoding='utf-8') as f:
            json.dump(result, f, ensure_ascii=False, indent=2)
        print(f"Resultado salvo em: {args.output}")
    else:
        print(json.dumps(result, ensure_ascii=False, indent=2))

if __name__ == "__main__":
    main() 