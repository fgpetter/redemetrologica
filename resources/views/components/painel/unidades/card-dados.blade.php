<div class="card shadow-none mb-1">
    <div class="card-header py-1"> <span class="fs-5" >Unidade: {{ $unidade->nome }}</span> </div>
    <div class="card-body d-flex justify-content-between align-items-start py-2">
        <div>
            <p>
                {!! '<strong> Responsável: </strong>' . $unidade->nome_responsavel !!} <br>
                <strong> Email: </strong> {{ $unidade->email }} <br>
                <strong> Código do laboratório: </strong> {{ $unidade->cod_laboratorio }} <br>
                <strong> Responsável técnico: </strong> {{ $unidade->responsavel_tecnico }} <br>
                <strong> Fone: </strong> <input type="text" value="{{ $unidade->telefone }}"
                    class="form-control-plaintext pt-0 d-inline w-75 telefone"><br>
                <strong> Endereço: </strong> {{ $unidade->endereco->endereco }}, {{ $unidade->endereco->complemento }}
                <br>
                {{ $unidade->endereco->bairro }}, {{ $unidade->endereco->cidade }} <br>
                {{ $unidade->endereco->uf }} - CEP: {{ $unidade->endereco->cep }}
            </p>
        </div>
        <div>
            <a href="#" role="button" id="dropdownMenuLink1" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="ph-dots-three-outline-vertical" style="font-size: 1.5rem" data-bs-toggle="tooltip"
                    title="Detalhes e edição"></i>
            </a>
            <ul class="dropdown-menu" aria-labelledby="dropdownMenuLink1">
                <li><a class="dropdown-item" data-bs-toggle="modal"
                        data-bs-target="{{ "#modal_unidade_$unidade->uid" }}">Editar</a></li>
                <li>

                    <x-painel.form-delete.delete route='unidade-delete' id="{{ $unidade->uid }}" />
                </li>
            </ul>
        </div>
    </div>
</div>
