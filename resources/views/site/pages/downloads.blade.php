@extends('site.layouts.layout-site')
@section('content')
    {{-- banner --}}
    <x-site.component-title title='Download' />
    {{-- banner --}}
    {{-- table --}}
    <div class="container">
        <div class="card-body">
            <div id="table-gridjs">
                <div role="complementary" class="gridjs gridjs-container" style="width: 100%;">
                    <div class="gridjs-head">
                        <div class="gridjs-search"><input type="search" placeholder="Pesquisar..."
                                aria-label="Type a keyword..." class="gridjs-input gridjs-search-input"></div>
                    </div>
                    <div class="gridjs-wrapper" style="height: auto;">
                        <table role="grid" class="gridjs-table" style="height: auto;">
                            <thead class="gridjs-thead">
                                <tr class="gridjs-tr">
                                    <th data-column-id="titulo" class="gridjs-th gridjs-th-sort" tabindex="0"
                                        style="width: 180px;">
                                        <div class="gridjs-th-content">Titulo</div>
                                    </th>
                                    <th data-column-id="descrição" class="gridjs-th gridjs-th-sort" tabindex="0"
                                        style="width: 280px;">
                                        <div class="gridjs-th-content">Descrição</div>
                                    </th>
                                    <th data-column-id="categoria" class="gridjs-th gridjs-th-sort" tabindex="0"
                                        style="width: 30px;">
                                        <div class="gridjs-th-content">Categoria</div>
                                    </th>

                                </tr>
                            </thead>
                            <tbody class="gridjs-tbody">
                                <tr class="gridjs-tr">
                                    <td data-column-id="titulo" class="gridjs-td">FR61 rev1_FORMULARIO PARA RECLAMAÇÃO DE
                                        CLIENTES</td>
                                    <td data-column-id="descrição" class="gridjs-td">Use este formulário para reclamação de
                                        clientes da Rede Metrológica RS</td>
                                    <td data-column-id="categoria" class="gridjs-td">Cursos</td>
                                </tr>
                                <tr class="gridjs-tr">
                                    <td data-column-id="titulo" class="gridjs-td">FR61 rev1_FORMULARIO PARA RECLAMAÇÃO DE
                                        CLIENTES</td>
                                    <td data-column-id="descrição" class="gridjs-td">Use este formulário para reclamação de
                                        clientes da Rede Metrológica RS</td>
                                    <td data-column-id="categoria" class="gridjs-td">Cursos</td>
                                </tr>
                                <tr class="gridjs-tr">
                                    <td data-column-id="titulo" class="gridjs-td">FR61 rev1_FORMULARIO PARA RECLAMAÇÃO DE
                                        CLIENTES</td>
                                    <td data-column-id="descrição" class="gridjs-td">Use este formulário para reclamação de
                                        clientes da Rede Metrológica RS</td>
                                    <td data-column-id="categoria" class="gridjs-td">Cursos</td>
                                </tr>
                                <tr class="gridjs-tr">
                                    <td data-column-id="titulo" class="gridjs-td">FR61 rev1_FORMULARIO PARA RECLAMAÇÃO DE
                                        CLIENTES</td>
                                    <td data-column-id="descrição" class="gridjs-td">Use este formulário para reclamação de
                                        clientes da Rede Metrológica RS</td>
                                    <td data-column-id="categoria" class="gridjs-td">Cursos</td>
                                </tr>

                            </tbody>
                        </table>
                    </div>
                    <div class="gridjs-footer">
                        <div class="gridjs-pagination">
                            <div role="status" aria-live="polite" class="gridjs-summary" title="Page 1 of 2">
                                Mostrando <b>1</b> de <b>2</b> dos <b>2</b> resultados</div>
                            <div class="gridjs-pages"><button tabindex="0" role="button" disabled="" title="Previous"
                                    aria-label="Previous" class="">Anterior</button><button tabindex="0"
                                    role="button" class="gridjs-currentPage" title="Page 1"
                                    aria-label="Page 1">1</button><button tabindex="0" role="button" class=""
                                    title="Page 2" aria-label="Page 2">2</button><button tabindex="0" role="button"
                                    title="Next" aria-label="Next" class="">Proximo</button></div>
                        </div>
                    </div>
                    <div id="gridjs-temp" class="gridjs-temp"></div>
                </div>
            </div>
        </div>
    </div>
    {{-- table --}}
@endsection
