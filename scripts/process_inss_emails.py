import imaplib
import email
import re
import mysql.connector
from datetime import datetime
from email.header import decode_header
import os
from dotenv import load_dotenv
import logging
from email.utils import parsedate_to_datetime

# Configurar logging
logging.basicConfig(
    level=logging.DEBUG,
    format='%(asctime)s - %(levelname)s - %(message)s',
    handlers=[
        logging.FileHandler('/var/www/previdia.com.br/storage/logs/inss_emails.log'),
        logging.StreamHandler()
    ]
)
logger = logging.getLogger(__name__)

# Carrega as variáveis de ambiente do arquivo .env
load_dotenv()
logger.info("Iniciando processamento de emails")

# Configurações do email
EMAIL_USER = "intimacoes@previdia.com"
EMAIL_PASSWORD = "Nova365@"
IMAP_SERVER = "imap.kinghost.net"
IMAP_PORT = 993

# Configurações do banco de dados
DB_HOST = os.getenv('DB_HOST', '127.0.0.1')
DB_USER = os.getenv('DB_USERNAME', 'root')
DB_PASS = os.getenv('DB_PASSWORD', '')
DB_NAME = os.getenv('DB_DATABASE', 'laravel')

def conectar_banco():
    logger.info("Tentando conectar ao banco de dados")
    try:
        conn = mysql.connector.connect(
            host=DB_HOST,
            user=DB_USER,
            password=DB_PASS,
            database=DB_NAME
        )
        logger.info("Conexão com banco de dados estabelecida com sucesso")
        return conn
    except Exception as e:
        logger.error(f"Erro ao conectar ao banco de dados: {str(e)}")
        raise

def extrair_protocolo(texto):
    logger.debug(f"Tentando extrair protocolo do texto: {texto[:200]}...")  # Primeiros 200 caracteres
    
    # Vários padrões possíveis para encontrar o protocolo
    padroes = [
        r'requerimento\s*[nº.:]*\s*(\d+)',  # Primeiro padrão para capturar do assunto
        r'protocolo\s*[nº.:]*\s*(\d+)',
        r'processo\s*[nº.:]*\s*(\d+)',
        r'número\s*[.:]*\s*(\d+)',
        r'#\s*(\d+)',
        r'[\[\(](\d{6,}?)[\]\)]'  # Números com 6+ dígitos entre colchetes ou parênteses
    ]
    
    for padrao in padroes:
        match = re.search(padrao, texto.lower())
        if match:
            protocolo = match.group(1)
            logger.info(f"Protocolo encontrado: {protocolo}")
            return protocolo
            
    # Se não encontrou com os padrões acima, procura por qualquer número com 6 ou mais dígitos
    numeros = re.findall(r'\d{6,}', texto)
    if numeros:
        protocolo = numeros[0]
        logger.info(f"Protocolo encontrado (número longo): {protocolo}")
        return protocolo
        
    logger.warning("Nenhum protocolo encontrado no texto")
    return None

def get_email_content(msg):
    """Extrai o conteúdo do email de forma mais robusta, focando no despacho do INSS"""
    content = []
    
    if msg.is_multipart():
        # Se o email tem múltiplas partes, processa cada parte
        for part in msg.walk():
            if part.get_content_maintype() == 'multipart':
                continue
            if part.get('Content-Disposition') is not None:
                continue
            
            # Tenta obter o conteúdo da parte
            try:
                if part.get_content_type() == 'text/html':
                    payload = part.get_payload(decode=True)
                    if payload:
                        charset = part.get_content_charset() or 'utf-8'
                        try:
                            decoded_content = payload.decode(charset)
                            # Remove scripts e estilos
                            decoded_content = re.sub(r'<script[^>]*>.*?</script>', '', decoded_content, flags=re.DOTALL)
                            decoded_content = re.sub(r'<style[^>]*>.*?</style>', '', decoded_content, flags=re.DOTALL)
                            
                            # Procura por texto entre tags HTML comuns em emails
                            tags_to_extract = ['p', 'td', 'div', 'span', 'li']
                            for tag in tags_to_extract:
                                matches = re.findall(f'<{tag}[^>]*>(.*?)</{tag}>', decoded_content, re.DOTALL)
                                for match in matches:
                                    # Remove todas as tags HTML restantes
                                    clean_text = re.sub(r'<[^>]+>', '', match)
                                    # Remove caracteres especiais HTML
                                    clean_text = re.sub(r'&nbsp;', ' ', clean_text)
                                    clean_text = re.sub(r'&[a-zA-Z]+;', '', clean_text)
                                    # Remove espaços extras e quebras de linha
                                    clean_text = ' '.join(clean_text.split())
                                    if clean_text and len(clean_text.strip()) > 10:  # Ignora textos muito curtos
                                        content.append(clean_text.strip())
                        except Exception as e:
                            logger.error(f"Erro ao processar HTML: {str(e)}")
                            decoded_content = payload.decode(charset, 'replace')
                            content.append(decoded_content)
                elif part.get_content_type() == 'text/plain':
                    payload = part.get_payload(decode=True)
                    if payload:
                        charset = part.get_content_charset() or 'utf-8'
                        try:
                            decoded_content = payload.decode(charset)
                            content.append(decoded_content)
                        except:
                            decoded_content = payload.decode(charset, 'replace')
                            content.append(decoded_content)
            except Exception as e:
                logger.error(f"Erro ao decodificar parte do email: {str(e)}")
                continue
    else:
        # Se o email é texto simples
        try:
            payload = msg.get_payload(decode=True)
            if payload:
                charset = msg.get_content_charset() or 'utf-8'
                try:
                    decoded_content = payload.decode(charset)
                    content.append(decoded_content)
                except:
                    decoded_content = payload.decode(charset, 'replace')
                    content.append(decoded_content)
        except Exception as e:
            logger.error(f"Erro ao decodificar email simples: {str(e)}")
            content.append(msg.get_payload())

    # Junta todo o conteúdo e limpa
    full_content = "\n".join(content)
    
    # Remove linhas vazias extras e espaços
    clean_lines = []
    for line in full_content.splitlines():
        line = line.strip()
        if line:
            # Remove linhas que são apenas pontuação ou caracteres especiais
            if not re.match(r'^[\s\W]+$', line):
                clean_lines.append(line)
    
    # Remove duplicatas mantendo a ordem
    seen_normalized = {}  # Dicionário para armazenar versões normalizadas das linhas
    unique_lines = []
    
    for line in clean_lines:
        # Normaliza a linha para comparação (remove pontuação e converte para minúsculas)
        normalized = re.sub(r'[^\w\s]', '', line.lower())
        normalized = ' '.join(normalized.split())  # Remove espaços extras
        
        # Se a linha normalizada não foi vista antes ou é uma linha especial (protocolo, serviço, etc)
        if normalized not in seen_normalized or any(key in line for key in ['Protocolo:', 'Serviço:', 'Data do Protocolo:', 'Unidade responsável:', 'Status atual:']):
            seen_normalized[normalized] = True
            unique_lines.append(line)
    
    # Organiza o conteúdo em seções
    organized_content = []
    metadata = []
    despacho = []
    despacho_seen = set()  # Para controlar duplicação do despacho
    
    # Lista de informações para omitir
    skip_patterns = [
        'INSS - INSTITUTO NACIONAL DO SEGURO SOCIAL',
        'Unidade responsável:',
        'Serviço:',
        'Requerimento'
    ]
    
    for line in unique_lines:
        # Verifica se a linha deve ser omitida
        should_skip = any(pattern in line for pattern in skip_patterns)
        if should_skip:
            continue
            
        if any(key in line for key in ['Protocolo:', 'Data do Protocolo:', 'Status atual:']):
            metadata.append(line)
        elif 'Esta é uma mensagem automática' in line:
            continue  # Ignora a mensagem automática
        else:
            # Normaliza o texto do despacho para comparação
            despacho_normalized = re.sub(r'[^\w\s]', '', line.lower())
            despacho_normalized = ' '.join(despacho_normalized.split())
            
            # Verifica se é uma saudação ou parte do despacho
            if 'prezado' in despacho_normalized or 'prezada' in despacho_normalized:
                if 'prezado' not in despacho_seen and 'prezada' not in despacho_seen:
                    despacho_seen.add('prezado')
                    despacho.append(line)
            else:
                # Adiciona apenas se não for uma duplicata do despacho
                similar_found = False
                for existing in despacho_seen:
                    # Calcula a similaridade entre os textos
                    if len(despacho_normalized) > 20 and (
                        despacho_normalized in existing or 
                        existing in despacho_normalized or
                        (len(set(despacho_normalized.split()) & set(existing.split())) / len(set(despacho_normalized.split() + existing.split()))) > 0.8
                    ):
                        similar_found = True
                        break
                
                if not similar_found:
                    despacho_seen.add(despacho_normalized)
                    despacho.append(line)
    
    # Monta o conteúdo final na ordem desejada
    final_content = metadata + despacho
    
    result = "\n".join(final_content)
    logger.debug(f"Conteúdo limpo do email: {result[:200]}...")
    return result

def processar_email(msg):
    logger.info("Processando novo email")
    
    # Extrair assunto
    subject = ""
    if msg["subject"]:
        subject_bytes, encoding = decode_header(msg["subject"])[0]
        if isinstance(subject_bytes, bytes):
            subject = subject_bytes.decode(encoding if encoding else "utf-8")
        else:
            subject = subject_bytes
    logger.debug(f"Assunto do email: {subject}")

    # Extrair data de recebimento usando email.utils.parsedate_to_datetime
    try:
        date_received = parsedate_to_datetime(msg["date"])
        logger.debug(f"Data de recebimento: {date_received}")
    except Exception as e:
        logger.error(f"Erro ao processar data {msg['date']}: {str(e)}")
        date_received = datetime.now()

    # Extrair corpo do email usando a nova função
    corpo = get_email_content(msg)
    logger.debug(f"Corpo do email (primeiros 200 caracteres): {corpo[:200]}...")
    
    # Extrair protocolo do assunto ou do corpo
    protocolo = None
    if subject:
        protocolo = extrair_protocolo(subject)
    if not protocolo and corpo:
        protocolo = extrair_protocolo(corpo)

    # Extrair serviço do corpo
    servico = None
    for linha in corpo.split('\n'):
        if 'Serviço:' in linha:
            servico = linha.split('Serviço:')[1].strip()
            break
    
    # Gerar um ID único para o email
    email_id = f"{protocolo}_{date_received.strftime('%Y%m%d%H%M%S')}"
    
    return {
        'protocolo': protocolo,
        'assunto': subject,
        'conteudo': corpo,
        'data_recebimento': date_received,
        'servico': servico,
        'email_id': email_id
    }

def salvar_email(conn, dados):
    if not dados['protocolo']:
        logger.warning("Email descartado: sem número de protocolo")
        return False

    cursor = conn.cursor()
    try:
        # Verificar se o protocolo já existe na tabela de despachos
        cursor.execute("SELECT id FROM despachos WHERE protocolo = %s", (dados['protocolo'],))
        if cursor.fetchone():
            # Atualizar o registro existente
            sql = """UPDATE despachos 
                    SET conteudo = %s, data_email = %s, servico = %s, updated_at = NOW()
                    WHERE protocolo = %s"""
            cursor.execute(sql, (
                dados['conteudo'],
                dados['data_recebimento'],
                dados['servico'],
                dados['protocolo']
            ))
            logger.info(f"Despacho com protocolo {dados['protocolo']} atualizado")
        else:
            # Inserir novo registro
            sql = """INSERT INTO despachos 
                    (protocolo, conteudo, data_email, servico, email_id, created_at, updated_at) 
                    VALUES (%s, %s, %s, %s, %s, NOW(), NOW())"""
            cursor.execute(sql, (
                dados['protocolo'],
                dados['conteudo'],
                dados['data_recebimento'],
                dados['servico'],
                dados['email_id']
            ))
            logger.info(f"Despacho com protocolo {dados['protocolo']} inserido")
            
        conn.commit()
        return True
    except mysql.connector.Error as err:
        logger.error(f"Erro ao salvar despacho: {err}")
        return False
    finally:
        cursor.close()

def main():
    try:
        # Conectar ao servidor IMAP
        logger.info(f"Conectando ao servidor IMAP {IMAP_SERVER}:{IMAP_PORT}")
        imap = imaplib.IMAP4_SSL(IMAP_SERVER, IMAP_PORT)
        imap.login(EMAIL_USER, EMAIL_PASSWORD)
        logger.info("Conectado ao servidor IMAP com sucesso")
        
        # Selecionar a caixa de entrada
        imap.select('INBOX')
        logger.info("Caixa de entrada selecionada")

        # Buscar todos os emails do INSS dos últimos 30 dias
        status, mensagens = imap.search(None, '(FROM "noreply@inss.gov.br" SINCE "9-Jun-2025")')
        if status != 'OK' or not mensagens[0]:
            logger.warning("Nenhum email encontrado ou erro na busca")
            return
            
        msg_nums = mensagens[0].split()
        num_mensagens = len(msg_nums)
        logger.info(f"Encontrados {num_mensagens} emails do INSS")
        
        # Conectar ao banco de dados
        conn = conectar_banco()
        
        # Processar cada email
        emails_processados = 0
        for num in msg_nums:
            if not isinstance(num, bytes):
                num = str(num).encode('utf-8')
                
            logger.info(f"Processando email {num}")
            status, msg_data = imap.fetch(num, '(RFC822)')
            if status != 'OK' or not msg_data or not msg_data[0]:
                logger.error(f"Erro ao buscar email {num}")
                continue
                
            email_body = msg_data[0][1]
            if not isinstance(email_body, bytes):
                logger.error(f"Corpo do email {num} não é bytes")
                continue
                
            msg = email.message_from_bytes(email_body)
            
            # Processar e salvar o email
            dados = processar_email(msg)
            if salvar_email(conn, dados):
                emails_processados += 1
                logger.info("Email salvo com sucesso")
        
        logger.info(f"Total de emails processados: {emails_processados}")
        
        # Fechar conexões
        conn.close()
        imap.logout()
        logger.info("Processamento concluído com sucesso")

    except Exception as e:
        logger.error(f"Erro durante a execução: {str(e)}")
        raise

if __name__ == "__main__":
    main() 