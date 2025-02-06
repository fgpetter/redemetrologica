   <div class="card">
     <div class="card-body">

       {{-- Abas --}}
       @if (!$pessoa->id)
         <ul class="nav nav-pills nav-primary mb-3" role="tablist">
           <li class="nav-item">
             <a class="nav-link active" data-bs-toggle="tab" href="#pf" role="tab">Pessoa Física</a>
           </li>
           <li class="nav-item ">
             <a class="nav-link" data-bs-toggle="tab" href="#pj" role="tab">Pessoa Jurídica</a>
           </li>
         </ul>
       @endif

       {{-- Formularios --}}
       <div class="tab-content">

         {{-- PF --}}
         @if (!$pessoa->id || $pessoa->tipo_pessoa == 'PF')
           <div class="tab-pane active" id="pf" role="tabpanel">
             <form method="POST"
               action="{{ isset($pessoa->id) ? route('pessoa-update', $pessoa->uid) : route('pessoa-create') }}">
               @csrf
               <div class="row gy-3">
                 <input type="hidden" name="tipo_pessoa" value="PF">
                 <div class="col-12">
                   <x-forms.input-field name="nome_razao" required='required' :value="old('nome_razao') ?? ($pessoa->nome_razao ?? null)">
                     <x-slot:label>
                       Nome Completo
                     </x-slot:label>
                   </x-forms.input-field>
                   @error('nome_razao')
                     <div class="text-warning">{{ $message }}</div>
                   @enderror
                 </div>

                 <div class="col-6">
                   <x-forms.input-field :value="old('cpf_cnpj') ?? ($pessoa->cpf_cnpj ?? null)" name="cpf_cnpj" required='required'
                     id="input-cpf">
                     <x-slot:label>
                       CPF
                     </x-slot:label>
                   </x-forms.input-field>
                   @error('cpf_cnpj')
                     <div class="text-warning">{{ $message }}</div>
                   @enderror
                 </div>

                 <div class="col-6">
                   <x-forms.input-field :value="old('rg_ie') ?? ($pessoa->rg_ie ?? null)" type="number" name="rg_ie" label="RG" />
                   @error('rg_ie') <div class="text-warning">{{ $message }}</div> @enderror
                 </div>

                 <div class="col-6">
                   <x-forms.input-field :value="old('telefone') ?? ($pessoa->telefone ?? null)" name="telefone" class="telefone"
                     label="Telefone" /> 
                   @error('telefone') <div class="text-warning">{{ $message }}</div> @enderror
                 </div>

                 <div class="col-6">
                   <x-forms.input-field :value="old('telefone_alt') ?? ($pessoa->telefone_alt ?? null)" name="telefone_alt" class="telefone"
                     label="Telefone Alternativo" />
                   @error('telefone_alt') <div class="text-warning">{{ $message }}</div> @enderror
                 </div>

                 <div class="col-6">
                   <x-forms.input-field :value="old('celular') ?? ($pessoa->celular ?? null)" name="celular" class="telefone"
                     label="Celular" />
                   @error('celular') <div class="text-warning">{{ $message }}</div> @enderror
                 </div>
                 <div class="col-6">
                  @if($pessoa?->user)
                    <x-forms.input-field :value="$pessoa->user->email ?? null" label="Email" 
                      tooltip="Este e-mail é o login do cliente, para alterar, edite o usuário." readonly="readonly"/>
                  @else
                   <x-forms.input-field :value="old('email') ?? ($pessoa->email ?? null)" type="email" name="email" id="email"
                    label="Email" />
                  @endif
                  @error('email') <div class="text-warning">{{ $message }}</div> @enderror
                 </div>

                 <div class="col-12">
                  <x-forms.input-textarea name="observacoes" label="Observações">
                    {{ old('observacoes') ?? ($pessoa->observacoes ?? null) }}
                  </x-forms.input-textarea>
                 </div>

                 <div class="col-12">
                   <button type="submit"
                     class="btn btn-primary px-4">{{ $pessoa->id ? 'Atualizar' : 'Salvar' }}</button>
                 </div>

               </div>
             </form>
           </div>
         @endif
         {{-- PJ --}}
         @if (!$pessoa->id || $pessoa->tipo_pessoa == 'PJ')
           <div class="tab-pane {{ $pessoa->tipo_pessoa == 'PJ' ? 'active' : '' }}" id="pj"
             role="tabpanel">
             <form method="POST"
               action="{{ isset($pessoa->id) ? route('pessoa-update', $pessoa->uid) : route('pessoa-create') }}">
               @csrf
               <div class="row gy-3">

                 <input type="hidden" name="tipo_pessoa" value="PJ">
                 <div class="col-12">
                   <x-forms.input-field :value="old('nome_razao') ?? ($pessoa->nome_razao ?? null)" name="nome_razao" required='required'
                     :uppercase="true">
                     <x-slot:label>
                       Razão Social
                     </x-slot:label>
                   </x-forms.input-field>
                   @error('nome_razao')
                     <div class="text-warning">{{ $message }}</div>
                   @enderror
                 </div>

                 <div class="col-12">
                   <x-forms.input-field :value="old('nome_fantasia') ?? ($pessoa->nome_fantasia ?? null)" name="nome_fantasia" label="Nome Fantasia"
                     :uppercase="true" />
                   @error('nome_fantasia')
                     <div class="text-warning">{{ $message }}</div>
                   @enderror
                 </div>

                 <div class="col-6">
                   <x-forms.input-field :value="old('cpf_cnpj') ?? ($pessoa->cpf_cnpj ?? null)" name="cpf_cnpj" required='required'
                     id="input-cnpj">
                     <x-slot:label> CNPJ </x-slot:label>
                   </x-forms.input-field>
                   @error('cpf_cnpj')
                     <div class="text-warning">{{ $message }}</div>
                   @enderror
                 </div>

                 <div class="col-6">
                   <x-forms.input-field :value=" old('rg_ie') ?? $pessoa->rg_ie ?? ($pessoa->tipo_pessoa == 'PJ' ? 'ISENTO' : '') " type="text" name="rg_ie"
                     label="RG ou Inscrição Estadual" />
                   @error('rg_ie')
                     <div class="text-warning">{{ $message }}</div>
                   @enderror
                 </div>

                 <div class="col-6">
                   <x-forms.input-field :value="old('insc_municipal') ?? ($pessoa->insc_municipal ?? null)" type="number" name="insc_municipal"
                     label="Inscrição Municipal" />
                   @error('insc_municipal')
                     <div class="text-warning">{{ $message }}</div>
                   @enderror
                 </div>

                 <div class="col-6">
                   <x-forms.input-field :value="old('telefone') ?? ($pessoa->telefone ?? null)" name="telefone" label="Telefone"
                     class="telefone" />
                   @error('telefone') <div class="text-warning">{{ $message }}</div> @enderror
                 </div>

                 <div class="col-6">
                  <x-forms.input-field :value="old('telefone_alt') ?? ($pessoa->telefone_alt ?? null)" name="telefone_alt" class="telefone"
                    label="Telefone Alternativo" />
                  @error('telefone_alt') <div class="text-warning">{{ $message }}</div> @enderror
                </div>

                <div class="col-6">
                  <x-forms.input-field :value="old('celular') ?? ($pessoa->celular ?? null)" name="celular" class="telefone"
                    label="Celular" />
                  @error('celular') <div class="text-warning">{{ $message }}</div> @enderror
                </div>


                 <div class="col-6">
                   <x-forms.input-field :value="old('email') ?? ($pessoa->email ?? null)" type="email" name="email"
                     label="Email" />
                   @error('email') <div class="text-warning">{{ $message }}</div> @enderror
                 </div>

                 <div class="col-6">
                   <x-forms.input-field :value="old('site') ?? ($pessoa->site ?? null)" type="site" name="site"
                     label="Site" />
                   @error('site') <div class="text-warning">{{ $message }}</div> @enderror
                 </div>

                 <div class="col-12">
                   <button type="submit"
                     class="btn btn-primary px-4">{{ $pessoa->id ? 'Atualizar' : 'Salvar' }}</button>
                 </div>
               </div>
             </form>
           </div>
         @endif

       </div>

       @if ($pessoa->id)
         <x-painel.form-delete.delete route="pessoa-delete" id="{{ $pessoa->uid }}" label="Pessoa" />
       @endif

     </div>

   </div>
