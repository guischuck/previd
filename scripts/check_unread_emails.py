import imaplib
import email
from email.header import decode_header

# Configurações do email
EMAIL_USER = "intimacoes@previdia.com"
EMAIL_PASSWORD = "Nova365@"
IMAP_SERVER = "imap.kinghost.net"
IMAP_PORT = 993

def decode_subject(subject):
    if subject is None:
        return ""
    decoded_header = decode_header(subject)
    subject_parts = []
    for content, encoding in decoded_header:
        if isinstance(content, bytes):
            try:
                subject_parts.append(content.decode(encoding if encoding else 'utf-8'))
            except:
                subject_parts.append(content.decode('utf-8', 'ignore'))
        else:
            subject_parts.append(str(content))
    return " ".join(subject_parts)

def main():
    print("Conectando ao servidor IMAP...")
    imap = imaplib.IMAP4_SSL(IMAP_SERVER, IMAP_PORT)
    imap.login(EMAIL_USER, EMAIL_PASSWORD)
    
    print("\nCaixas de email disponíveis:")
    status, mailboxes = imap.list()
    for mailbox in mailboxes:
        print(mailbox.decode())
    
    print("\nSelecionando INBOX...")
    imap.select('INBOX')
    
    print("\nBuscando emails não lidos do INSS...")
    _, mensagens = imap.search(None, '(UNSEEN FROM "noreply@inss.gov.br")')
    
    if not mensagens[0]:
        print("Nenhum email não lido do INSS encontrado.")
        return
    
    print(f"\nEncontrados {len(mensagens[0].split())} emails não lidos do INSS:")
    for num in mensagens[0].split():
        _, msg_data = imap.fetch(num, '(RFC822)')
        email_body = msg_data[0][1]
        msg = email.message_from_bytes(email_body)
        
        subject = decode_subject(msg["subject"])
        from_addr = msg["from"]
        date = msg["date"]
        
        print(f"\nEmail #{num.decode()}:")
        print(f"De: {from_addr}")
        print(f"Data: {date}")
        print(f"Assunto: {subject}")
        print("-" * 50)
    
    imap.close()
    imap.logout()

if __name__ == "__main__":
    main() 