      <div class="row gy-3">

        <div class="col-8">
          <label class="form-label">Nome Completo<small class="text-danger-emphasis opacity-75"> (Obrigatório)
            </small></label>
          <input type="text" class="form-control" name="nome_razao"
            value="{{ old('nome_razao') ?? ($avaliador->pessoa->nome_razao ?? null) }}">
          @error('nome_razao')
            <div class="text-warning">{{ $message }}</div>
          @enderror
        </div>

        <div class="col-4">
          <label class="form-label">CPF<small class="text-danger-emphasis opacity-75"> (Obrigatório)
            </small></label>
          <input type="text" class="form-control" name="cpf_cnpj" id="input-cpf"
            value="{{ old('cpf_cnpj') ?? ($avaliador->pessoa->cpf_cnpj ?? null) }}">
          @error('cpf_cnpj')
            <div class="text-warning">{{ $message }}</div>
          @enderror
        </div>

        <div class="col-4">
          <label class="form-label">RG</label>
          <input type="number" class="form-control" name="rg_ie"
            value="{{ old('rg_ie') ?? ($avaliador->pessoa->rg_ie ?? null) }}">
          @error('rg_ie')
            <div class="text-warning">{{ $message }}</div>
          @enderror
        </div>

        <div class="col-4">
          <label class="form-label">Telefone</label>
          <input type="text" class="form-control" name="telefone" id="telefone"
            value="{{ old('telefone') ?? ($avaliador->pessoa->telefone ?? null) }}">
          @error('telefone')
            <div class="text-warning">{{ $message }}</div>
          @enderror
        </div>

        <div class="col-4">
          <label class="form-label">Email</label>
          <input type="text" class="form-control" name="email" id="email"
            value="{{ old('email') ?? ($avaliador->pessoa->email ?? null) }}">
          @error('email')
            <div class="text-warning">{{ $message }}</div>
          @enderror
        </div>

        <div class="row mt-4">

          <div class="col-6">
            <div class="form-check mb-3 bg-light rounded" style="padding: 0.8rem 1.8rem 0.8rem;">
              <input class="form-check-input" name="exp_min_comprovada" value="1" value="1" id="exp_min_comprovada" type="checkbox"
              @checked(old('exp_min_comprovada') ?? ($avaliador->exp_min_comprovada)) >
              <label class="form-check-label" for="exp_min_comprovada">
                Experiência Comprovada
              </label>
            </div>

            <div class="form-check mb-3 bg-light rounded" style="padding: 0.8rem 1.8rem 0.8rem;">
              <input class="form-check-input" name="curso_incerteza" value="1" id="curso_incerteza" type="checkbox"
              @checked(old('curso_incerteza') ?? ($avaliador->curso_incerteza)) >
              <label class="form-check-label" for="curso_incerteza">
                Curso Incerteza
              </label>
            </div>
            
            <div class="form-check mb-3 bg-light rounded" style="padding: 0.8rem 1.8rem 0.8rem;">
              <input class="form-check-input" name="curso_iso" value="1" id="curso_iso" type="checkbox"
              @checked(old('curso_iso') ?? ($avaliador->curso_iso)) >
              <label class="form-check-label" for="curso_iso">
                Curso de ISO
              </label>
            </div>
          </div>

          <div class="col-6">

            <div class="form-check mb-3 bg-light rounded" style="padding: 0.8rem 1.8rem 0.8rem;">
              <input class="form-check-input" name="curso_aud_interna" value="1" id="curso_aud_interna" type="checkbox"
              @checked(old('curso_aud_interna') ?? ($avaliador->curso_aud_interna)) >
              <label class="form-check-label" for="curso_aud_interna">
                Curso de Auditoria Interna
              </label>
            </div>

            <div class="form-check mb-3 bg-light rounded" style="padding: 0.8rem 1.8rem 0.8rem;">
              <input class="form-check-input" name="parecer_psicologico" value="1" id="parecer_psicologico" type="checkbox"
              @checked(old('parecer_psicologico') ?? ($avaliador->parecer_psicologico)) >
              <label class="form-check-label" for="parecer_psicologico">
                Parecer Psicológico
              </label>
            </div>
          </div>

        </div>
        <div class="row">
          <div class="col-8">
            @if ($avaliador->curriculo)
              <div class="input-group mt-4">
                <input type="text" class="form-control" readonly
                  value="{{ explode('curriculos/', $avaliador->curriculo)[1] }}">
                <button class="btn btn-success dropdown-toggle" type="button" data-bs-toggle="dropdown"
                  aria-expanded="false"></button>
                <ul class="dropdown-menu dropdown-menu-end">
                  <li><a class="dropdown-item" href="{{ asset($avaliador->curriculo) }}"
                      target="_blank">Baixar</a></li>
                  <li>
                    <hr class="dropdown-divider">
                  </li>
                  <li>
                    <a class="dropdown-item" href="javascript:void(0)"
                      onclick="document.getElementById('curriculo-delete').submit();">Remover
                    </a>
                  </li>
                </ul>
              </div>
            @else
              <label for="curriculo" class="form-label">Currículo</label>
              <input class="form-control" name="curriculo" type="file" id="curriculo"
                accept=".doc, .pdf, .docx">
              @error('curriculo')
                <div class="text-warning">{{ $message }}</div>
              @enderror
            @endif
          </div>

          <div class="col-4">
            <label class="form-label">Data ingresso</label>
            <input type="date" class="form-control" name="data_ingresso" id="data_ingresso" 
              value="{{ old('data_ingresso') ?? $avaliador->data_ingresso ?? null }}" required>
            @error('data_ingresso') <div class="text-warning">{{ $message }}</div> @enderror 
          </div>

        </div>
        <h6 class="mb-0">Dados de endereço</h6>
        <x-painel.enderecos.form-endereco :endereco="$avaliador->pessoa->enderecos[0] ?? null" />

        <div class="col-12">
          <button type="submit"
            class="btn btn-primary px-4">{{ $avaliador->id ? 'Atualizar' : 'Salvar' }}</button>
        </div>

      </div>
