@extends('layouts.master')

@section('title') @lang('translation.starter') @endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1') Inicio @endslot
        @slot('title') Painel @endslot
    @endcomponent
    @if(auth()->user()->pessoa->funcionario)
        {{-- carega componentes referentes ao funcionário e área --}}
        é funcionário
        
    @else

        @if ( session('curso') )
            <x-painel.painelcliente.confirma-inscricao />
        @endif

        @if ( auth()->user()->pessoa->cursos->count() > 0 )
            Tem cursos
        @endif

    
    @endif

@endsection

@section('script')
@endsection