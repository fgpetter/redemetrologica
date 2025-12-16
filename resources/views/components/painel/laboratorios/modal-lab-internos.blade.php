{{-- modal --}}
<div class="modal fade" id="{{ isset($labinterno) ? 'labinternoModal'.$labinterno->uid : 'labinternoModal' }}" tabindex="-1" aria-labelledby="labinternoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="labinternoModalLabel">Adicionar Laboratório</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <form method="POST" action="{{ route('laboratorio-save-interno', $labinterno->uid ?? null) }}" enctype="multipart/form-data">
            @csrf
            <div class="row gy-3 mb-3">

              <input type="hidden" name="laboratorio_id" value="{{ $laboratorio->id }}">

              <div class="col-12">
                <x-forms.input-select name="area_atuacao_id" label="Area de Atuação" required>
                  <option value="">Selecione</option>
                  @foreach ($areasatuacao as $areaatuacao)
                    <option value="{{ $areaatuacao->id }}" @selected( isset($labinterno) && $labinterno->area_atuacao_id == $areaatuacao->id )>{{ $areaatuacao->descricao }}</option>
                  @endforeach
                </x-forms.input-select>
              </div>

              <div class="col-8">
                <x-forms.input-field name="nome" :value="old('nome') ?? ($labinterno->nome ?? null)" label="Nome do laboratório" required/>
                  @error('nome') <div class="text-warning">{{ $message }}</div> @enderror
              </div>

              <div class="col-4">
                <x-forms.input-field name="cod_labinterno" :value="old('cod_labinterno') ?? ($labinterno->cod_labinterno ?? null)" label="Código do Laboratório" />
                  @error('cod_labinterno') <div class="text-warning">{{ $message }}</div> @enderror
              </div>
        
              <div class="col-4">
                <x-forms.input-field name="telefone" :value="old('telefone') ?? ($labinterno->telefone ?? null)" label="Telefone" mask="telefone" />
                  @error('telefone') <div class="text-warning">{{ $message }}</div> @enderror
              </div>
        
              <div class="col-4">
                <x-forms.input-field name="email" :value="old('email') ?? ($labinterno->email ?? null)" label="E-mail" />
                  @error('email') <div class="text-warning">{{ $message }}</div> @enderror
              </div>
        
              <div class="col-4">
                <x-forms.input-field name="responsavel_tecnico" :value="old('responsavel_tecnico') ?? ($labinterno->responsavel_tecnico ?? null)" label="Responsável Técnico" />
                  @error('responsavel_tecnico') <div class="text-warning">{{ $message }}</div> @enderror
              </div>

              <div class="col-4">
                <x-forms.input-select name="reconhecido" label="Reconhecido">
                  <option value="0"> NÃO </option>
                  <option @selected($labinterno->reconhecido ?? null) value="1"> SIM </option>
                </x-forms.input-select>

              </div>
              <div class="col-4">
                <x-forms.input-select name="sebrae" label="SEBRAE">
                  <option value="0"> NÃO </option>
                  <option @selected($labinterno->sebrae ?? null) value="1"> SIM </option>
                </x-forms.input-select>

              </div>
              <div class="col-4">
                <x-forms.input-select name="site" label="Site">
                  <option value="0"> NÃO </option>
                  <option @selected($labinterno->site ?? null) value="1"> SIM </option>
                </x-forms.input-select>
              </div>

              <div class="col-12">
                @if ($labinterno->certificado ?? null)
                  <label class="form-label">Arquivo atual</label><br>
                  <a href="{{ asset('laboratorios-certificados/' . $labinterno->certificado) }}">
                    <span>
                      <i class="ph-file-arrow-down align-middle" style="font-size: 1rem"></i>
                    </span>
                    {{ $labinterno->certificado }}
                  </a>
                  <br>
                  <br>
                @endif
                <label for="certificado">@if ($labinterno->certificado ?? null) Alterar @else Inserir @endif Certificado
                  <span data-bs-toggle="tooltip" data-bs-html="true" title="Ao adicionar um arquivo o anterior será removido">
                    <i class="ri-information-line align-middle text-warning-emphasis" style="font-size: 1rem"></i>
                  </span>
                </label>
                <input class="form-control" type="file" name="certificado" id="certificado">
                @error('certificado') <div class="text-warning">{{ $message }}</div> @enderror
              </div>
              
  
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
              <button type="submit" class="btn btn-primary">Salvar</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
  {{-- endmodal --}}
    