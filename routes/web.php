<?php

use Illuminate\Support\Facades\{Auth, Route};
use App\Http\Controllers\{
  PostController,
  UserController,
  CursoController,
  BancosController,
  UnidadeController,
  DownloadController,
  InterlabController,
  AvaliadorController,
  InstrutorController,
  PostMediaController,
  ParametrosController,
  PlanoContaController,
  AgendaCursoController,
  AreaAtuacaoController,
  CentroCustoController,
  FuncionarioController,
  FornecedorController,
  LaboratorioController,
  DadoBancarioController,
  TipoAvaliacaoController,
  AgendaInterlabController,
  InscricaoCursoController,
  AgendaAvaliacaoController,
  MateriaisPadroesController,
  InscricaoInterlabController,
  ModalidadePagamentoController,
  LancamentoFinanceiroController,
  HomeController,
  PainelController,
  AgendaCursoInCompanyController
};
use App\Http\Controllers\Auth\ForgotPasswordController;

Auth::routes();
Route::get('/forgot-password', [ForgotPasswordController::class, 'forgotPassword'])->name('password.request');
Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('send-reset-link-email');
Route::post('/reset-password', [ForgotPasswordController::class, 'resetPassword'])->name('reset-password');

Route::get('/', [HomeController::class, 'root'])->name('root');

/* Rotas estáticas */
Route::get('home', [HomeController::class, 'root'])->name('root');
Route::view('noticias', 'site.pages.noticias');
Route::view('galerias', 'site.pages.galerias');
Route::view('associe-se', 'site.pages.associe-se');

/*Rotas de interlabs */
Route::get('interlaboratoriais', [AgendaInterlabController::class,'exibeInterlabsSite'])->name('site-list-interlaboratoriais');
Route::get('interlaboratorial/{agendainterlab:uid}', [AgendaInterlabController::class,'exibePaginaAgendaInterlab'])->name('site-single-interlaboratorial');
Route::get('interlab/inscricao', [InscricaoInterlabController::class, 'interlabInscricao'])->name('interlab-inscricao');

Route::view('laboratorios-avaliacao', 'site.pages.laboratorios-avaliacao');
Route::get('laboratorios-reconhecidos', [LaboratorioController::class, 'siteIndex']);
Route::view('bonus-metrologia', 'site.pages.bonus-metrologia');
Route::get('laboratorios-downloads', [DownloadController::class, 'siteIndex']);
Route::view('fale-conosco', 'site.pages.fale-conosco');
Route::view('slug-da-noticia', 'site.pages.slug-da-noticia');
Route::view('slug-da-galeria', 'site.pages.slug-da-galeria');
Route::view('sobre', 'site.pages.sobre');

/*Rotas das slugs (noticia e galeria) */
Route::get('noticias', [PostController::class, 'ListNoticias'])->name('show-list'); //mostra lista de noticias
Route::get('galerias', [PostController::class, 'ListGalerias'])->name('show-list'); //mostra lista de galerias
Route::get('noticia/{slug}', [PostController::class, 'show'])->name('noticia-show'); //mostra noticia
Route::get('galeria/{slug}', [PostController::class, 'show'])->name('galeria-show'); //mostra galeria

/*Rotas de cursos */
Route::get('cursos', [AgendaCursoController::class, 'listCursosAgendados'])->name('cursos-agendados-list');
Route::get('cursos/{agendacurso:uid}', [AgendaCursoController::class, 'showCursoAgendado'])->name('curso-agendado-show');
Route::get('curso/inscricao', [InscricaoCursoController::class, 'cursoInscricao'])->name('curso-inscricao');
Route::view('slug-cursos', 'site.pages.slug-cursos');

/* Rotas do template */
// Route::get('{any}', [App\Http\Controllers\HomeController::class, 'index'])->name('index');

/**
 * 
 * Rotas do painel
 * 
 */
Route::prefix('painel')->middleware('auth')->group(function () {

  Route::get('/', [PainelController::class, 'index'])->name('painel-index');

  /* Usuários */
  Route::group(['prefix' => 'user',], function () {
    Route::get('index', [UserController::class, 'index'])->name('user-index')->middleware('permission:funcionario,admin');
    Route::get('edit/{user}', [UserController::class, 'view'])->name('user-edit');
    Route::post('create', [UserController::class, 'create'])->name('user-create')->middleware('permission:funcionario,admin');
    Route::post('update/{user}', [UserController::class, 'update'])->name('user-update');
    Route::post('delete/{user}', [UserController::class, 'delete'])->name('user-delete')->middleware('permission:funcionario,admin');
    Route::post('update-permissions/{user}', [UserController::class, 'updatePermission'])->name('user-permission-update')->middleware('permission:funcionario,admin');
  });

  /**
   * Pessoas 
   */
  Route::prefix('pessoa')->group( base_path('routes/pessoas.php') )->middleware('permission:funcionario,admin');

  /* Endereços */
  Route::prefix('endereco')->group( base_path('routes/enderecos.php') )->middleware('permission:funcionario,admin');

  /* Unidades */
  Route::group(['prefix' => 'unidade', 'middleware' => 'permission:funcionario,admin'], function () {
    Route::post('create', [UnidadeController::class, 'create'])->name('unidade-create');
    Route::post('update/{unidade:uid}', [UnidadeController::class, 'update'])->name('unidade-update');
    Route::post('delete/{unidade:uid}', [UnidadeController::class, 'delete'])->name('unidade-delete');
  });

  /* Funcionarios */
  Route::group(['prefix' => 'funcionario', 'middleware' => 'permission:funcionario,admin'], function () {
    Route::get('index', [FuncionarioController::class, 'index'])->name('funcionario-index');
    Route::get('insert/{funcionario:uid?}', [FuncionarioController::class, 'insert'])->name('funcionario-insert');
    Route::post('create', [FuncionarioController::class, 'create'])->name('funcionario-create');
    Route::post('update/{funcionario:uid}', [FuncionarioController::class, 'update'])->name('funcionario-update');
    Route::post('delete/{funcionario:uid}', [FuncionarioController::class, 'delete'])->name('funcionario-delete');
    Route::post('delete-curriculo/{funcionario:uid}', [FuncionarioController::class, 'curriculoDelete'])->name('curriculo-delete');
  });

  /* Fornecedores */
  Route::group(['prefix' => 'fornecedor', 'middleware' => 'permission:funcionario,admin'], function () {
    Route::get('index', [FornecedorController::class, 'index'])->name('fornecedor-index');
    Route::get('insert/{fornecedor:uid?}', [FornecedorController::class, 'insert'])->name('fornecedor-insert');
    Route::post('create', [FornecedorController::class, 'create'])->name('fornecedor-create');
    Route::post('update/{fornecedor:uid}', [FornecedorController::class, 'update'])->name('fornecedor-update');
    Route::post('delete/{fornecedor:uid}', [FornecedorController::class, 'delete'])->name('fornecedor-delete');
  });

  /* Dados bancários */
  Route::group(['prefix' => 'conta', 'middleware' => 'permission:funcionario,admin'], function () {
    Route::post('create', [DadoBancarioController::class, 'create'])->name('conta-create');
    Route::post('update/{conta:uid}', [DadoBancarioController::class, 'update'])->name('conta-update');
    Route::post('delete/{conta:uid}', [DadoBancarioController::class, 'delete'])->name('conta-delete');
  });



  /**
   *  Cursos 
   */
  Route::group(['prefix' => 'curso', 'middleware' => 'permission:funcionario,admin'], function () {
    Route::get('index', [CursoController::class, 'index'])->name('curso-index');
    Route::get('insert/{curso:uid?}', [CursoController::class, 'insert'])->name('curso-insert');
    Route::post('create', [CursoController::class, 'create'])->name('curso-create');
    Route::post('update/{curso:uid}', [CursoController::class, 'update'])->name('curso-update');
    Route::post('delete/{curso:uid}', [CursoController::class, 'delete'])->name('curso-delete');
    Route::post('delete-folder/{curso:uid}', [CursoController::class, 'folderDelete'])->name('curso-folder-delete');
    Route::post('delete-thumb/{curso:uid}', [CursoController::class, 'thumbDelete'])->name('curso-thumb-delete');
    Route::post('upload-material/{curso:uid}', [CursoController::class, 'uploadMaterial'])->name('curso-upload-material');
    Route::post('delete-material/{material:uid}', [CursoController::class, 'deleteMaterial'])->name('curso-delete-material');
    Route::get('visualizar-certificado/{inscrito:uid}', [CursoController::class, 'viewCertificado'])->name('curso-visualizar-certificado');
  });

  /* Agendamento de cursos*/
  Route::group(['prefix' => 'agendamento-curso', 'middleware' => 'permission:funcionario,admin'], function () {
    Route::get('index', [AgendaCursoController::class, 'index'])->name('agendamento-curso-index');
    Route::get('insert/{agendacurso:uid?}', [AgendaCursoController::class, 'insert'])->name('agendamento-curso-insert');
    Route::post('create', [AgendaCursoController::class, 'create'])->name('agendamento-curso-create');
    Route::post('update/{agendacurso:uid}', [AgendaCursoController::class, 'update'])->name('agendamento-curso-update');
    Route::post('delete/{agendacurso:uid}', [AgendaCursoController::class, 'delete'])->name('agendamento-curso-delete');
    Route::post('salvar-despesa', [AgendaCursoController::class, 'salvaDespesa'])->name('curso-salvar-despesa');
    Route::post('delete-despesa/{despesa:uid}', [AgendaCursoController::class, 'deleteDespesa'])->name('curso-delete-despesa');
  });

  /* Agendamento de cursos in-company */
  Route::group(['prefix' => 'agendamento-curso-in-company', 'middleware' => 'permission:funcionario,admin'], function () {
    Route::get('index', [AgendaCursoInCompanyController::class, 'index'])->name('agendamento-curso-in-company-index');
    Route::get('insert/{agendacurso:uid?}', [AgendaCursoInCompanyController::class, 'insert'])->name('agendamento-curso-in-company-insert');
    Route::post('create', [AgendaCursoInCompanyController::class, 'create'])->name('agendamento-curso-in-company-create');
    Route::post('update/{agendacurso:uid}', [AgendaCursoInCompanyController::class, 'update'])->name('agendamento-curso-in-company-update');
  });
  
  /* Matricula em cursos */
  Route::group(['prefix' => 'inscricao-curso'], function () {
    Route::post('confirmacao', [InscricaoCursoController::class, 'confirmaInscricao'])->name('confirma-inscricao');
    Route::post('envia-convite', [InscricaoCursoController::class, 'enviaConvite'])->name('envia-convite-curso');
    Route::post('informa-empresa', [InscricaoCursoController::class, 'informaEmpresa'])->name('informa-empresa');
    Route::post('cancela-inscricao/{inscrito:uid}', [InscricaoCursoController::class, 'cancelaInscricao'])->name('cancela-inscricao');
    Route::post('salvar-inscrito/{inscrito:uid?}', [InscricaoCursoController::class, 'salvaInscrito'])->name('salvar-inscrito');
    Route::get('conclui-inscricao', [InscricaoCursoController::class, 'concluiInscricao'])->name('conclui-inscricao');
    Route::post('envia-lista-inscritos/{agendacurso:uid}', [InscricaoCursoController::class, 'adicionaInscritosPorLista'])->name('envia-lista-inscritos');
  });

  /* Instrutores*/
  Route::group(['prefix' => 'instrutor', 'middleware' => 'permission:funcionario,admin'], function () {
    Route::get('index', [InstrutorController::class, 'index'])->name('instrutor-index');
    Route::get('insert/{instrutor:uid?}', [InstrutorController::class, 'insert'])->name('instrutor-insert');
    Route::post('create', [InstrutorController::class, 'create'])->name('instrutor-create');
    Route::post('update/{instrutor:uid}', [InstrutorController::class, 'update'])->name('instrutor-update');
    Route::post('delete/{instrutor:uid}', [InstrutorController::class, 'delete'])->name('instrutor-delete');

    Route::post('createcursoshabilitado/{instrutor:uid}', [InstrutorController::class, 'createCursoHabilitado'])->name('instrutor-create-curso-habilitado');
    Route::post('updatecursoshabilitado/{cursohabilitado:uid}', [InstrutorController::class, 'updateCursoHabilitado'])->name('instrutor-update-curso-habilitado');
    Route::post('deletecursoshabilitado/{cursohabilitado:uid}', [InstrutorController::class, 'deleteCursoHabilitado'])->name('instrutor-delete-curso-habilitado');
    Route::post('delete-curriculo/{instrutor:uid}', [InstrutorController::class, 'curriculoDelete'])->name('instrutor-curriculo-delete');
  });



  /**
   * Avalições
   */
  Route::group(['prefix' => 'avaliacao', 'middleware' => 'permission:funcionario,admin'], function () {
    Route::get('index', [AgendaAvaliacaoController::class, 'index'])->name('agendamento-avaliacao-index');
    Route::get('insert/{avaliacao:uid}', [AgendaAvaliacaoController::class, 'insert'])->name('avaliacao-insert');
    Route::post('create', [AgendaAvaliacaoController::class, 'create'])->name('avaliacao-create');
    Route::post('update/{avaliacao:uid}', [AgendaAvaliacaoController::class, 'update'])->name('avaliacao-update');
    Route::post('delete/{avaliacao:uid}', [AgendaAvaliacaoController::class, 'delete'])->name('avaliacao-delete');
    Route::post('save-area/{area:uid?}', [AgendaAvaliacaoController::class, 'saveArea'])->name('avaliacao-save-area');
    Route::post('delete-area/{area:uid?}', [AgendaAvaliacaoController::class, 'deleteArea'])->name('avaliacao-delete-area');
  });

  /* Avaliadores */
  Route::group(['prefix' => 'avaliador', 'middleware' => 'permission:funcionario,admin'], function () {
    Route::get('index', [AvaliadorController::class, 'index'])->name('avaliador-index');
    Route::get('insert/{avaliador:uid?}', [AvaliadorController::class, 'insert'])->name('avaliador-insert');
    Route::post('create', [AvaliadorController::class, 'create'])->name('avaliador-create');
    Route::post('update/{avaliador:uid}', [AvaliadorController::class, 'update'])->name('avaliador-update');
    Route::post('delete/{avaliador:uid}', [AvaliadorController::class, 'delete'])->name('avaliador-delete');
    Route::post('delete-curriculo/{avaliador:uid}', [AvaliadorController::class, 'curriculoDelete'])->name('avaliador-curriculo-delete');
    
    Route::post('create-avaliacao/{avaliador:uid}', [AvaliadorController::class, 'createAvaliacao'])->name('avaliador-create-avaliacao');
    Route::post('update-avaliacao/{avaliacao:uid}', [AvaliadorController::class, 'updateAvaliacao'])->name('avaliador-update-avaliacao');

    Route::post('create-qualificacao/{avaliador:uid}', [AvaliadorController::class, 'createQualificacao'])->name('avaliador-create-qualificacao');
    Route::post('update-qualificacao/{qualificacao:uid}', [AvaliadorController::class, 'updateQualificacao'])->name('avaliador-update-qualificacao');
    Route::post('delete-qualificacao/{qualificacao:uid}', [AvaliadorController::class, 'deleteQualificacao'])->name('avaliador-delete-qualificacao');
    
    Route::post('create-area/{avaliador:uid}', [AvaliadorController::class, 'createArea'])->name('avaliador-create-area');
    Route::post('update-area/{area:uid}', [AvaliadorController::class, 'updateArea'])->name('avaliador-update-area');
    Route::post('delete-area/{area:uid}', [AvaliadorController::class, 'deleteArea'])->name('avaliador-delete-area');
    
    Route::post('create-certificado/{avaliador:uid}', [AvaliadorController::class, 'createCertificado'])->name('avaliador-create-certificado');
    Route::post('update-certificado/{certificado:uid}', [AvaliadorController::class, 'updateCertificado'])->name('avaliador-update-certificado');
    Route::post('delete-certificado/{certificado:uid}', [AvaliadorController::class, 'deleteCertificado'])->name('avaliador-delete-certificado');

    Route::post('create-status/{avaliador:uid}', [AvaliadorController::class, 'createStatus'])->name('avaliador-create-status');
    Route::post('update-status/{status:uid}', [AvaliadorController::class, 'updateStatus'])->name('avaliador-update-status');
    Route::post('delete-status/{status:uid}', [AvaliadorController::class, 'deleteStatus'])->name('avaliador-delete-status');

  });

  /* Laboratorios */
  Route::group(['prefix' => 'laboratorios', 'middleware' => 'permission:funcionario,admin'], function () {
    Route::get('index', [LaboratorioController::class, 'index'])->name('laboratorio-index');
    Route::get('insert/{laboratorio:uid?}', [LaboratorioController::class, 'insert'])->name('laboratorio-insert');
    Route::post('create', [LaboratorioController::class, 'create'])->name('laboratorio-create');
    Route::post('update/{laboratorio:uid}', [LaboratorioController::class, 'update'])->name('laboratorio-update');
    Route::post('delete/{laboratorio:uid}', [LaboratorioController::class, 'delete'])->name('laboratorio-delete');

    Route::post('save-interno/{laboratorio_interno:uid?}', [LaboratorioController::class, 'saveInterno'])->name('laboratorio-save-interno');
    Route::post('delete-interno/{laboratorio_interno:uid?}', [LaboratorioController::class, 'deleteInterno'])->name('laboratorio-delete-interno');

  });



  /**
   *  Interlaboratoriais
   */
  Route::group(['prefix' => 'interlab', 'middleware' => 'permission:funcionario,admin'], function () {
    Route::get('index', [InterlabController::class, 'index'])->name('interlab-index');
    Route::get('insert/{interlab:uid?}', [InterlabController::class, 'insert'])->name('interlab-insert');
    Route::post('create', [InterlabController::class, 'create'])->name('interlab-create');
    Route::post('update/{interlab:uid}', [InterlabController::class, 'update'])->name('interlab-update');
    Route::post('delete/{interlab:uid}', [InterlabController::class, 'delete'])->name('interlab-delete');
    Route::post('delete-folder/{interlab:uid}', [InterlabController::class, 'folderDelete'])->name('interlab-folder-delete');
  });

  /* Agenda de interlab */
  Route::group(['prefix' => 'agenda-interlab', 'middleware' => 'permission:funcionario,admin'], function () {
    Route::get('index', [AgendaInterlabController::class, 'index'])->name('agenda-interlab-index');
    Route::get('insert/{agendainterlab:uid?}', [AgendaInterlabController::class, 'insert'])->name('agenda-interlab-insert');
    Route::post('create', [AgendaInterlabController::class, 'create'])->name('agenda-interlab-create');
    Route::post('update/{agendainterlab:uid}', [AgendaInterlabController::class, 'update'])->name('agenda-interlab-update');
    Route::post('delete/{agendainterlab:uid}', [AgendaInterlabController::class, 'delete'])->name('agenda-interlab-delete');
    
    /* Despesas */
    Route::post('salva-despesa', [AgendaInterlabController::class, 'salvaDespesa'])->name('salvar-despesa');
    Route::get('duplicar-despesa/{despesa:uid}', [AgendaInterlabController::class, 'duplicarDespesa'])->name('agenda-interlab-duplicar-despesa');
    Route::post('delete-despesa/{despesa:uid}', [AgendaInterlabController::class, 'deleteDespesa'])->name('delete-despesa');
    
    /* Parametros */
    Route::post('salva-parametro', [AgendaInterlabController::class, 'salvaParametro'])->name('salva-parametro');
    Route::post('delete-parametro/{parametro}', [AgendaInterlabController::class, 'deleteParametro'])->name('delete-parametro');
    
    /* RodadascursoInscricao */
    Route::post('salva-rodada', [AgendaInterlabController::class, 'salvaRodada'])->name('salvar-rodada');
    Route::post('delete-rodada/{rodada:uid}', [AgendaInterlabController::class, 'deleteRodada'])->name('delete-rodada');
    
    Route::get('export/{agendainterlab:uid}', [AgendaInterlabController::class, 'exportLaboratoriosToXLS'])->name('interlab-relatorio-inscritos');
  });

  /* Inscricao em interlaboratoriais */
  Route::group(['prefix' => 'inscricao-interlab'], function () {
    Route::post('confirmacao', [InscricaoInterlabController::class, 'confirmaInscricao'])->name('confirma-inscricao-interlab');
    Route::post('informa-empresa', [InscricaoInterlabController::class, 'informaEmpresa'])->name('informa-empresa-interlab');
    Route::post('cancela-inscricao/{inscrito:uid}', [InscricaoInterlabController::class, 'cancelaInscricao'])->name('cancela-inscricao-interlab');
    Route::post('salvar-inscrito/{inscrito:uid}', [InscricaoInterlabController::class, 'salvaInscrito'])->name('salvar-inscrito-interlab');
    Route::post('envia-convite', [InscricaoInterlabController::class, 'enviaConvite'])->name('envia-convite-interlab');
    Route::post('limpa-sessao', [InscricaoInterlabController::class, 'limpaSessao'])->name('limpa-sessao-interlab');
  });


  /**
   * Cadastros adicionais
   */

  /* Area de atuação */
  Route::group(['prefix' => 'area-atuacao', 'middleware' => 'permission:funcionario,admin'], function () {
    Route::get('index', [AreaAtuacaoController::class, 'index'])->name('area-atuacao-index');
    Route::post('store', [AreaAtuacaoController::class, 'store'])->name('area-atuacao-store');
    Route::post('update/{areaAtuacao:uid}', [AreaAtuacaoController::class, 'update'])->name('area-atuacao-update');
    Route::post('delete/{areaAtuacao:uid}', [AreaAtuacaoController::class, 'destroy'])->name('area-atuacao-delete');
  });

  /* Lista de materiais/padrões */
  Route::group(['prefix' => 'materiais-padroes', 'middleware' => 'permission:funcionario,admin'], function () {
    Route::get('index', [MateriaisPadroesController::class, 'index'])->name('materiais-padroes-index');
    Route::post('store', [MateriaisPadroesController::class, 'store'])->name('materiais-padroes-store');
    Route::post('update/{materiaisPadroes:uid}', [MateriaisPadroesController::class, 'update'])->name('materiais-padroes-update');
    Route::post('delete/{materiaisPadroes:uid}', [MateriaisPadroesController::class, 'destroy'])->name('materiais-padroes-delete');
  });

  /* Cadastro de parâmetros*/
  Route::group(['prefix' => 'parametros', 'middleware' => 'permission:funcionario,admin'], function () {
    Route::get('index', [ParametrosController::class, 'index'])->name('parametros-index');
    Route::post('store', [ParametrosController::class, 'store'])->name('parametro-store');
    Route::post('update/{parametro:uid}', [ParametrosController::class, 'update'])->name('parametro-update');
    Route::post('delete/{parametro:uid}', [ParametrosController::class, 'destroy'])->name('parametro-delete');
  });

  /* Cadastro de tipos de avaliação*/
  Route::group(['prefix' => 'tipos-avaliacao', 'middleware' => 'permission:funcionario,admin'], function () {
    Route::get('index', [TipoAvaliacaoController::class, 'index'])->name('tipo-avaliacao-index');
    Route::post('store', [TipoAvaliacaoController::class, 'store'])->name('tipo-avaliacao-store');
    Route::post('update/{tipoAvaliacao:uid}', [TipoAvaliacaoController::class, 'update'])->name('tipo-avaliacao-update');
    Route::post('delete/{tipoAvaliacao:uid}', [TipoAvaliacaoController::class, 'destroy'])->name('tipo-avaliacao-delete');
  });

  /* Cadastro de bancos*/
  Route::group(['prefix' => 'banco', 'middleware' => 'permission:funcionario,admin'], function () {
    Route::get('index', [BancosController::class, 'index'])->name('banco-index');
    Route::post('store', [BancosController::class, 'store'])->name('banco-store');
    Route::post('update/{banco:uid}', [BancosController::class, 'update'])->name('banco-update');
    Route::post('delete/{banco:uid}', [BancosController::class, 'destroy'])->name('banco-delete');
  });

  /* Cadastro de centro de custo*/
  Route::group(['prefix' => 'centro-custo', 'middleware' => 'permission:funcionario,admin'], function () {
    Route::get('index', [CentroCustoController::class, 'index'])->name('centro-custo-index');
    Route::post('store', [CentroCustoController::class, 'store'])->name('centro-custo-store');
    Route::post('update/{centroCusto:uid}', [CentroCustoController::class, 'update'])->name('centro-custo-update');
    Route::post('delete/{centroCusto:uid}', [CentroCustoController::class, 'destroy'])->name('centro-custo-delete');
  });

  /* Cadastro de centro de custo*/
  Route::group(['prefix' => 'modalidade-pagamento', 'middleware' => 'permission:funcionario,admin'], function () {
    Route::get('index', [ModalidadePagamentoController::class, 'index'])->name('modalidade-pagamento-index');
    Route::post('store', [ModalidadePagamentoController::class, 'store'])->name('modalidade-pagamento-store');
    Route::post('update/{modalidadePagamento:uid}', [ModalidadePagamentoController::class, 'update'])->name('modalidade-pagamento-update');
    Route::post('delete/{modalidadePagamento:uid}', [ModalidadePagamentoController::class, 'destroy'])->name('modalidade-pagamento-delete');
  });

  /* Cadastro de plano de contas */
  Route::group(['prefix' => 'plano-conta', 'middleware' => 'permission:funcionario,admin'], function () {
    Route::get('index', [PlanoContaController::class, 'index'])->name('plano-conta-index');
    Route::post('store', [PlanoContaController::class, 'store'])->name('plano-conta-store');
    Route::post('update/{planoconta:uid}', [PlanoContaController::class, 'update'])->name('plano-conta-update');
    Route::post('delete/{planoconta:uid}', [PlanoContaController::class, 'destroy'])->name('plano-conta-delete');
  });

  /* Financeiro */
  Route::group(['prefix' => 'financeiro', 'middleware' => 'permission:funcionario,admin'], function () {
    Route::get('lancamento/index', [LancamentoFinanceiroController::class, 'index'])->name('lancamento-financeiro-index');
    Route::get('lancamento/insert/{lancamento:uid?}', [LancamentoFinanceiroController::class, 'insert'])->name('lancamento-financeiro-insert');
    Route::post('lancamento/store', [LancamentoFinanceiroController::class, 'store'])->name('lancamento-financeiro-store');
    Route::post('lancamento/update/{lancamento:uid}', [LancamentoFinanceiroController::class, 'update'])->name('lancamento-financeiro-update');
    Route::post('lancamento/delete/{lancamento:uid}', [LancamentoFinanceiroController::class, 'delete'])->name('lancamento-financeiro-delete');

    Route::get('areceber/index', [LancamentoFinanceiroController::class, 'areceber'])->name('a-receber-index');

  });


  /*
   * Site 
   */

  // Rotas de noticia e galeria
  Route::group(['prefix' => 'post', 'middleware' => 'permission:funcionario,admin'], function () {
    Route::get('noticias', [PostController::class, 'indexNoticias'])->name('noticia-index'); // tela de lista
    Route::get('galeria', [PostController::class, 'indexGaleria'])->name('galeria-index'); // tela de lista
    Route::get('noticia-insert/{post:slug?}', [PostController::class, 'noticiaInsert'])->name('noticia-insert'); // tela de edicao
    Route::get('galeria-insert/{post:slug?}', [PostController::class, 'galeriaInsert'])->name('galeria-insert'); // tela de edicao
    Route::post('create', [PostController::class, 'create'])->name('post-create'); // tela de cadastro
    Route::post('update/{post:slug}', [PostController::class, 'update'])->name('post-update'); // salvar
    Route::post('delete/{post:id}', [PostController::class, 'delete'])->name('post-delete');
    Route::post('image-upload', [PostController::class, 'storeImage'])->name('image-upload');
    Route::post('delete-thumb/{post:id}', [PostController::class, 'thumbDelete'])->name('thumb-delete'); //deletar thumb

    Route::delete('post-media/{id}', [PostMediaController::class, 'destroy'])->name('post-media.destroy'); //apaga postMedia
  });

  // Rotas de upload de arquivos
  Route::group(['prefix' => 'downloads', 'middleware' => 'permission:funcionario,admin'], function () {
    Route::get('index', [DownloadController::class, 'index'])->name('download-index');
    Route::get('insert/{download:uid?}', [DownloadController::class, 'insert'])->name('download-insert');
    Route::post('create', [DownloadController::class, 'create'])->name('download-create');
    Route::post('update/{download:uid}', [DownloadController::class, 'update'])->name('download-update');
    Route::post('delete/{download:uid}', [DownloadController::class, 'delete'])->name('download-delete');
  });

});