@php
  if (isset($_GET['area'])) {
      $getarea = preg_replace('/[^a-zA-Zà-úÀ-Ú0-9\-\s\(\)\/]/', '', $_GET['area']);
  }
  if (isset($_GET['laboratorio'])) {
      $getlab = preg_replace('/[^a-zA-Zà-úÀ-Ú0-9\-\s\(\)\/]/', '', $_GET['laboratorio']);
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
    <table class="table table-responsive table-striped align-middle">
      <thead>
        <h5 class="h5">Filtros</h5>
        <tr>
          
          <th>
            <input type="text" name="laboratorio" id="laboratorio" class="form-control form-control-sm"
              placeholder="Digite o nome do laboratório e pressione ENTER para buscar." value="{{ $getlab ?? '' }}"
              onkeypress="if(event.keyCode === 13){ search(event, window.location.href, 'laboratorio'); }">
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
          <th scope="col">Área de Atuação</th>
          <th scope="col">Nome do Laboratório</th>
        </tr>
      </thead>

      <tbody>
        @forelse ($laboratorios_internos as $laboratorio_interno)
          <tr>
            <td>
              <a href="{{ route('lab-interno-show', $laboratorio_interno->uid) }}">
                {{ $laboratorio_interno->laboratorio->pessoa->nome_razao }}
              </a>
            </td>
            <td>{{ $laboratorio_interno->area->descricao }}</td>
            <td>{{ $laboratorio_interno->nome }}</td>
          </tr>
        @empty
          <tr>
            <td colspan="3" class="text-center">Não há laboratorios cadastrados</td>
          </tr>
        @endforelse
      </tbody>

    </table>

  </div>
  {{-- table --}}
  
@endsection