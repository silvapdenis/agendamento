# Sistema de Agendamento Médico - SaaS

Sistema SaaS completo para clínicas médicas desenvolvido com Laravel 11 e Vue 3, incluindo automações com n8n e integração WhatsApp.

## Estrutura do Projeto

```
meu-projeto
├── app                  # Lógica de negócios da aplicação
├── bootstrap             # Arquivos de inicialização
├── config               # Configurações da aplicação
├── database             # Migrações, seeds e fábricas
├── public               # Ponto de entrada da aplicação
│   └── index.php
├── resources            # Recursos da aplicação
│   ├── js              # JavaScript da aplicação
│   │   └── app.js
│   ├── views           # Views Blade
│   │   └── welcome.blade.php
├── routes              # Definição de rotas
│   └── web.php
├── storage             # Armazenamento de arquivos gerados
├── tests               # Testes automatizados
├── artisan             # Console de comandos do Laravel
├── composer.json       # Configuração do Composer
├── package.json        # Configuração do npm
├── webpack.mix.js      # Configuração do Laravel Mix
└── README.md           # Documentação do projeto
```

## Instalação

1. Clone o repositório:
   ```
   git clone https://github.com/seu-usuario/meu-projeto.git
   ```

2. Navegue até o diretório do projeto:
   ```
   cd meu-projeto
   ```

3. Instale as dependências do Composer:
   ```
   composer install
   ```

4. Instale as dependências do npm:
   ```
   npm install
   ```

5. Configure o arquivo `.env` com suas credenciais de banco de dados.

6. Execute as migrações:
   ```
   php artisan migrate
   ```

7. Inicie o servidor de desenvolvimento:
   ```
   php artisan serve
   ```

## Uso

Acesse a aplicação em `http://localhost:8000` para ver a página inicial.

## Contribuição

Sinta-se à vontade para contribuir com melhorias e correções. Crie um fork do repositório e envie um pull request.

## Licença

Este projeto está licenciado sob a MIT License. Veja o arquivo LICENSE para mais detalhes.