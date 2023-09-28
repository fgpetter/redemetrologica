<x-layout-site>

    <x-slot name="content">
        {{-- busca --}}
        <div class="container text-start my-5">
            <div class="row">
                <div class="col-10">
                    <h5>Posts populares</h5>
                    <h5>Últimos posts</h5>
                </div>
                <div class="col-2 ">
                    <div class="btn-toolbar d-flex justify-content-end  " role="toolbar"
                        aria-label="Toolbar with button groups">
                        <div class="input-group border-bottom pb-5">
                            <input type="text" class="form-control" placeholder="PESQUISAR POR"
                                aria-label="Input group example" aria-describedby="btnGroupAddon2">
                            <div class="input-group-text" id="btnGroupAddon2"><i class="bi bi-search"></i></div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
        {{-- busca --}}

        {{-- noticias --}}
        <div class="container">
            <div class="row">
                <div class="col-10 border">noticias</div>
                <div class="col-2 border">tags</div>
            </div>
        </div>
        {{-- noticias --}}
    </x-slot>

</x-layout-site>
