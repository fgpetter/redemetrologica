{{-- modal --}}
<div class="modal fade" id="cursoshabilitadosModal" tabindex="-1" aria-labelledby="cursoshabilitadosModalLabel"
    aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="cursoshabilitadosModalLabel">Adicionar Curso</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form method="POST"
                    action="{{ isset($material) ? route('materiais-padroes-update', $material->uid) : route('materiais-padroes-store') }}">
                    @csrf
                    <div class="row gy-3">

                        <div class="col-sm-12">
                            <x-forms.input-select name="curso" label="Curso">
                                @foreach ($cursos as $curso)
                                    <option value="{{ $curso }}">{{ $curso->descricao }}</option>
                                @endforeach
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
