@props(['instrutores' => [], 'pessoas' => []])

@php
    $order_name = request('name', 'asc') == 'asc' ? 'desc' : 'asc';
    $order_doc = request('doc', 'asc') == 'asc' ? 'desc' : 'asc';
    $order_data = request('data', 'asc') == 'asc' ? 'desc' : 'asc';
    $busca_nome = request('buscanome', '');
    $busca_doc = request('buscadoc', '');
@endphp

<div class="card">
    <div class="card-body">
        <div class="row">
            <div class="col-12">
                <div class="hstack gap-2 flex-wrap mb-3 justify-content-end">
                    <button class="btn btn-sm btn-success" type="button" data-bs-toggle="collapse"
                        data-bs-target="#collapseExample" aria-expanded="false" aria-controls="collapseExample">
                        <i class="ri-add-line align-bottom me-1"></i> Adicionar Instrutor
                    </button>
                </div>

                <div class="collapse" id="collapseExample">
                    <div class="card mb-3 shadow-none">
                        <div class="card-body">
                            <form action="{{ route('instrutor-create') }}" method="POST">
                                @csrf
                                <div class="row">
                                    <div class="col-10">
                                        <select class="form-control" data-choices name="pessoa_uid"
                                            id="choices-single-default">
                                            <option value="">Selecione na lista</option>
                                            @foreach ($pessoas as $pessoa)
                                                <option value="{{ $pessoa->uid }}">{{ $pessoa->cpf_cnpj }} | {{ $pessoa->nome_razao }}</option>
                                            @endforeach
                                        </select>
                                        @error('pessoa_uid')
                                            <div class="text-warning">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-2">
                                        <button class="btn btn-success" type="submit">Adicionar</button>
                                    </div>
                                </div>
                            </form>
                            <p>Caso a pessoa não esteja cadastrada ainda, <a href="{{ route('pessoa-insert') }}">Clique Aqui</a></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>


        <div class="table-responsive" style="min-height: 25vh">
            <table class="table table-responsive table-striped align-middle table-nowrap mb-0">
                <thead>
                    <tr>
                        <th scope="col"></th>
                        <th scope="col">
                            <input type="text" class="form-control form-control-sm"
                                onkeypress="search(event, window.location.href, 'buscanome')"
                                placeholder="Buscar por nome" value="{{ $busca_nome }}">
                        </th>
                        <th scope="col">
                            <input type="text" class="form-control form-control-sm"
                                onkeypress="search(event, window.location.href, 'buscadoc')"
                                placeholder="Buscar por documento" value="{{ $busca_doc }}">
                        </th>
                        <th scope="col"></th>
                        <th scope="col"></th>
                    </tr>
                </thead>
                <thead>
                    <tr>
                        <th scope="col" style="width: 1%;">Código</th>
                        <th scope="col">
                            <a href="{{ route('instrutor-index', [
                                'name' => $order_name,
                                'buscanome' => $busca_nome,
                                'buscadoc' => $busca_doc,
                                'data' => request('data')
                            ]) }}">
                                Nome 
                                {!! $order_name == 'asc' ? '<i class="ri-arrow-up-s-line"></i>' : '<i class="ri-arrow-down-s-line"></i>' !!}
                            </a>
                        </th>
                        <th scope="col">
                            <a href="{{ route('instrutor-index', [
                                'doc' => $order_doc,
                                'buscanome' => $busca_nome,
                                'buscadoc' => $busca_doc,
                                'data' => request('data')
                            ]) }}">
                                CPF/CNPJ
                                {!! $order_doc == 'asc' ? '<i class="ri-arrow-up-s-line"></i>' : '<i class="ri-arrow-down-s-line"></i>' !!}
                            </a>
                        </th>
                        <th scope="col">
                            <a href="{{ route('instrutor-index', [
                                'data' => $order_data,
                                'buscanome' => $busca_nome,
                                'buscadoc' => $busca_doc
                            ]) }}">
                                Data Cadastro
                                {!! $order_data == 'asc' ? '<i class="ri-arrow-up-s-line"></i>' : '<i class="ri-arrow-down-s-line"></i>' !!}
                            </a>
                        </th>
                        <th scope="col"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($instrutores->where('pessoa' , '!=', null) as $instrutor)
                        <tr>
                            <th>
                                <a href="{{ route('instrutor-insert', ['instrutor' => $instrutor->uid]) }}"
                                    class="fw-medium">
                                    #{{ substr($instrutor->uid, 7) }}
                                </a>
                            </th>
                            <td>{{ $instrutor->pessoa->nome_razao }}</td>
                            <td>{{ $instrutor->pessoa->cpf_cnpj }}</td>
                            <td>{{ $instrutor->created_at->format('d/m/Y') }}</td>
                            <td>
                                <div class="dropdown">
                                    <a href="#" role="button" id="dropdownMenuLink2" data-bs-toggle="dropdown"
                                        aria-expanded="false">
                                        <i class="ph-dots-three-outline-vertical" style="font-size: 1.5rem"
                                            data-bs-toggle="tooltip" data-bs-placement="top"
                                            title="Detalhes e edição"></i>
                                    </a>
                                    <ul class="dropdown-menu" aria-labelledby="dropdownMenuLink2">
                                        <li>
                                            <a class="dropdown-item"
                                                href="{{ route('instrutor-insert', ['instrutor' => $instrutor->uid]) }}">
                                                Editar
                                            </a>
                                        </li>
                                        <li></li>
                                        <x-painel.form-delete.delete route='instrutor-delete'
                                            id="{{ $instrutor->uid }}" />
                                        </li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center">Não há Instrutores cadastrados.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            <div class="row mt-3 w-100">
                {!! $instrutores->withQueryString()->links('pagination::bootstrap-5') !!}
            </div>

        </div>
    </div>
</div>
