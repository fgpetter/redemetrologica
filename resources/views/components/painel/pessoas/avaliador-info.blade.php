<div class="card">
    <div class="card-header d-flex align-items-center gap-3">
        <div class="avatar-sm">
            <span class="avatar-title bg-primary-subtle text-primary rounded-circle fs-3">
                <i class="ph-chart-line-up-light"></i>
            </span>
        </div>
        <h4 class="card-title mb-0">Dados de Avaliador</h4>
    </div><!-- end card header -->
    <div class="card-body">
        <table class="table table-sm" style="table-layout: auto; width: 150px;">
        <tr>
            <td style="width: 1%; white-space: nowrap;" class="ps-4 pe-2">Data de Cadastro</td>
            <td class="px-4">{{ $pessoa->avaliador->data_ingresso }}</td>
        </tr>
        <tr>
            <td style="width: 1%; white-space: nowrap;" class="ps-4 pe-2">Exp. Comprovada</td>
            <td class="px-4">{{ $pessoa->avaliador->exp_min_comprovada ? 'Sim' : 'Não' }}</td>
        </tr>
        <tr>
            <td style="width: 1%; white-space: nowrap;" class="ps-4 pe-2">Curso Incerteza</td>
            <td class="px-4">{{ $pessoa->avaliador->curso_incerteza ? 'Sim' : 'Não' }}</td>
        </tr>
        <tr>
            <td style="width: 1%; white-space: nowrap;" class="ps-4 pe-2">Curso ISO</td>
            <td class="px-4">{{ $pessoa->avaliador->curso_iso ? 'Sim' : 'Não' }}</td>
        </tr>
        <tr>
            <td style="width: 1%; white-space: nowrap;" class="ps-4 pe-2">Curso Aud. Interna</td>
            <td class="px-4">{{ $pessoa->avaliador->curso_aud_interna ? 'Sim' : 'Não' }}</td>
        </tr>
        <tr>
            <td style="width: 1%; white-space: nowrap;" class="ps-4 pe-2">Parecer Psic.</td>
            <td class="px-4">{{ $pessoa->avaliador->parecer_psicologico ? 'Sim' : 'Não' }}</td>
        </tr>
        </table>
    </div>
</div>