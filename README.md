![Sistema Escolar — Amancio.dev](.github/assets/cover.svg?v=a660fd56fe)

<div align="center">

# Sistema Escolar

**Gestão acadêmica com matrículas, notas, frequência e portal do aluno**

![PHP](https://img.shields.io/badge/PHP-8.3%2B-777BB4?style=flat-square&logo=php&logoColor=white)
![Laravel](https://img.shields.io/badge/Laravel-13-FF2D20?style=flat-square&logo=laravel&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-8%2B-4479A1?style=flat-square&logo=mysql&logoColor=white)
![JavaScript](https://img.shields.io/badge/JavaScript-F7DF1E?style=flat-square&logo=javascript&logoColor=111827)

</div>

[Visão geral](#visão-geral) · [Tecnologias](#tecnologias) · [Funcionalidades](#funcionalidades) · [Decisões técnicas](#decisões-técnicas) · [Como executar](#como-executar) · [Testes](#testes) · [Estrutura](#estrutura)

## Visão geral

O Sistema Escolar organiza cadastros acadêmicos, matrículas, notas e frequência em uma aplicação Laravel. A interface administrativa atende a gestão escolar, enquanto o aluno possui uma área para consultar suas próprias informações.

O projeto reúne práticas de desenvolvimento back-end como modelagem relacional, validações no servidor, autorização por perfil, integridade de registros e testes de funcionalidades.

## Tecnologias

| Camada | Tecnologias e uso |
| --- | --- |
| Back-end | PHP 8.3+, Laravel 13 e Eloquent ORM |
| Banco de dados | MySQL 8+, migrations e seeder |
| Interface | Templates Blade, CSS e JavaScript |
| Assets | Vite para desenvolvimento e build |
| Testes | PHPUnit e recursos de teste do Laravel |

## Funcionalidades

- Dashboard com indicadores e atividades recentes.
- Cadastro, consulta, pesquisa, edição e exclusão de alunos, professores, turmas, disciplinas e matrículas.
- Alocação de professores por turma e disciplina.
- Lançamento de notas, registro de frequência e chamada em lote.
- Painel de frequência com filtros, totais e resumo de presenças e faltas por aluno.
- Portal para consulta das próprias notas e frequência.
- Criação de conta interna ao cadastrar aluno ou professor.
- Validação no servidor, mensagens de erro e restrições à exclusão de registros com vínculos.
- API em `/api` com acesso autenticado e permissões por perfil.
- Interface responsiva com navegação móvel e vermelho como cor de destaque.

### Perfis de acesso

| Perfil | Acesso |
| --- | --- |
| Administrador | Gestão dos cadastros acadêmicos, turmas, alocações, matrículas, notas e frequência |
| Professor | Consulta de turmas, notas, frequência e chamada em lote, conforme os vínculos e as regras de autorização |
| Aluno | Próprio painel, perfil, notas e frequência |

O cadastro público cria uma conta de aluno. Os perfis de administrador e professor não são concedidos pelo formulário público. As restrições também são aplicadas no servidor quando uma rota é acessada diretamente.

## Decisões técnicas

- **Autorização em rotas e consultas:** middlewares verificam autenticação, conta ativa e perfil; as consultas do portal usam o vínculo do aluno autenticado.
- **Integridade acadêmica:** validações e testes tratam duplicidade de matrícula, ano letivo, turma ativa, capacidade da turma e vínculos entre registros.
- **Chamada em lote:** o fluxo utiliza a lista de alunos ativos da turma; a suíte inclui atualização da mesma chamada sem duplicar registros e restrição aos vínculos do professor.
- **Páginas e API:** controllers compartilham operações entre formulários Blade e respostas JSON. A API utiliza a mesma sessão autenticada do portal e possui rotas separadas por permissões.

## Como executar

### Pré-requisitos

- PHP 8.3 ou superior
- Composer
- Node.js 20.19+ ou 22.12+, conforme as dependências Vite
- MySQL 8 ou superior

### Instalação local

~~~bash
git clone https://github.com/amancio-dev/sistemaEscolar.git
cd sistemaEscolar

composer install
cp .env.example .env
php artisan key:generate
~~~

Configure as credenciais e o banco de dados no arquivo `.env`. Em seguida:

~~~bash
php artisan migrate
npm install
npm run build
php artisan serve
~~~

Acesse [http://127.0.0.1:8000](http://127.0.0.1:8000).

Durante o desenvolvimento, também é possível usar:

~~~bash
composer run dev
~~~

O projeto possui dados de demonstração em `database/seeders/DatabaseSeeder.php`. Consulte o arquivo antes de utilizá-los em um banco local de testes.

## Testes

~~~bash
php artisan test
~~~

Entre os cenários documentados pelo código de teste estão:

| Área | Exemplos na suíte |
| --- | --- |
| Acesso e portal | `AccessControlTest.php`, `StudentAccessTest.php` |
| Privacidade da API | `AcademicApiPrivacyTest.php`: campos retornados por registros e relacionamentos |
| Integridade acadêmica | `AcademicRecordIntegrityTest.php`, `MatriculaIntegrityTest.php` |
| Chamada e vínculos docentes | `BatchAttendanceTest.php`, `TeacherAllocationTest.php` |

A suíte está em `tests/Feature`. Execute o comando para verificar o resultado da versão instalada no seu ambiente.

## Estrutura

| Caminho | Responsabilidade |
| --- | --- |
| `resources/views/layouts/app.blade.php` | Layout compartilhado, menu lateral e barra superior |
| `resources/views/dashboard/index.blade.php` | Página inicial com indicadores |
| `resources/views/dashboard/aluno.blade.php` | Painel do estudante |
| `resources/views/portal` | Consultas de notas e frequência do aluno autenticado |
| `resources/views/{modulo}/index.blade.php` | Consulta e pesquisa de registros |
| `resources/views/{modulo}/create.blade.php` | Página de cadastro |
| `resources/views/{modulo}/edit.blade.php` | Página de edição |
| `resources/views/{modulo}/_form.blade.php` | Campos compartilhados entre cadastro e edição |
| `resources/views/components` | Mensagens, cabeçalhos, status e paginação |
| `resources/css/app.css` | Tema visual responsivo |
| `resources/js/app.js` | Menu móvel, confirmação de exclusão e máscaras de campos |
| `routes/web.php` | Páginas Blade e operações de formulários |
| `routes/api.php` | Endpoints JSON para integrações |
| `app/Http/Controllers/CrudController.php` | Operações compartilhadas entre páginas e API |
| `database/migrations` | Estrutura e evolução do banco |
| `tests/Feature` | Testes de funcionalidades |

Os módulos `alunos`, `professores`, `turmas`, `disciplinas`, `matriculas`, `notas` e `frequencias` possuem views próprias. As páginas são renderizadas em Blade; a navegação entre módulos não depende de JavaScript para substituir o conteúdo de uma página única.
