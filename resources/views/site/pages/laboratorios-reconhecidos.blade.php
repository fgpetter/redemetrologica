@extends('site.layouts.layout-site')
@section('content')
    {{-- banner inicial --}}
    <div class="card text-bg-dark">
        <img src="{{ asset('build\images\site\lab-reconhecido-01.png') }}" class="card-img" alt="...">

        <div class="card-img-overlay d-flex justify-content-center">
            <div class="align-self-center text-center ">
                <h1 class="text-warning"><strong>LABORATÓRIOS RECONHECIDOS</strong></h1>
            </div>
        </div>

    </div>
    {{-- banner inicial --}}

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
                                    <th data-column-id="Entidade" class="gridjs-th gridjs-th-sort" tabindex="0"
                                        style="width: 80px;">
                                        <div class="gridjs-th-content">Entidade</div>
                                    </th>
                                    <th data-column-id="Laboratório" class="gridjs-th gridjs-th-sort" tabindex="0"
                                        style="width: 150px;">
                                        <div class="gridjs-th-content">Laboratório</div>
                                    </th>
                                    <th data-column-id="Cidade" class="gridjs-th gridjs-th-sort" tabindex="0"
                                        style="width: 220px;">
                                        <div class="gridjs-th-content">Cidade</div>
                                    </th>
                                    <th data-column-id="Outrsinf" class="gridjs-th gridjs-th-sort" tabindex="0"
                                        style="width: 220px;">
                                        <div class="gridjs-th-content">Outras informações</div>
                                    </th>

                                </tr>
                            </thead>
                            <tbody class="gridjs-tbody">
                                <tr class="gridjs-tr">
                                    <td data-column-id="Entidade" class="gridjs-td">3D METROLOGIA</td>
                                    <td data-column-id="Laboratório" class="gridjs-td">DIMENSIONAL</td>
                                    <td data-column-id="Cidade" class="gridjs-td">SAO LEOPOLDO</td>
                                    <td data-column-id="Outrasinf" class="gridjs-td">Bônus Metrologia</td>
                                </tr>
                                <tr class="gridjs-tr">
                                    <td data-column-id="Entidade" class="gridjs-td">3D METROLOGIA</td>
                                    <td data-column-id="Laboratório" class="gridjs-td">DIMENSIONAL</td>
                                    <td data-column-id="Cidade" class="gridjs-td">SAO LEOPOLDO</td>
                                    <td data-column-id="Outrasinf" class="gridjs-td">Bônus Metrologia</td>
                                </tr>
                                <tr class="gridjs-tr">
                                    <td data-column-id="Entidade" class="gridjs-td">3D METROLOGIA</td>
                                    <td data-column-id="Laboratório" class="gridjs-td">DIMENSIONAL</td>
                                    <td data-column-id="Cidade" class="gridjs-td">SAO LEOPOLDO</td>
                                    <td data-column-id="Outrasinf" class="gridjs-td">Bônus Metrologia</td>
                                </tr>
                                <tr class="gridjs-tr">
                                    <td data-column-id="Entidade" class="gridjs-td">3D METROLOGIA</td>
                                    <td data-column-id="Laboratório" class="gridjs-td">DIMENSIONAL</td>
                                    <td data-column-id="Cidade" class="gridjs-td">SAO LEOPOLDO</td>
                                    <td data-column-id="Outrasinf" class="gridjs-td">Bônus Metrologia</td>
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
