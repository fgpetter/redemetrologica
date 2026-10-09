@extends('layouts.master')

@section('title') Tutorial de inscrição em PEP @endsection

@section('content')
    <style>
        .tutorial-image-viewport {
            background: #f8f9fa;
            text-align: center;
        }

        .tutorial-image-frame {
            position: relative;
            display: inline-block;
            max-width: 100%;
            text-align: left;
            vertical-align: top;
        }

        .tutorial-image-frame a {
            display: block;
        }

        .tutorial-image-frame img {
            display: block;
            width: auto;
            max-width: 100%;
            max-height: 80vh;
            margin: 0 auto;
        }

        .tutorial-image-marker {
            position: absolute;
            display: grid;
            width: 2rem;
            height: 2rem;
            place-items: center;
            transform: translate(-50%, -50%);
            border: 2px solid #fff;
            border-radius: 50%;
            background: #dc3545;
            box-shadow: 0 0.15rem 0.45rem rgb(0 0 0 / 35%);
            color: #fff;
            font-weight: 700;
            pointer-events: none;
        }

        .tutorial-question {
            scroll-margin-top: 1.5rem;
        }
    </style>

    @component('components.breadcrumb')
        @slot('li_1') Área do Cliente @endslot
        @slot('title') Tutorial de inscrição em PEP @endslot
    @endcomponent

    <div class="row justify-content-center">
        <div class="col-12 col-xxl-10">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-4 p-lg-5">
                    <div class="d-flex flex-wrap align-items-start justify-content-between gap-3 mb-4">
                        <div>
                            <span class="badge bg-primary-subtle text-primary mb-2">PASSO A PASSO</span>
                            <h1 class="h3 mb-2">Como fazer uma inscrição em PEP?</h1>
                            <p class="text-muted mb-0">Encontre respostas às dúvidas mais comuns e acompanhe o caminho até concluir sua inscrição.</p>
                        </div>
                        <a href="{{ route('site-list-interlaboratoriais') }}" target="_blank" rel="noopener" class="btn btn-primary">
                            Ver PEPs disponíveis <i class="ri-external-link-line ms-1"></i>
                        </a>
                    </div>

                    <nav class="border rounded bg-light p-3 p-lg-4" aria-label="Índice do tutorial">
                        <h2 class="h6 text-uppercase text-muted mb-3">Escolha uma pergunta</h2>
                        <div class="row g-2">
                            <div class="col-12 col-md-6">
                                <a href="#como-comecar" class="link-primary">Como inscrever meu laboratório em um PEP?</a>
                            </div>
                            <div class="col-12 col-md-6">
                                <a href="#dados-empresa" class="link-primary">Quais dados da empresa preciso preencher?</a>
                            </div>
                            <div class="col-12 col-md-6">
                                <a href="#laboratorio-existente" class="link-primary">Como inscrever um laboratório já cadastrado?</a>
                            </div>
                            <div class="col-12 col-md-6">
                                <a href="#laboratorio-novo" class="link-primary">Como adicionar um novo laboratório em um PEP?</a>
                            </div>
                            <div class="col-12 col-md-6">
                                <a href="#multiplos-laboratorios" class="link-primary">Como cadastrar múltiplos laboratórios em um PEP?</a>
                            </div>
                            <div class="col-12 col-md-6">
                                <a href="#outro-cnpj" class="link-primary">Como fazer uma inscrição usando outro CNPJ?</a>
                            </div>
                            <div class="col-12 col-md-6">
                                <a href="#blocos-analistas" class="link-primary">Como selecionar os blocos e cadastrar os analistas?</a>
                            </div>
                            <div class="col-12 col-md-6">
                                <a href="#certificado" class="link-primary">Como solicitar o Certificado de Desempenho?</a>
                            </div>
                            <div class="col-12 col-md-6">
                                <a href="#concluir-inscricao" class="link-primary">Como concluir e conferir minha inscrição?</a>
                            </div>
                            <div class="col-12 col-md-6">
                                <a href="#corrigir-dados" class="link-primary">Como corrigir os dados de um laboratório inscrito?</a>
                            </div>
                        </div>
                    </nav>
                </div>
            </div>

            <section id="como-comecar" class="card border-0 shadow-sm mb-4 tutorial-question">
                <div class="card-body p-4">
                    <h2 class="h4">Como inscrever meu laboratório em um PEP?</h2>
                    <ul class="mb-4">
                        <li class="mb-2">No painel, clique em <strong>Inscreva-se em um Ensaio de Proficiência</strong>.</li>
                        <li class="mb-2">Encontre um PEP com inscrições abertas e abra seus detalhes.</li>
                        <li class="mb-2">Clique em <strong>INSCREVA-SE</strong>. Se aparecer o aviso sobre o sistema de inscrições, clique em <strong>Entendi!</strong> para continuar.</li>
                        <li>Você será levado ao painel para informar o CNPJ da empresa responsável pela cobrança e pela nota fiscal.</li>
                    </ul>
                    @include('painel.painel-cliente.partials.tutorial-print', [
                        'image' => 'tutorial-pep/00-escolha-pep.jpg',
                        'alt' => 'Página de Ensaios de Proficiência com cartões de PEPs que estão com inscrições abertas.',
                        'caption' => 'Na lista, escolha um PEP com inscrições abertas.',
                        'legend' => '1 — Cartão do PEP. Abra os detalhes para conferir o ensaio.',
                        'markers' => [['number' => 1, 'x' => 0.16, 'y' => 0.92]],
                    ])
                    @include('painel.painel-cliente.partials.tutorial-print', [
                        'image' => 'tutorial-pep/00-detalhe-pep.jpg',
                        'alt' => 'Detalhes do PEP com o status de inscrições abertas e o botão INSCREVA-SE.',
                        'caption' => 'Confira o PEP e clique em INSCREVA-SE.',
                        'legend' => '1 — Botão para iniciar a inscrição.',
                        'markers' => [['number' => 1, 'x' => 0.79, 'y' => 0.68]],
                    ])
                    @include('painel.painel-cliente.partials.tutorial-print', [
                        'image' => 'tutorial-pep/01-cnpj.jpg',
                        'alt' => 'Painel do cliente no início da inscrição do PEP, com o campo para digitar o CNPJ e o botão Buscar.',
                        'caption' => 'Informe o CNPJ da empresa para prosseguir.',
                        'legend' => '1 — Campo CNPJ. 2 — Botão Buscar.',
                        'markers' => [['number' => 1, 'x' => 0.28, 'y' => 0.72], ['number' => 2, 'x' => 0.47, 'y' => 0.72]],
                    ])
                </div>
            </section>

            <section id="dados-empresa" class="card border-0 shadow-sm mb-4 tutorial-question">
                <div class="card-body p-4">
                    <h2 class="h4">Quais dados da empresa preciso preencher?</h2>
                    <p>Depois da busca, confira os dados encontrados. Se a empresa ainda não estiver cadastrada, preencha os campos marcados como obrigatórios:</p>
                    <ul>
                        <li><strong>Obrigatórios:</strong> razão social, CNPJ, e-mail de cobrança, CEP, endereço, bairro, cidade e UF.</li>
                        <li><strong>Opcionais:</strong> telefone e complemento do endereço.</li>
                    </ul>
                    <p>Quando a busca não encontrar a empresa, preencha o formulário de novo cadastro. Se ela já existir, revise os dados que aparecem. O CEP pode preencher automaticamente endereço, bairro, cidade e UF; confira tudo antes de clicar em <strong>Continuar</strong>.</p>
                    @include('painel.painel-cliente.partials.tutorial-print', [
                        'image' => 'tutorial-pep/02-empresa-nova.jpg',
                        'alt' => 'Formulário vazio para cadastrar uma nova empresa após buscar um CNPJ ainda não cadastrado.',
                        'caption' => 'Quando o CNPJ não é localizado, informe os dados da nova empresa.',
                        'legend' => '1 — Dados da empresa e contato de cobrança. 2 — Endereço de cobrança. 3 — Continuar.',
                        'markers' => [['number' => 1, 'x' => 0.46, 'y' => 0.48], ['number' => 2, 'x' => 0.46, 'y' => 0.67], ['number' => 3, 'x' => 0.91, 'y' => 0.84]],
                    ])
                    @include('painel.painel-cliente.partials.tutorial-print', [
                        'image' => 'tutorial-pep/03-empresa-revisao.jpg',
                        'alt' => 'Formulário de revisão da empresa com razão social, CNPJ, e-mail de cobrança e endereço.',
                        'caption' => 'Revise a empresa e o endereço usados para cobrança.',
                        'legend' => '1 — Razão social e CNPJ. 2 — E-mail de cobrança. 3 — Endereço de cobrança. 4 — Continuar.',
                        'markers' => [['number' => 1, 'x' => 0.43, 'y' => 0.2], ['number' => 2, 'x' => 0.8, 'y' => 0.31], ['number' => 3, 'x' => 0.42, 'y' => 0.5], ['number' => 4, 'x' => 0.89, 'y' => 0.8]],
                    ])
                </div>
            </section>

            <section id="laboratorio-existente" class="card border-0 shadow-sm mb-4 tutorial-question">
                <div class="card-body p-4">
                    <h2 class="h4">Como inscrever um laboratório já cadastrado?</h2>
                    <ul class="mb-4">
                        <li class="mb-2">Após confirmar a empresa, encontre o laboratório na lista de laboratórios disponíveis.</li>
                        <li class="mb-2">Clique em <strong>Inscrever</strong> para abrir o formulário daquele laboratório.</li>
                        <li class="mb-2">Confira ou edite os dados e preencha o responsável técnico e o e-mail do laboratório. Telefone e complemento são opcionais.</li>
                        <li>Selecione os blocos exigidos pelo PEP e clique em <strong>Salvar</strong>.</li>
                    </ul>
                    @include('painel.painel-cliente.partials.tutorial-print', [
                        'image' => 'tutorial-pep/04-laboratorio-opcoes.jpg',
                        'alt' => 'Lista de laboratórios previamente cadastrados com botão Inscrever e opção Cadastrar Novo Laboratório.',
                        'caption' => 'Escolha o laboratório da empresa ou abra o cadastro de um novo.',
                        'legend' => '1 — Laboratório existente. 2 — Inscrever. 3 — Cadastrar Novo Laboratório.',
                        'markers' => [['number' => 1, 'x' => 0.36, 'y' => 0.65], ['number' => 2, 'x' => 0.94, 'y' => 0.66], ['number' => 3, 'x' => 0.42, 'y' => 0.78]],
                    ])
                    @include('painel.painel-cliente.partials.tutorial-print', [
                        'image' => 'tutorial-pep/05-laboratorio-edicao.jpg',
                        'alt' => 'Formulário do laboratório cadastrado aberto para revisar dados, escolher bloco e salvar.',
                        'caption' => 'Revise o cadastro existente e os dados de contato usados nesta inscrição.',
                        'legend' => '1 — Nome e responsável técnico. 2 — E-mail. 3 — Endereço do laboratório.',
                        'markers' => [['number' => 1, 'x' => 0.53, 'y' => 0.62], ['number' => 2, 'x' => 0.75, 'y' => 0.72], ['number' => 3, 'x' => 0.52, 'y' => 0.9]],
                    ])
                    @include('painel.painel-cliente.partials.tutorial-print', [
                        'image' => 'tutorial-pep/05-laboratorio-salvar.jpg',
                        'alt' => 'Parte inferior do formulário do laboratório com bloco de inscrição, certificado adicional, Salvar e Cancelar.',
                        'caption' => 'Selecione pelo menos um bloco e salve a inscrição do laboratório.',
                        'legend' => '1 — Bloco disponível. 2 — Certificado opcional com custo adicional. 3 — Salvar.',
                        'markers' => [['number' => 1, 'x' => 0.17, 'y' => 0.5], ['number' => 2, 'x' => 0.29, 'y' => 0.79], ['number' => 3, 'x' => 0.85, 'y' => 0.92]],
                    ])
                </div>
            </section>

            <section id="laboratorio-novo" class="card border-0 shadow-sm mb-4 tutorial-question">
                <div class="card-body p-4">
                    <h2 class="h4">Como adicionar um novo laboratório em um PEP?</h2>
                    <ul class="mb-4">
                        <li class="mb-2">Clique em <strong>Cadastrar Novo Laboratório</strong>.</li>
                        <li class="mb-2">Preencha laboratório, responsável técnico, e-mail e endereço. O nome, responsável, e-mail, CEP, logradouro, número, bairro, cidade e UF são obrigatórios.</li>
                        <li class="mb-2">Telefone, complemento e observações adicionais são opcionais.</li>
                        <li>Selecione os blocos disponíveis e clique em <strong>Salvar</strong>.</li>
                    </ul>
                    @include('painel.painel-cliente.partials.tutorial-print', [
                        'image' => 'tutorial-pep/10-novo-laboratorio.jpg',
                        'alt' => 'Formulário aberto para cadastrar um novo laboratório, com campos de contato, endereço, blocos, certificado e botão Salvar.',
                        'caption' => 'Preencha o cadastro do novo laboratório e selecione os blocos de inscrição.',
                        'legend' => '1 — Dados de identificação e contato. 2 — Endereço e número. 3 — Blocos. 4 — Salvar.',
                        'markers' => [['number' => 1, 'x' => 0.55, 'y' => 0.32], ['number' => 2, 'x' => 0.88, 'y' => 0.43], ['number' => 3, 'x' => 0.18, 'y' => 0.69], ['number' => 4, 'x' => 0.88, 'y' => 0.85]],
                    ])
                </div>
            </section>

            <section id="multiplos-laboratorios" class="card border-0 shadow-sm mb-4 tutorial-question">
                <div class="card-body p-4">
                    <h2 class="h4">Como cadastrar múltiplos laboratórios em um PEP?</h2>
                    <p>Faça uma inscrição por laboratório, na mesma empresa:</p>
                    <ul>
                        <li class="mb-2">Inscreva um laboratório existente ou salve o cadastro de um novo.</li>
                        <li class="mb-2">Para adicionar outro, selecione o próximo laboratório disponível ou abra <strong>Cadastrar Novo Laboratório</strong>.</li>
                        <li>Repita o processo para cada laboratório. Quando todos aparecerem na lista de inscritos, clique em <strong>Concluir Inscrições</strong>.</li>
                    </ul>
                    <div class="alert alert-info mb-0" role="note">O botão <strong>Salvar</strong> registra cada laboratório. Você pode adicionar outros antes de encerrar o processo.</div>
                    @include('painel.painel-cliente.partials.tutorial-print', [
                        'image' => 'tutorial-pep/07-multiplos-laboratorios.jpg',
                        'alt' => 'Lista de um laboratório já inscrito, com a opção para cadastrar outro laboratório e o botão Concluir Inscrições.',
                        'caption' => 'Depois de salvar um laboratório, adicione os próximos antes de concluir.',
                        'legend' => '1 — Laboratório já salvo. 2 — Cadastrar outro. 3 — Concluir quando terminar todos.',
                        'markers' => [['number' => 1, 'x' => 0.35, 'y' => 0.4], ['number' => 2, 'x' => 0.91, 'y' => 0.52], ['number' => 3, 'x' => 0.88, 'y' => 0.65]],
                    ])
                </div>
            </section>

            <section id="outro-cnpj" class="card border-0 shadow-sm mb-4 tutorial-question">
                <div class="card-body p-4">
                    <h2 class="h4">Como fazer uma inscrição usando outro CNPJ?</h2>
                    <ul class="mb-4">
                        <li class="mb-2">Conclua a inscrição da empresa atual.</li>
                        <li class="mb-2">No painel, abra novamente o mesmo PEP pela lista de ensaios disponíveis.</li>
                        <li class="mb-2">Clique em <strong>INSCREVA-SE</strong>, informe o novo CNPJ e confira os dados da empresa.</li>
                        <li>Selecione ou cadastre os laboratórios dessa empresa e conclua a inscrição.</li>
                    </ul>
                    @include('painel.painel-cliente.partials.tutorial-print', [
                        'image' => 'tutorial-pep/08-multiplas-empresas.jpg',
                        'alt' => 'Inscrições do mesmo cliente agrupadas no painel por empresas com CNPJs diferentes.',
                        'caption' => 'O painel agrupa os laboratórios por empresa dentro de cada PEP.',
                        'legend' => '1 — Primeira empresa. 2 — Segunda empresa, cadastrada com outro CNPJ.',
                        'markers' => [['number' => 1, 'x' => 0.43, 'y' => 0.23], ['number' => 2, 'x' => 0.43, 'y' => 0.42]],
                    ])
                </div>
            </section>

            <section id="blocos-analistas" class="card border-0 shadow-sm mb-4 tutorial-question">
                <div class="card-body p-4">
                    <h2 class="h4">Como selecionar os blocos e cadastrar os analistas?</h2>
                    <p>Os blocos disponíveis, a quantidade de opções e os valores dependem do PEP:</p>
                    <ul class="mb-4">
                        <li class="mb-2"><strong>Avaliação laboratorial:</strong> marque os blocos que deseja incluir. É necessário selecionar ao menos um.</li>
                        <li class="mb-2"><strong>Avaliação por analista:</strong> selecione um bloco. O formulário exibirá a quantidade de analistas prevista para o bloco.</li>
                        <li><strong>Para cada analista:</strong> informe nome, e-mail e telefone; todos são obrigatórios.</li>
                    </ul>
                    @include('painel.painel-cliente.partials.tutorial-print', [
                        'image' => 'tutorial-pep/06-analista-bloco.jpg',
                        'alt' => 'Formulário de inscrição com nome, e-mail e telefone obrigatórios do analista, seleção do bloco e opção de certificado.',
                        'caption' => 'Os campos do analista aparecem após escolher um bloco que exige participantes identificados.',
                        'legend' => '1 — Nome, e-mail e telefone do analista. 2 — Bloco único nesta modalidade. 3 — Opção de certificado.',
                        'markers' => [['number' => 1, 'x' => 0.28, 'y' => 0.56], ['number' => 2, 'x' => 0.29, 'y' => 0.68], ['number' => 3, 'x' => 0.5, 'y' => 0.82]],
                    ])
                </div>
            </section>

            <section id="certificado" class="card border-0 shadow-sm mb-4 tutorial-question">
                <div class="card-body p-4">
                    <h2 class="h4">Como solicitar o Certificado de Desempenho?</h2>
                    <p>No formulário do laboratório, marque <strong>Solicitar Certificado de Desempenho</strong> se desejar incluí-lo. A tela informa o custo adicional — no exemplo, <strong>R$ 300,00</strong> — junto da opção. Confira o valor exibido para o PEP antes de salvar.</p>
                    @include('painel.painel-cliente.partials.tutorial-print', [
                        'image' => 'tutorial-pep/10-novo-laboratorio.jpg',
                        'alt' => 'Opção Solicitar Certificado de Desempenho com o valor adicional de R$ 300,00 exibido no formulário.',
                        'caption' => 'O valor aparece ao lado da opção do certificado.',
                        'legend' => '1 — Marque apenas se desejar incluir o certificado e confira o custo adicional exibido.',
                        'markers' => [['number' => 1, 'x' => 0.5, 'y' => 0.81]],
                    ])
                </div>
            </section>

            <section id="concluir-inscricao" class="card border-0 shadow-sm mb-4 tutorial-question">
                <div class="card-body p-4">
                    <h2 class="h4">Como concluir e conferir minha inscrição?</h2>
                    <ul class="mb-4">
                        <li class="mb-2">Clique em <strong>Salvar</strong> para registrar os dados e a inscrição daquele laboratório.</li>
                        <li class="mb-2">Confira a lista de laboratórios inscritos e adicione outros se necessário.</li>
                        <li class="mb-2">Clique em <strong>Concluir Inscrições</strong> para encerrar a etapa do PEP.</li>
                        <li>No painel, confira o PEP, a empresa e os laboratórios inscritos. As inscrições aparecem agrupadas por empresa.</li>
                    </ul>
                    @include('painel.painel-cliente.partials.tutorial-print', [
                        'image' => 'tutorial-pep/09-inscricoes-concluidas.jpg',
                        'alt' => 'Painel do cliente exibindo PEPs inscritos, agrupados por empresas e laboratórios.',
                        'caption' => 'Após concluir, confira cada empresa e laboratório no painel.',
                        'legend' => '1 — PEP inscrito. 2 — Empresa e CNPJ. 3 — Dados do laboratório e da inscrição.',
                        'markers' => [['number' => 1, 'x' => 0.47, 'y' => 0.15], ['number' => 2, 'x' => 0.37, 'y' => 0.22], ['number' => 3, 'x' => 0.45, 'y' => 0.3]],
                    ])
                </div>
            </section>

            <section id="corrigir-dados" class="card border-0 shadow-sm mb-4 tutorial-question">
                <div class="card-body p-4">
                    <h2 class="h4">Como corrigir os dados de um laboratório inscrito?</h2>
                    <ul>
                        <li class="mb-2">Abra o PEP na área de inscrição e expanda o laboratório inscrito para editar seus dados.</li>
                        <li class="mb-2">Faça as alterações e clique em <strong>Salvar</strong>.</li>
                        <li>Para encerrar, volte à lista e clique em <strong>Concluir Inscrições</strong>.</li>
                    </ul>
                    @include('painel.painel-cliente.partials.tutorial-print', [
                        'image' => 'tutorial-pep/05-laboratorio-edicao.jpg',
                        'alt' => 'Dados do laboratório existente expandidos para corrigir nome, contato e endereço.',
                        'caption' => 'Expanda o laboratório inscrito, corrija os dados e salve as alterações.',
                        'legend' => '1 — Nome do laboratório. 2 — Responsável técnico e e-mail da inscrição. 3 — Endereço compartilhado do laboratório.',
                        'markers' => [['number' => 1, 'x' => 0.3, 'y' => 0.62], ['number' => 2, 'x' => 0.72, 'y' => 0.62], ['number' => 3, 'x' => 0.57, 'y' => 0.89]],
                    ])
                    <div class="alert alert-warning mb-0" role="note">Alterações no <strong>nome</strong> e no <strong>endereço</strong> atualizam o cadastro compartilhado do laboratório e também aparecem nas inscrições anteriores. Os dados de responsável técnico e contato são informados para a inscrição.</div>
                </div>
            </section>

            <div class="text-center mb-4">
                <a href="{{ route('site-list-interlaboratoriais') }}" target="_blank" rel="noopener" class="btn btn-primary btn-lg">
                    Ver PEPs disponíveis <i class="ri-arrow-right-line ms-1"></i>
                </a>
                <a href="{{ route('painel-index') }}" class="btn btn-outline-secondary btn-lg ms-2">Voltar ao painel</a>
            </div>
        </div>
    </div>
@endsection
