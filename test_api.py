import requests
import json
import base64

# Configurações da API
API_KEY = "Cu3xUFd0EA6ZgM8RdqvLT9lYV0c1UGjONTsb2PlBZh1e2mx6pC8JdjhWHVSh"
BASE_URL = "https://api.softwareadvbox.com.br"

# Criando o header de autenticação Basic
auth_string = f"{API_KEY}:"  # Adicionando : no final como senha vazia
auth_bytes = auth_string.encode('ascii')
auth_b64 = base64.b64encode(auth_bytes).decode('ascii')

headers = {
    "Authorization": f"Basic {auth_b64}",
    "Content-Type": "application/json",
    "Accept": "application/json"
}

def test_connection():
    try:
        # Tentando fazer uma requisição GET para a raiz da API
        print("Headers:", json.dumps(headers, indent=2))
        print(f"Fazendo requisição para: {BASE_URL}")
        
        response = requests.get(
            BASE_URL,
            headers=headers,
            timeout=30,
            verify=True
        )
        
        print(f"\nStatus Code: {response.status_code}")
        print("Response Headers:", json.dumps(dict(response.headers), indent=2))
        
        # Tentando imprimir o corpo da resposta de forma mais legível
        try:
            json_response = response.json()
            print("Response Body (JSON):", json.dumps(json_response, indent=2))
        except:
            print("Response Body (Text):", response.text[:500])  # Limitando a 500 caracteres
            
        return response.status_code == 200
    except requests.exceptions.RequestException as e:
        print(f"\nErro na requisição: {str(e)}")
        return False
    except Exception as e:
        print(f"\nErro inesperado: {str(e)}")
        return False

if __name__ == "__main__":
    print("Testando conexão com a API usando autenticação Basic...")
    test_connection() 