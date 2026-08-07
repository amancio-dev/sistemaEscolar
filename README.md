# Sistema Escolar

Sistema acadêmico desenvolvido com Laravel 13, PHP e JavaScript. A interface administrativa permite gerenciar alunos, professores, turmas, disciplinas, matrículas, notas e frequências.

## Principais recursos

- Painel com indicadores e atividades recentes.
- Cadastros, consultas, edição, pesquisa e exclusão.
- Controle de acesso por perfil aplicado nas rotas e na interface.
- Área do aluno limitada à consulta das próprias notas e frequências.
- Painel de frequência com filtros, totais e resumo de presenças/faltas por aluno.
- Interface responsiva com vermelho como cor de destaque.
- Criação automática da conta interna ao cadastrar aluno ou professor.
- Validação dos dados no servidor e mensagens de erro na interface.
- Proteção contra exclusão de registros que ainda possuem vínculos.
- API organizada em `/api` e protegida para perfis autorizados.

## Perfis de acesso

- **Administrador e professor:** acessam os módulos de gestão acadêmica.
- **Aluno:** acessa apenas o próprio painel, suas notas, sua frequência e seu perfil.
- O cadastro público sempre cria uma conta de aluno. Perfis administrativos e docentes não podem ser obtidos pelo formulário público.
- O bloqueio é feito no servidor; informar diretamente a URL de um módulo restrito retorna acesso negado.

## Requisitos

- PHP 8.3 ou superior
- Composer
- Node.js 20 ou superior
- MySQL 8 ou superior

## Instalação

```bash
composer install
cp .env.example .env
php artisan key:generate
```

Configure o banco de dados no arquivo `.env` e execute:

```bash
php artisan migrate
npm install
npm run build
php artisan serve
```

Acesse `http://127.0.0.1:8000`.

Durante o desenvolvimento, também é possível usar:

```bash
composer run dev
```

## Estrutura da interface

- `resources/views/layouts/app.blade.php`: layout compartilhado, menu lateral e barra superior.
- `resources/views/dashboard/index.blade.php`: página inicial com indicadores.
- `resources/views/dashboard/aluno.blade.php`: painel exclusivo e limitado do estudante.
- `resources/views/portal`: consultas de notas e frequência do aluno autenticado.
- `resources/views/{modulo}/index.blade.php`: consulta e pesquisa dos registros.
- `resources/views/{modulo}/create.blade.php`: página de cadastro.
- `resources/views/{modulo}/edit.blade.php`: página de edição.
- `resources/views/{modulo}/_form.blade.php`: campos reutilizados no cadastro e na edição.
- `resources/views/components`: mensagens, cabeçalhos, status e paginação.
- `resources/css/app.css`: tema visual responsivo.
- `resources/js/app.js`: menu móvel, confirmação de exclusão e máscaras de campos.
- `routes/web.php`: páginas Blade e operações dos formulários.
- `routes/api.php`: endpoints JSON mantidos para integrações.
- `app/Http/Controllers/CrudController.php`: operações compartilhadas entre as páginas e a API.

Cada módulo (`alunos`, `professores`, `turmas`, `disciplinas`, `matriculas`, `notas` e `frequencias`) possui views próprias. A interface não depende mais de uma página única nem de JavaScript para trocar o conteúdo entre módulos.
