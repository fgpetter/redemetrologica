@php
if(isset($_GET['area'])) {
  $getarea = preg_replace('/[^A-Za-z0-9\-]/', '', $_GET['area']);
}
if(isset($_GET['laboratorio'])) {
  $getlab = preg_replace('/[^A-Za-z0-9\-]/', '', $_GET['laboratorio']);
}
@endphp

@extends('site.layouts.layout-site')
@section('title') Laboratórios Reconhecidos @endsection
@section('content')
  {{-- banner --}}
  <div class="SiteCards__bgimage p-5 text-center  text-white "
    style="background-size: cover; background-image: url('{{ asset('build/images/site/BANNER-HOME-TOPO-2698-x-726-px_5-1.png') }}');height:100%; width:100%;">
    <div class="container h-full">
      <h1 class="text-white"> Laboratórios Reconhecidos</h1>

    </div>
  </div>
  {{-- banner --}}

  {{-- table --}}
  <div class="container mt-4">
    <table class="table table-responsive table-striped align-middle table-nowrap">
      <thead>
        <h5 class="h5">Filtros</h5>
        <tr>
          <th>
            <select name="laboratorio" id="laboratorio" class="form-select form-select-sm" onchange="searchSelect(event, window.location.href, 'laboratorio')">
            <option value="">Filtrar por laboratório</option>
              @foreach ($laboratorios->sortBy('nome_laboratorio') as $laboratorio)
                <option @selected($laboratorio->uid == ($getlab ?? null)) value="{{ $laboratorio->uid }}">{{ $laboratorio->nome_laboratorio }}</option>
              @endforeach
            </select>
          </th>
          <th scope="col">
            <select name="area_atuacao_id" id="area_atuacao_id" class="form-select form-select-sm" onchange="searchSelect(event, window.location.href, 'area')">
              <option value="">Filtrar por área de atuação</option>
              @foreach ($areas_atuacao as $area)
                <option @selected($area->uid == ($getarea ?? null) ) value="{{ $area->uid }}">{{ $area->descricao }}</option>
              @endforeach
            </select>
            </th>
          <th scope="col"></th>
        </tr>
      </thead>
      <thead>
        <tr>
          <th scope="col">Entidade</th>
          <th scope="col">Laboratório</th>
          <th scope="col">Cidade</th>
          <th scope="col">Outras informações</th>
        </tr>
      </thead>

      <tbody>
        @forelse ($laboratorios_internos as $laboratorio_interno)
          <tr>
            <td>
              <a href="{{ asset('laboratorios-certificados/' . $laboratorio_interno->certificado) }}" target="_blank">
                <i class="ph-file-arrow-down align-middle me-1" style="font-size: 1.4rem"></i>
                {{ $laboratorio_interno->laboratorio->nome_laboratorio }}
              </a>
            </td>
            <td>{{ $laboratorio_interno->area->descricao }}</td>
            <td>
              {{ $laboratorio_interno->laboratorio->pessoa?->enderecos->first()->cidade ?? null }}
            </td>
            <td></td>
          </tr>
        @empty
          <tr>
            <td colspan="5" class="text-center">Não há laboratorios cadastrados</td>
          </tr>
        @endforelse
      </tbody>

    </table>
    <div class="row mt-3 w-100">
      {!! $laboratorios_internos->withQueryString()->links('pagination::bootstrap-5') !!}
    </div>

  </div>
  {{-- table --}}
  
@endsection