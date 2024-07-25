<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PostController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CursoController;
use App\Http\Controllers\BancosController;
use App\Http\Controllers\PessoaController;
use App\Http\Controllers\UnidadeController;
use App\Http\Controllers\DownloadController;
use App\Http\Controllers\EnderecoController;
use App\Http\Controllers\InterlabController;
use App\Http\Controllers\AvaliadorController;
use App\Http\Controllers\InstrutorController;
use App\Http\Controllers\PostMediaController;
use App\Http\Controllers\ParametrosController;
use App\Http\Controllers\PlanoContaController;
use App\Http\Controllers\AgendaCursoController;
use App\Http\Controllers\AreaAtuacaoController;
use App\Http\Controllers\CentroCustoController;
use App\Http\Controllers\FuncionarioController;
use App\Http\Controllers\LaboratorioController;
use App\Http\Controllers\DadoBancarioController;
use App\Http\Controllers\TipoAvaliacaoController;
use App\Http\Controllers\AgendaInterlabController;
use App\Http\Controllers\InscricaoCursoController;
use App\Http\Controllers\AgendaAvaliacaoController;
use App\Http\Controllers\MateriaisPadroesController;
use App\Http\Controllers\ModalidadePagamentoController;
use App\Http\Controllers\LancamentoFinanceiroController;

Auth::routes();
//Route::get('index/{locale}', [App\Http\Controllers\HomeController::class, 'lang']);
Route::get('/', [App\Http\Controllers\HomeController::class, 'root'])->name('root');

/* Rotas estáticas */
Route::get('home', [App\Http\Controllers\HomeController::class, 'root'])->name('root');
Route::view('noticias', 'site.pages.noticias');
Route::view('galerias', 'site.pages.galerias');
Route::view('associe-se', 'site.pages.associe-se');

Route::view('interlaboratoriais', 'site.pages.interlaboratoriais');
Route::view('laboratorios-avaliacao', 'site.pages.laboratorios-avaliacao');
Route::get('laboratorios-reconhecidos', [LaboratorioController::class, 'siteIndex']);
Route::view('bonus-metrologia', 'site.pages.bonus-metrologia');
Route::get('laboratorios-downloads', [DownloadController::class, 'siteIndex']);
Route::view('fale-conosco', 'site.pages.fale-conosco');
Route::view('slug-da-noticia', 'site.pages.slug-da-noticia');
Route::view('slug-da-galeria', 'site.pages.slug-da-galeria');
Route::view('sobre', 'site.pages.sobre');
Route::view('slug-interlaboratoriais', 'site.pages.slug-interlaboratoriais');
Route::view('slug-cursos', 'site.pages.slug-cursos');

/*Rotas das slugs (noticia e galeria) */
Route::get('noticias', [PostController::class, 'ListNoticias'])->name('show-list'); //mostra lista de noticias
Route::get('galerias', [PostController::class, 'ListGalerias'])->name('show-list'); //mostra lista de galerias
Route::get('noticia/{slug}', [PostController::class, 'show'])->name('noticia-show'); //mostra noticia
Route::get('galeria/{slug}', [PostController::class, 'show'])->name('galeria-show'); //mostra galeria


/*Rotas de cursos */
Route::get('cursos', [AgendaCursoController::class, 'listCursosAgendados'])->name('cursos-agendados-list');
Route::get('cursos/{agendacurso:uid}', [AgendaCursoController::class, 'showCursoAgendado'])->name('curso-agendado-show');
Route::get('curso/inscricao', [InscricaoCursoController::class, 'cursoInscricao'])->name('curso-inscricao');

/* Rotas do template */
// Route::get('{any}', [App\Http\Controllers\HomeController::class, 'index'])->name('index');

/* Rotas do painel */
Route::prefix('painel')->middleware('auth')->group(function () {

  Route::get('/', function () {
    return view('index');
  });

  /* Usuários */
  Route::group(['prefix' => 'user'], function () {
    Route::get('index', [UserController::class, 'index'])->name('user-index');
    Route::get('edit/{user}', [UserController::class, 'view'])->name('user-edit');
    Route::post('create', [UserController::class, 'create'])->name('user-create');
    Route::post('update/{user}', [UserController::class, 'update'])->name('user-update');
    Route::post('delete/{user}', [UserController::class, 'delete'])->name('user-delete');
  });

  /**
   * Pessoas 
   */
  Route::group(['prefix' => 'pessoa'], function () {
    Route::get('index', [PessoaController::class, 'index'])->name('pessoa-index');
    Route::get('insert/{pessoa:uid?}', [PessoaController::class, 'insert'])->name('pessoa-insert');
    Route::post('create', [PessoaController::class, 'create'])->name('pessoa-create');
    Route::post('update/{pessoa:uid}', [PessoaController::class, 'update'])->name('pessoa-update');
    Route::post('delete/{pessoa:uid}', [PessoaController::class, 'delete'])->name('pessoa-delete');
    Route::post('associa-empresa/{pessoa:uid}', [PessoaController::class, 'associaEmpresa'])->name('pessoa-associa-empresa');
  });

  /* Endereços */
  Route::group(['prefix' => 'endereco'], function () {
    Route::post('create', [EnderecoController::class, 'create'])->name('endereco-create');
    Route::post('update/{endereco:uid}', [EnderecoController::class, 'update'])->name('endereco-update');
    Route::post('delete/{endereco:uid}', [EnderecoController::class, 'delete'])->name('endereco-delete');
  });

  /* Unidades */
  Route::group(['prefix' => 'unidade'], function () {
    Route::post('create', [UnidadeController::class, 'create'])->name('unidade-create');
    Route::post('update/{unidade:uid}', [UnidadeController::class, 'update'])->name('unidade-update');
    Route::post('delete/{unidade:uid}', [UnidadeController::class, 'delete'])->name('unidade-delete');
  });

  /* Funcionarios */
  Route::group(['prefix' => 'funcionario'], function () {
    Route::get('index', [FuncionarioController::class, 'index'])->name('funcionario-index');
    Route::get('insert/{funcionario:uid?}', [FuncionarioController::class, 'insert'])->name('funcionario-insert');
    Route::post('create', [FuncionarioController::class, 'create'])->name('funcionario-create');
    Route::post('update/{funcionario:uid}', [FuncionarioController::class, 'update'])->name('funcionario-update');
    Route::post('delete/{funcionario:uid}', [FuncionarioController::class, 'delete'])->name('funcionario-delete');
    Route::post('delete-curriculo/{funcionario:uid}', [FuncionarioController::class, 'curriculoDelete'])->name('curriculo-delete');
  });  

  /* Dados bancários */
  Route::group(['prefix' => 'conta'], function () {
    Route::post('create', [DadoBancarioController::class, 'create'])->name('conta-create');
    Route::post('update/{conta:uid}', [DadoBancarioController::class, 'update'])->name('conta-update');
    Route::post('delete/{conta:uid}', [DadoBancarioController::class, 'delete'])->name('conta-delete');
  });

  /**
   *  Cursos 
   */
  Route::group(['prefix' => 'curso'], function () {
    Route::get('index', [CursoController::class, 'index'])->name('curso-index');
    Route::get('insert/{curso:uid?}', [CursoController::class, 'insert'])->name('curso-insert');
    Route::post('create', [CursoController::class, 'create'])->name('curso-create');
    Route::post('update/{curso:uid}', [CursoController::class, 'update'])->name('curso-update');
    Route::post('delete/{curso:uid}', [CursoController::class, 'delete'])->name('curso-delete');
    Route::post('delete-folder/{curso:uid}', [CursoController::class, 'folderDelete'])->name('curso-folder-delete');
    Route::post('delete-thumb/{curso:uid}', [CursoController::class, 'thumbDelete'])->name('curso-thumb-delete');
  });

  /* Agendamento de cursos*/
  Route::group(['prefix' => 'agendamento-curso'], function () {
    Route::get('index', [AgendaCursoController::class, 'index'])->name('agendamento-curso-index');
    Route::get('insert/{agendacurso:uid?}', [AgendaCursoController::class, 'insert'])->name('agendamento-curso-insert');
    Route::post('create', [AgendaCursoController::class, 'create'])->name('agendamento-curso-create');
    Route::post('update/{agendacurso:uid}', [AgendaCursoController::class, 'update'])->name('agendamento-curso-update');
    Route::post('delete/{agendacurso:uid}', [AgendaCursoController::class, 'delete'])->name('agendamento-curso-delete');
    Route::post('salvar-despesa', [AgendaCursoController::class, 'salvaDespesa'])->name('salvar-despesa');
    Route::post('delete-despesa/{despesa:uid}', [AgendaCursoController::class, 'deleteDespesa'])->name('delete-despesa');
  });

  /* Matricula em cursos */
  Route::group(['prefix' => 'inscricao-curso'], function () {
    Route::post('confirmacao', [InscricaoCursoController::class, 'confirmaInscricao'])->name('confirma-inscricao');
    Route::post('informa-empresa', [InscricaoCursoController::class, 'informaEmpresa'])->name('informa-empresa');
    Route::post('cancela-inscricao', [InscricaoCursoController::class, 'cancelaInscricao'])->name('cancela-inscricao');
    Route::post('salvar-inscrito', [InscricaoCursoController::class, 'salvaInscrito'])->name('salvar-inscrito');
  });

  /* Instrutores*/
  Route::group(['prefix' => 'instrutor'], function () {
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
  Route::group(['prefix' => 'avaliacao'], function () {
    Route::get('index', [AgendaAvaliacaoController::class, 'index'])->name('agendamento-avaliacao-index');
    Route::get('insert/{avaliacao:uid}', [AgendaAvaliacaoController::class, 'insert'])->name('avaliacao-insert');
    Route::post('create', [AgendaAvaliacaoController::class, 'create'])->name('avaliacao-create');
    Route::post('update/{avaliacao:uid}', [AgendaAvaliacaoController::class, 'update'])->name('avaliacao-update');
    Route::post('delete/{avaliacao:uid}', [AgendaAvaliacaoController::class, 'delete'])->name('avaliacao-delete');
    Route::post('save-area/{area:uid?}', [AgendaAvaliacaoController::class, 'saveArea'])->name('avaliacao-save-area');
  });

  /* Avaliadores */
  Route::group(['prefix' => 'avaliador'], function () {
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
  Route::group(['prefix' => 'laboratorios'], function () {
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
  Route::group(['prefix' => 'interlab'], function () {
    Route::get('index', [InterlabController::class, 'index'])->name('interlab-index');
    Route::get('insert/{interlab:uid?}', [InterlabController::class, 'insert'])->name('interlab-insert');
    Route::post('create', [InterlabController::class, 'create'])->name('interlab-create');
    Route::post('update/{interlab:uid}', [InterlabController::class, 'update'])->name('interlab-update');
    Route::post('delete/{interlab:uid}', [InterlabController::class, 'delete'])->name('interlab-delete');
    Route::post('delete-folder/{interlab:uid}', [InterlabController::class, 'folderDelete'])->name('interlab-folder-delete');
    Route::post('delete-thumb/{interlab:uid}', [InterlabController::class, 'thumbDelete'])->name('interlab-thumb-delete');
  });

  /* Agenda de interlab */
  Route::group(['prefix' => 'agenda-interlab'], function () {
    Route::get('index', [AgendaInterlabController::class, 'index'])->name('agenda-interlab-index');
    Route::get('insert/{agendainterlab:uid?}', [AgendaInterlabController::class, 'insert'])->name('agenda-interlab-insert');
    Route::post('create', [AgendaInterlabController::class, 'create'])->name('agenda-interlab-create');
    Route::post('update/{agendainterlab:uid}', [AgendaInterlabController::class, 'update'])->name('agenda-interlab-update');
    Route::post('delete/{agendainterlab:uid}', [AgendaInterlabController::class, 'delete'])->name('agenda-interlab-delete');
    Route::post('salva-material-padrao', [AgendaInterlabController::class, 'salvaMaterialPadrao'])->name('salvar-material-padrao');
    Route::post('delete-material-padrao/{materialPadrao:uid}', [AgendaInterlabController::class, 'deleteMaterialPadrao'])->name('delete-material-padrao');

  });


  
  /**
   * Cadastros adicionais
   */

  /* Area de atuação */
  Route::group(['prefix' => 'area-atuacao'], function () {
    Route::get('index', [AreaAtuacaoController::class, 'index'])->name('area-atuacao-index');
    Route::post('store', [AreaAtuacaoController::class, 'store'])->name('area-atuacao-store');
    Route::post('update/{areaAtuacao:uid}', [AreaAtuacaoController::class, 'update'])->name('area-atuacao-update');
    Route::post('delete/{areaAtuacao:uid}', [AreaAtuacaoController::class, 'destroy'])->name('area-atuacao-delete');
  });

  /* Lista de materiais/padrões */
  Route::group(['prefix' => 'materiais-padroes'], function () {
    Route::get('index', [MateriaisPadroesController::class, 'index'])->name('materiais-padroes-index');
    Route::post('store', [MateriaisPadroesController::class, 'store'])->name('materiais-padroes-store');
    Route::post('update/{materiaisPadroes:uid}', [MateriaisPadroesController::class, 'update'])->name('materiais-padroes-update');
    Route::post('delete/{materiaisPadroes:uid}', [MateriaisPadroesController::class, 'destroy'])->name('materiais-padroes-delete');
  });

  /* Cadastro de parâmetros*/
  Route::group(['prefix' => 'parametros'], function () {
    Route::get('index', [ParametrosController::class, 'index'])->name('parametros-index');
    Route::post('store', [ParametrosController::class, 'store'])->name('parametro-store');
    Route::post('update/{parametro:uid}', [ParametrosController::class, 'update'])->name('parametro-update');
    Route::post('delete/{parametro:uid}', [ParametrosController::class, 'destroy'])->name('parametro-delete');
  });

  /* Cadastro de tipos de avaliação*/
  Route::group(['prefix' => 'tipos-avaliacao'], function () {
    Route::get('index', [TipoAvaliacaoController::class, 'index'])->name('tipo-avaliacao-index');
    Route::post('store', [TipoAvaliacaoController::class, 'store'])->name('tipo-avaliacao-store');
    Route::post('update/{tipoAvaliacao:uid}', [TipoAvaliacaoController::class, 'update'])->name('tipo-avaliacao-update');
    Route::post('delete/{tipoAvaliacao:uid}', [TipoAvaliacaoController::class, 'destroy'])->name('tipo-avaliacao-delete');
  });

  /* Cadastro de bancos*/
  Route::group(['prefix' => 'banco'], function () {
    Route::get('index', [BancosController::class, 'index'])->name('banco-index');
    Route::post('store', [BancosController::class, 'store'])->name('banco-store');
    Route::post('update/{banco:uid}', [BancosController::class, 'update'])->name('banco-update');
    Route::post('delete/{banco:uid}', [BancosController::class, 'destroy'])->name('banco-delete');
  });

  /* Cadastro de centro de custo*/
  Route::group(['prefix' => 'centro-custo'], function () {
    Route::get('index', [CentroCustoController::class, 'index'])->name('centro-custo-index');
    Route::post('store', [CentroCustoController::class, 'store'])->name('centro-custo-store');
    Route::post('update/{centroCusto:uid}', [CentroCustoController::class, 'update'])->name('centro-custo-update');
    Route::post('delete/{centroCusto:uid}', [CentroCustoController::class, 'destroy'])->name('centro-custo-delete');
  });

  /* Cadastro de centro de custo*/
  Route::group(['prefix' => 'modalidade-pagamento'], function () {
    Route::get('index', [ModalidadePagamentoController::class, 'index'])->name('modalidade-pagamento-index');
    Route::post('store', [ModalidadePagamentoController::class, 'store'])->name('modalidade-pagamento-store');
    Route::post('update/{modalidadePagamento:uid}', [ModalidadePagamentoController::class, 'update'])->name('modalidade-pagamento-update');
    Route::post('delete/{modalidadePagamento:uid}', [ModalidadePagamentoController::class, 'destroy'])->name('modalidade-pagamento-delete');
  });

  /* Cadastro de plano de contas */
  Route::group(['prefix' => 'plano-conta'], function () {
    Route::get('index', [PlanoContaController::class, 'index'])->name('plano-conta-index');
    Route::post('store', [PlanoContaController::class, 'store'])->name('plano-conta-store');
    Route::post('update/{planoconta:uid}', [PlanoContaController::class, 'update'])->name('plano-conta-update');
    Route::post('delete/{planoconta:uid}', [PlanoContaController::class, 'destroy'])->name('plano-conta-delete');
  });

  /* Financeiro */
  Route::group(['prefix' => 'financeiro'], function () {
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
  Route::group(['prefix' => 'post'], function () {
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
  Route::group(['prefix' => 'downloads'], function () {
    Route::get('index', [DownloadController::class, 'index'])->name('download-index');
    Route::get('insert/{download:uid?}', [DownloadController::class, 'insert'])->name('download-insert');
    Route::post('create', [DownloadController::class, 'create'])->name('download-create');
    Route::post('update/{download:uid}', [DownloadController::class, 'update'])->name('download-update');
    Route::post('delete/{download:uid}', [DownloadController::class, 'delete'])->name('download-delete');
  });



});
