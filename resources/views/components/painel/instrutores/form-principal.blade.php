     <form method="POST"
         action="{{ isset($instrutor->uid) ? route('instrutor-update', $instrutor->uid) : route('instrutor-create') }}"
         enctype="multipart/form-data">
         @csrf
         <div class="row gy-3 mt-3">

             <div class="col-sm-8">
                 <x-forms.input-field :value="old('nome') ?? ($instrutor->pessoa->nome_razao ?? null)" name="nome" label="Nome" />
                 @error('nome')
                     <div class="text-warning">{{ $message }}</div>
                 @enderror
             </div>

             <div class="col-sm-2">
                 <x-forms.input-select name="situacao" label="Situação">
                     <option @selected($instrutor->situacao == 1) value="1">ATIVO</option>
                     <option @selected($instrutor->situacao == 0) value="0">INATIVO</option>
                 </x-forms.input-select>
             </div>

             <div class="col-sm-2">
                 <x-forms.input-select name="tipo_pessoa" label="Tipo Pessoa">
                     <option @selected($instrutor->pessoa->tipo_pessoa == 'PF') value="PF">FÍSICA</option>
                     <option @selected($instrutor->pessoa->tipo_pessoa == 'PJ') value="PJ">JURÍDICA</option>
                 </x-forms.input-select>
             </div>

             <div class="col-sm-3">
                 <x-forms.input-field :value="old('cnpj_cpf') ?? ($instrutor->pessoa->cpf_cnpj ?? null)" name="cpf_cnpj" label="CNPJ/CPF" />
                 @error('cnpj_cpf')
                     <div class="text-warning">{{ $message }}</div>
                 @enderror
             </div>
             <div class="col-sm-3">
                 <x-forms.input-field :value="old('rg_ie') ?? ($instrutor->pessoa->rg_ie ?? null)" name="rg_ie" label="RG/IE" />
                 @error('rg_ie')
                     <div class="text-warning">{{ $message }}</div>
                 @enderror
             </div>




             <div class="col-sm-6">
                 <label for="curriculo" class="form-label">Currículo</label>
                 @if ($instrutor->curriculo)
                     <div class="input-group mt-0">
                         <input type="text" class="form-control" readonly
                             value="{{ explode('curriculos/', $instrutor->curriculo)[0] }}">
                         <button class="btn btn-success dropdown-toggle" type="button" data-bs-toggle="dropdown"
                             aria-expanded="false"></button>
                         <ul class="dropdown-menu dropdown-menu-end">
                             <li><a class="dropdown-item" href="{{ asset($instrutor->curriculo) }}"
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
                     <input class="form-control" name="curriculo" type="file" id="curriculo"
                         accept=".doc, .pdf, .docx">
                     @error('curriculo')
                         <div class="text-warning">{{ $message }}</div>
                     @enderror
                 @endif
             </div>



             <div class="col-sm-12">
                 <x-forms.input-textarea name="observacoes" label="Observações"
                    >{{ old('observacoes') ?? ($instrutor->observacoes ?? null) }}
                 </x-forms.input-textarea>
                 @error('observacoes')
                     <div class="text-warning">{{ $message }}</div>
                 @enderror
             </div>
         </div>
         <!-- Btn -->
         <div class="row mt-3">
             <div class="col-sm-12">
                 <button type="submit" class="btn btn-primary px-4">
                     {{ isset($instrutor->uid) ? 'ATUALIZAR' : 'CADASTRAR' }} </button>
             </div>
         </div>
     </form>
     <form method="POST" id="curriculo-delete" action="{{ route('instrutor-curriculo-delete', $instrutor->uid) }}">
         @csrf
     </form>
