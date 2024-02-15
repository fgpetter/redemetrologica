@if (session('funcionario-error'))
  <div class="alert alert-danger"> {{ session('error') }} </div>
@endif

<div class="card">
  <div class="card-body">

      <!-- Nav tabs -->
      <ul class="nav nav-tabs nav-justified mb-3" role="tablist">
        <li class="nav-item">
          <a class="nav-link active" data-bs-toggle="tab" href="#principal" role="tab"
            aria-selected="true">
            Dados Principais
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link" data-bs-toggle="tab" href="#documentos" role="tab" aria-selected="false">
            Cursos Habilitados
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link" data-bs-toggle="tab" href="#endereco" role="tab" aria-selected="false">
            Cursos Realizados
          </a>
        </li>

      </ul>

      <!-- Tab panes -->
      <div class="tab-content">
        <div class="tab-pane active" id="principal" role="tabpanel"> <!-- Dados principais -->
          <x-painel.instrutores.form-principal />
        </div>

        <div class="tab-pane" id="documentos" role="tabpanel"> <!-- Cursos habilitados -->

          <div class="row gy-3">

            <div class="col-sm-12">
              <x-forms.input-select name="curso" label="Curso">

                <option value="Curso 1">Curso 1</option>
                <option value="Curso 2">Curso 2</option>
                <option value="Curso 3">Curso 3</option>

              </x-forms.input-select>
              @error('curso')
                <div class="text-warning">{{ $message }}</div>
              @enderror
            </div>
            <div class="col-sm-6">
              <x-forms.input-select name="habilitado" label="Habilitado">

                <option value="SIM ">SIM</option>
                <option value="NÃO ">NÃO </option>


              </x-forms.input-select>
              @error('habilitado')
                <div class="text-warning">{{ $message }}</div>
              @enderror
            </div>
            <div class="col-sm-6"></div>
            <div class="col-sm-6">
              <x-forms.input-select name="conhecimento"
                label="Conhecimento (Graduação na área de atuação do curso)">

                <option value="SIM ">SIM</option>
                <option value="NÃO ">NÃO </option>


              </x-forms.input-select>
              @error('conhecimento')
                <div class="text-warning">{{ $message }}</div>
              @enderror
            </div>
            <div class="col-sm-6">
              <x-forms.input-select name="experiencia"
                label="Experiência (Mínimo de 02 anos na área de atuação do curso)">

                <option value="SIM ">SIM</option>
                <option value="NÃO ">NÃO </option>


              </x-forms.input-select>
              @error('experiencia')
                <div class="text-warning">{{ $message }}</div>
              @enderror
            </div>
            <div class="col-sm-12">
              <x-forms.input-textarea name="analise_observacoes" label="Análise/Observações">
                {{ old('analise_observacoes') ?? ($instrutor->analise_observacoes ?? null) }}
              </x-forms.input-textarea>
              @error('analise_observacoes')
                <div class="text-warning">{{ $message }}</div>
              @enderror
            </div>

            {{-- PLACEHOLDER --> trocar para lista de cursos --}}
            <div class="row">
              <div class="col">
                <x-painel.instrutores.list />
              </div>
            </div>
            {{-- PLACEHOLDER --> trocar para lista de cursos --}}

          </div>
        </div>

        <div class="tab-pane" id="endereco" role="tabpanel"> <!-- Cursos Realizado -->
          <div class="row gy-3">
            {{-- PLACEHOLDER --> trocar para lista de cursos --}}
            <div class="row">
              <div class="col">
                <x-painel.instrutores.list-cursos />
              </div>
            </div>
            {{-- PLACEHOLDER --> trocar para lista de cursos --}}

          </div>
        </div>

      </div>

  </div>

</div>
