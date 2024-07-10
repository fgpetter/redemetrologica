@php
if(isset($_GET['categoria'])) {
    $categoria = preg_replace('/[^A-Za-z0-9\-]/', '', $_GET['categoria']);
}
if(isset($_GET['descricao'])) {
    $descricao = preg_replace('/[^A-Za-z0-9\-]/', '', $_GET['descricao']);
}
@endphp

@extends('site.layouts.layout-site')
@section('content')
    {{-- banner --}}
    <div class="SiteCards__bgimage p-5 text-center  text-white "
        style="background-size: cover; background-image: url('{{ asset('build/images/site/BANNER-HOME-TOPO-2698-x-726-px_5-1.png') }}');height:100%; width:100%;">
        <div class="container h-full">
            <h1 class="text-white"> Downloads</h1>

        </div>
    </div>
    {{-- banner --}}

    {{-- table --}}
    <div class="container">
        <table class="table table-responsive table-striped align-middle table-nowrap mb-0">
            <thead>
                <h5 class="h5 mt-3">Filtros</h5>
                <tr>
                    <th>
                        <select name="categoria" id="categoria" class="form-select form-select-sm" onchange="searchSelect(event, window.location.href, 'categoria')">
                            <option value="">Selecione uma categoria</option>
                            <option @selected( isset($categoria) && $categoria == "CURSOS") value="CURSOS">CURSOS</option>
                            <option @selected( isset($categoria) && $categoria == "QUALIDADE") value="QUALIDADE">QUALIDADE</option>
                            <option @selected( isset($categoria) && $categoria == "INTERLAB") value="INTERLAB">INTERLAB</option>
                            <option @selected( isset($categoria) && $categoria == "INSTITUCIONAL") value="INSTITUCIONAL">INSTITUCIONAL</option>
                        </select>
                    </th>
                    <th scope="col">
                        <input type="text" class="form-control form-control-sm"
                            onkeypress="search(event, window.location.href, 'descricao')"
                            placeholder="Buscar por titulo ou descricao" value="{{ $descricao ?? null }}">
                    </th>
                    <th scope="col"></th>
                </tr>
            </thead>
            <thead>
                <tr>
                    <th scope="col">Arquivo</th>
                    <th scope="col">Descrição</th>
                    <th scope="col">Categoria</th>
                </tr>
            </thead>

            <tbody>
                @forelse ($downloads as $download)
                    <tr>
                        <td>
                            <a href="{{ asset('downloads/' . $download->arquivo) }}" target="_blank">
                                <i class="ph-file-arrow-down align-middle me-1" style="font-size: 1.4rem"></i>
                                {{ $download->titulo }}
                            </a>
                        </td>
                        <td>{{ $download->descricao }}</td>
                        <td>{{ $download->categoria }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center">Não há downloads cadastrados</td>
                    </tr>
                @endforelse
            </tbody>

        </table>
    </div>
    {{-- table --}}
@endsection