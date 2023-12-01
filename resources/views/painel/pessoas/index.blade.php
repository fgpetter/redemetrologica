@extends('layouts.master')
@section('title')
    Listagem de pessoas
@endsection
@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            Pessoas
        @endslot
        @slot('title')
            Listagem de pessoas
        @endslot
    @endcomponent

    <div class="row">
        <div class="col">
            <x-painel.pessoas.list />
            {{-- <x-painel.pessoas.list :pessoas="$pessoas"/> --}}
        </div>
    </div>
@endsection

@section('script')
<<<<<<< HEAD:resources/views/painel/pessoas/index.blade.php
<script src="{{ URL::asset('build/js/pages/imask.js') }}"></script>
<script src="{{ URL::asset('build/js/custom.js') }}"></script>
@endsection
=======
    <script src="{{ URL::asset('build/js/app.js') }}"></script>
    <script src="{{ URL::asset('build/js/pages/imask.js') }}"></script>
    <script src="{{ URL::asset('build/js/custom.js') }}"></script>
    <script type="text/javascript">
        $(function() {
            // coloca search personalizado por coluna no rodapé
            $('.data-table tfoot th').each(function() {
                var title = $(this).text();
                $(this).html('<input type="text" placeholder="Pesquisar ' + title + '" />');
            });
            // monta a datatable
            var table = $('.data-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('pessoa-index') }}",
                columns: [{
                        data: 'uid',
                        name: 'uid'
                    },
                    {
                        data: 'nome_razao',
                        name: 'nome_razao'
                    },
                    {
                        data: 'cpf_cnpj',
                        name: 'cpf_cnpj',
                        render: function(data, type,
                            row
                            ) { //mascara para cpf/cnpj (remover se mudar formato do campo no banco)
                            if (type === 'display' || type === 'filter') {
                                if (data.length === 11) {
                                    return data.replace(/(\d{3})(\d{3})(\d{3})(\d{2})/,
                                        "$1.$2.$3-$4");
                                } else if (data.length > 11) {
                                    return data.replace(/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/,
                                        "$1.$2.$3/$4-$5");
                                }
                            }
                            return data;
                        }
                    },
                    {
                        data: 'created_at',
                        name: 'created_at',
                        render: function(data, type, row) { //mascara para data de cadastro
                            if (type === 'display' || type === 'filter') {
                                var date = new Date(data);
                                var dia = date.getDate().toString().padStart(2, '0');
                                var mes = (date.getMonth() + 1).toString().padStart(2, '0');
                                var ano = date.getFullYear();
                                return dia + '/' + mes + '/' + ano;
                            }
                            return data;
                        }
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false
                    },
                ],
            });


            table.columns().every(function() {
                var that = this;

                $('input', this.footer()).on('keyup change clear', function() { //faz a busca pelas colunas
                    if (that.search() !== this.value) {
                        //trata o valor que será pesquisado para permitir buscas por data.
                        if (that.index() === 3) {

                            if (this.value.length < 5) {
                                that
                                    .search(this.value)
                                    .draw();
                            } else if (this.value.length >= 5 && this.value.length < 8) {
                                var value = this.value.replace(/\//g, '-');
                                var parts = value.split('-');
                                value = parts[1] + '-' + parts[0];
                                that
                                    .search(value)
                                    .draw();
                            } else if (this.value.length === 10) {
                                var value = this.value.replace(/\//g, '-');
                                var parts = value.split('-');
                                value = parts[2] + '-' + parts[1] + '-' + parts[0];
                                that
                                    .search(value)
                                    .draw();
                            }
                        } else {
                            that
                                .search(this.value)
                                .draw();
                        }
                    }
                });
            });

        });
    </script>
@endsection
>>>>>>> BranchDoMatheus:resources/views/pessoas/index.blade.php
