    <!-- {{-- banner inicial --}} -->
    @foreach ($agendacursos as $agendacurso)
        <div class=" p-5 text-center mb-5  "
            style=" background-repeat: no-repeat; background-size: contain; background-image: url('{{ asset('build/images/site/LAB-SOLICITAR-RECONHECIMENTO-1349-x-443.png') }}'); width: 100%; ">

            <div class=" container align-self-center text-start text-white ">
                <p class="SiteBanner--titulo my-5"><strong>
                        {{ $agendacurso->curso->descricao }}</strong></p>
                <p><i class="bi bi-clock text-warning mx-1"></i> Data:
                    {{ Carbon\Carbon::parse($agendacurso->data_inicio)->format('d/m/Y') }} a
                    {{ Carbon\Carbon::parse($agendacurso->data_fim)->format('d/m/Y') }} </p>
                <p><i class="bi bi-stopwatch text-warning mx-1"></i>Horário: {{ $agendacurso->horario }}</p>
                <p><i class="bi bi-question-circle text-warning mx-1"></i>Carga Horária:
                    {{ $agendacurso->carga_horaria }}</p>
                <p><i class="bi bi-star text-warning mx-1"></i>Local: {{ $agendacurso->endereco_local }}</p>
            </div>
            <!-- {{-- banner inicial --}} -->
            <div class="container bg-white shadow-lg rounded text-start pb-2">
                <div class="mx-5 my-5 ">
                    <h3 class=" pt-5">Objetivo:</h3>
                    <hr>
                    <p>{{ $agendacurso->curso->objetivo }}</p>

                    <h3 class=" pt-3">Público-Alvo:</h3>
                    <hr>
                    <p>{{ $agendacurso->curso->publico_alvo }}
                    </p>

                    <h3 class=" pt-3">Pré-Requisitos:</h3>
                    <hr>
                    <p>{{ $agendacurso->curso->pre_requisitos }}</p>

                    <h3 class=" pt-3">Referências:</h3>
                    <hr>
                    <p>{{ $agendacurso->curso->referencias_utilizadas }}</p>

                    <h3 class=" pt-3">Conteúdo Programático:</h3>
                    <hr>
                    <p>{{ $agendacurso->curso->conteudo_programatico }}</p>


                </div>
            </div>

        </div>
    @endforeach
