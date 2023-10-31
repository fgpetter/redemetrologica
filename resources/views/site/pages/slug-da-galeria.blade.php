@extends('site.layouts.layout-site')
@section('content')
    {{-- banner --}}
    <x-site.component-title title='Galeria de fotos' />
    {{-- banner --}}


    {{-- main --}}
    <x-site.component-post />

    {{-- menu lateral --}}
    <div class="col-md-2 ">
        {{-- busca --}}
        <div class="container text-start my-5">
            <div class="row">

                <div class="col">
                    <div class="btn-toolbar d-flex justify-content-end  " role="toolbar"
                        aria-label="Toolbar with button groups">
                        <div class="input-group border-bottom pb-4">
                            <input type="text" class="form-control" placeholder="PESQUISAR POR"
                                aria-label="Input group example" aria-describedby="btnGroupAddon2">
                            <div class="input-group-text" id="btnGroupAddon2"><i class="bi bi-search"></i></div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
        {{-- busca --}}

        <div class="row border-bottom py-2">
            <div class="col ">
                <h3>Posts recentes</h3>
                <a class="d-inline-block" href="">Lorem, ipsum .</a>
                <a class="d-inline-block" href="">Lorem, ipsum dolor.</a>
                <a class="d-inline-block" href="">Lorem, ipsum dolor.</a>
                <a class="d-inline-block" href="">Lorem, .</a>
                <a class="d-inline-block" href="">Lorem, ipsum dolor.</a>
                <a class="d-inline-block" href="">Lorem, dolor.</a>
                <a class="d-inline-block" href="">Lorem, ipsum dolor.</a>
            </div>
        </div>
        <div class="row border-bottom py-2">
            <div class="col">
                <h3>Categorias</h3>
                <a class="d-inline-block" href="">Lorem, ipsum .</a>
                <a class="d-inline-block" href="">Lorem, ipsum dolor.</a>
                <a class="d-inline-block" href="">Lorem, ipsum dolor.</a>
                <a class="d-inline-block" href="">Lorem, .</a>
                <a class="d-inline-block" href="">Lorem, ipsum dolor.</a>
                <a class="d-inline-block" href="">Lorem, dolor.</a>
                <a class="d-inline-block" href="">Lorem, ipsum dolor.</a>
            </div>
        </div>
        <div class="col border-bottom py-2">
            <h3>TAGS</h3>
            <span class="my-1 badge bg-primary">lorem</span>
            <span class="my-1 badge bg-primary ">Lorem, ipsum.</span>
            <span class="my-1 badge bg-primary ">Lorem.</span>
            <span class="my-1 badge bg-primary ">Lorem ipsum dolor sit.</span>
            <span class="my-1 badge bg-primary ">Lorem, ipsum dolor.</span>
            <span class="my-1 badge bg-primary ">Primary</span>
            <span class="my-1 badge bg-primary ">Lorem ipsum dolor sit amet.</span>
        </div>


    </div>
    {{-- menu lateral --}}
    </div>
    </div>
    {{-- main --}}
@endsection
