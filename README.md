Plataforma de Migração e ETL de Dados
📖 Sobre o Projeto
Este projeto é uma poderosa e flexível Plataforma de Migração e ETL (Extração, Transformação e Carga), desenvolvida do zero para lidar com cenários complexos de migração de dados entre diferentes sistemas e bancos de dados (MySQL, SQL Server, etc.).

O sistema foi arquitetado para ser totalmente configurável através de uma interface web intuitiva, permitindo que usuários técnicos e administradores configurem, executem e monitorem processos de migração complexos sem a necessidade de escrever código customizado para cada caso. A ferramenta foi refatorada de um conjunto de scripts procedurais para uma aplicação PHP moderna, orientada a objetos e com uma arquitetura robusta e escalável.

✨ Funcionalidades Principais
Esta plataforma não é apenas um migrador, mas uma suíte completa de ferramentas de gerenciamento de dados.

1. Motor de Migração Dinâmico
O coração do sistema é um motor de migração genérico que opera com base em "mapas" de configuração.

Múltiplas Tabelas: Execute a migração de dezenas de tabelas em uma única operação, com a ordem e dependências respeitadas.

Processamento em Lotes: Os dados são lidos e inseridos em lotes configuráveis, garantindo alta performance e baixo consumo de memória, mesmo com milhões de registros.

Controle de Processo: Inicie, cancele e monitore o progresso de cada migração em tempo real.

[Imagem do Painel de Configuração de Migração]

2. Mapeamento Avançado de Colunas
A interface permite a criação de regras de transformação complexas para cada campo, diretamente no navegador:

Mapeamento Direto: [Origem.NOME] -> [Destino.nome_cliente]

Valores Padrão: Defina valores fixos ou nulos para colunas que não existem na origem.

Geração de Siglas: Crie siglas e chaves primárias únicas automaticamente a partir de outros campos.

Auto-incremento Customizado: Gerencie chaves primárias numéricas com valores iniciais e sequências personalizadas.

Sub-consultas: Busque dados em tabelas relacionadas na origem para enriquecer os dados da tabela principal.

Busca De-Para: Resolva chaves estrangeiras mapeando IDs do sistema antigo para os novos IDs no sistema de destino através de uma tabela de mapeamento.

Concatenação de Campos: Junte múltiplos campos da origem em um único campo no destino, com separadores customizados.

[Imagem do Modal de Mapeamento Avançado]

3. Importador de CSV
Uma funcionalidade poderosa que permite ao usuário importar dados para qualquer banco de dados a partir de um conjunto de arquivos .csv.

Upload de Arquivos .zip: O usuário pode subir múltiplos CSVs de uma só vez.

Criação Dinâmica de Tabelas: O sistema analisa os CSVs e cria automaticamente as tabelas e colunas no banco de destino, inferindo os tipos de dados.

Limpeza e Tratamento de Dados: Funções robustas para corrigir problemas de codificação (UTF-8), remover tags HTML e limpar dados inconsistentes.

[Imagem da Página do Importador de CSV]

4. Dashboard de Monitoramento e BI
Um painel de controle central que oferece uma visão completa da saúde e do histórico das migrações.

KPIs em Tempo Real: Monitore o total de migrações, registros processados, taxa de sucesso e tempo médio.

Gráfico de Desempenho: Visualize um comparativo entre a quantidade de registros na origem e no destino para cada tabela migrada.

Logs Detalhados: Uma tabela com o log completo de cada etapa do processo, com filtros por cliente, nível (INFO, ERROR) e evento.

[Imagem do Dashboard com Gráficos e Logs]

5. Gerenciamento de Usuários e Permissões
A aplicação conta com um sistema de autenticação e controle de acesso baseado em papéis (RBAC).

Níveis de Acesso: Perfis de Administrador, Técnico e Consultor.

Segurança: Senhas armazenadas com hash seguro (PASSWORD_DEFAULT).

Gerenciamento via UI: Administradores podem criar e gerenciar usuários através da interface.

🏛️ Arquitetura e Padrões de Projeto
Este projeto foi construído seguindo os princípios de design de software moderno para garantir que seja robusto, seguro e fácil de manter.

Back-end (PHP 8+):

Padrão MVC-like: A lógica é separada em Controllers (que orquestram as requisições), Models (Entidades e Repositórios que lidam com os dados) e Views (templates PHP puros).

Front Controller: Todas as requisições passam por um único ponto de entrada (public/index.php), que gerencia o roteamento para páginas web e para a API.

Injeção de Dependência: As classes recebem suas dependências (como a conexão com o banco) através de seus construtores, tornando o código desacoplado e testável.

Camada de Serviço: Lógicas de negócio complexas (como o motor de migração e o importador de CSV) são encapsuladas em suas próprias classes de Serviço.

Padrão Repository: A interação com o banco de dados é abstraída através de Repositórios, que retornam objetos de Entidade.

Front-end (JavaScript/jQuery):

UI Dinâmica: A interface é altamente interativa, utilizando AJAX para buscar dados e atualizar componentes da página sem a necessidade de recarregamentos.

Componentização: A interface é construída com componentes reutilizáveis (painéis de tabela, modais), permitindo adicionar novas funcionalidades de forma rápida e consistente.

Tema AdminLTE 3: O layout é baseado no robusto e profissional tema AdminLTE 3, adaptado para um tema escuro.

API:

RESTful: O back-end expõe uma API RESTful coesa que o front-end consome. As ações são mapeadas para os verbos HTTP corretos (GET, POST, PUT, DELETE).

🛠️ Tecnologias Utilizadas
Back-end: PHP 8.1+

Banco de Dados: MySQL (com lógica adaptável para SQL Server via PDO)

Front-end: HTML5, CSS3, JavaScript (ES6+), jQuery, Bootstrap 5

Tema: AdminLTE 3

Dependências: Composer, DotEnv

🚀 Instalação e Execução
Clone o repositório: git clone ...

Instale as dependências do PHP: composer install

Configure suas variáveis de ambiente: copie .env.example para .env e preencha com os dados do seu banco de dados da aplicação.

Configure seu servidor web (Apache, Nginx) para apontar a raiz para a pasta public/.

Acesse a aplicação no seu navegador.

👨‍💻 Autor
[Roberval Martins Molina]

LinkedIn: www.linkedin.com/in/roberval-molina-862a4a135

GitHub: https://github.com/RoberMM

Email: [progrober@gmail.com]