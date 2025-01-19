@if ($errors->any())
  <div class="alert alert-warning">
    <strong>Erro ao salvar os dados!</strong> <br><br>
    <ul>
      @foreach ($errors->all() as $error)
        <li>{{ $error }}</li>
      @endforeach
    </ul>
  </div>
@endif

<div class="table-responsive" style="min-height: 180px">
  <table class="table table-responsive table-striped align-middle table-nowrap mb-0">
    @forelse ($empresasParticipantes as $empresaParticipante)
      <thead class="bg-light">
        <tr>
          <th scope="col" colspan="3"><strong>Empresa: </strong> &nbsp; {{ $empresaParticipante->pessoa->nome_razao }}</th>
        </tr>
      </thead>
        @foreach($participantes->where('empresa_id', $empresaParticipante->pessoa_id) as $participante)
          <tr>
            <td style="width: 1%;">{{ Carbon\Carbon::parse($participante->data_inscricao)->format('d/m/Y') }}</td>
            <td class="px-3">{{ $participante->pessoa->nome_razao }}</td>
          </tr>
        @endforeach
      <tbody>

      </tbody>
    @empty
      <tr>
        <td colspan="6" class="text-center">Este agendamento não possui inscritos.</td>
      </tr>
    @endforelse

      @if($participantes->sum('valor') > 0)
        <tfoot>
          <tr>
            <td colspan="3"></td>
            <td><strong>Total:</strong> {{ $participantes->sum('valor') }} </td>
            <td></td>
          </tr>
        </tfoot>
      @endif
  </table>

</div>
