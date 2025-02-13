    <!-- {{-- banner inicial --}} -->

    <div class=" p-5 text-center mb-3  "
        style=" background-repeat: no-repeat; background-size: contain; background-image: url('{{ asset('build/images/site/LAB-SOLICITAR-RECONHECIMENTO-1349-x-443.png') }}'); width: 100%; ">

        <div class=" container align-self-center text-start text-white ">
            <p class="SiteBanner--titulocursos  my-5"><strong>
                    {{ $agendacursos->curso->descricao ?? '' }}</strong></p>
            <p><i class="bi bi-clock text-warning mx-1"></i> Data:
                @if($agendacursos->data_inicio) {{ \Carbon\Carbon::parse($agendacursos->data_inicio)->format('d/m/Y') }} @endif
                a
                @if($agendacursos->data_fim) {{ \Carbon\Carbon::parse($agendacursos->data_fim)->format('d/m/Y') }} @endif</p>

            <p><i class="bi bi-stopwatch text-warning mx-1"></i>Horário: {{ $agendacursos->horario }}</p>
            <p><i class="bi bi-question-circle text-warning mx-1"></i>Carga Horária:
                {{ $agendacursos->carga_horaria }}</p>
            <p><i class="bi bi-star text-warning mx-1"></i>Local: {{ $agendacursos->endereco_local }}</p>
        </div>
        <!-- {{-- banner inicial --}} -->
        <div class="container bg-white shadow-lg rounded text-start pb-2">
            <div class="mx-5 mt-4 ">
                <h3 class=" pt-5">Objetivo:</h3>
                <hr>
                <p>{{ $agendacursos->curso->objetivo ?? '' }}</p>

                <h3 class=" pt-3">Público-Alvo:</h3>
                <hr>
                <p>{{ $agendacursos->curso->publico_alvo ?? '' }}
                </p>

                <h3 class=" pt-3">Pré-Requisitos:</h3>
                <hr>
                <p>{{ $agendacursos->curso->pre_requisitos ?? '' }}</p>

                <h3 class=" pt-3">Referências:</h3>
                <hr>
                <p>{{ $agendacursos->curso->referencias_utilizadas ?? '' }}</p>

                <h3 class=" pt-3">Conteúdo Programático:</h3>
                <hr>
                <p>{{ $agendacursos->curso->conteudo_programatico ?? '' }}</p>


            </div>
        </div>



    </div>
    @if ($agendacursos->inscricoes == 1)
        <div class="SiteCards__bgimage p-5 text-center mb-5 text-white container-fluid "
            style="background-size: cover; background-image: url('{{ asset('build/images/site/lab-banner-iso.png') }}');height:100%; width:100%;">
            <div class="container">
                <a href="{{ route('curso-inscricao', ['target' => $agendacursos->uid]) }}" class="btn btn-warning btn-lg mb-4 botao-inscrevase">INSCREVER-SE</a>


                <div class="row justify-content-center">
                    <div class="col-md-3 bg-white p-3 m-2 border rounded">
                        <span class="fs-1 font-weight-bold text-primary" style="font-size: 24px;">R$
                            {{ number_format($agendacursos->investimento_associado, 2, ',', '.') }}</span><br>
                        <span class="text-dark opacity-50" style="font-size: 18px;">Associados</span>
                    </div>
                    <div class="col-md-3 bg-white p-3 m-2 border rounded">
                        <span class="fs-1 font-weight-bold text-primary" style="font-size: 24px;">R$
                            {{ number_format($agendacursos->investimento, 2, ',', '.') }}</span><br>
                        <span class="text-dark opacity-50" style="font-size: 18px;">Não associados</span>
                    </div>
                </div>
            </div>
        </div>
    @endif
