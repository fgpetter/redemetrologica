@extends('site.layouts.layout-site')
@section('title')
    Fale Conosco
@endsection
@section('content')
    <div class="container my-5">
        <div class="row">
            <div class="col-md-6 col-lg-4">
                <i class="bi bi-buildings fs-1"></i>
                <h2>ENDEREÇO</h2>
                <p>Rua Santa Catarina, 40 – Salas 801/802</p>
                <p>Bairro Santa Maria Goretti</p>
                <p>Porto Alegre/RS – Cep 91030-330</p>
            </div>
            <div class="col-md-6 col-lg-4">
                <i class="bi bi-phone fs-1"></i>
                <h2>TELEFONE</h2>
                <p>(51) 2200 3988</p>
                <h2>WHATSAPP</h2>
                <i class="bi bi-whatsapp fs-1"></i>
                <p>(51) 99179-5131</p>
            </div>
            <div class="col-md-6 col-lg-4">
                <i class="bi bi-share-fill fs-1"></i>
                <h2>REDE SOCIAL</h2>
                <div class="">
                    <a href="https://www.facebook.com/Rede-Metrol%C3%B3gica-RS-788822964529119/"><i
                            class="bi bi-facebook fs-2  px-3 "></i></a>
                    <a href="https://www.instagram.com/redemetrologicars01/"><i class="bi bi-instagram fs-2 px-3 "></i></a>
                    <a href="https://br.linkedin.com/company/redemetrologicars"><i
                            class="bi bi-linkedin fs-2 px-3 "></i></a>
                </div>
            </div>
        </div>
    </div>

    <div class="container my-5">
        <div class="row">
            <div class="col offset-xxl-1 col-xxl-10">
                @if (session('success'))
                <div class="alert alert-success alert-dismissible bg-body-secondary fade show" role="alert">
                    <strong>Mensagem enviada com sucesso! </strong> <br>
                    <span class="text-dark">Nossa equipe entrará em contato em breve.</span>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                @endif
        
                <form id="faleConoscoForm" method="POST" action="{{ route('faleconosco.submit') }}">
                    @csrf
                    <div class="row">
                        <div class="col-lg-6">
                            <input class="form-control mb-3" name="name" type="text" placeholder="Seu nome"
                                value="{{ old('name') }}" required>
        
                            <input class="form-control my-3" name="email" type="email" placeholder="E-mail*"
                                value="{{ old('email') }}" required>
        
                            <input class="form-control my-3" name="phone" type="text" placeholder="Telefone para Contato"
                                value="{{ old('phone') }}" required>
                            <p><strong>A Rede Metrológica conta com Data Protection Officer (DPO). O Usuário pode entrar em
                                    contato com o DPO no endereço físico ou através do e-mail
                                    lgpd@redemetrologica.com.br</strong></p>
                        </div>
                        <div class="col-lg-6">
                            {{-- <label for="exampleFormControlTextarea1" class="form-label">Example textarea</label> --}}
                            <textarea class="form-control mb-3" name="message" rows="3" placeholder="Mensagem*">{{ old('message') }}</textarea>
                            <p>Selecione as área(s) que deseja fazer contato</p>
                            @foreach (['Avaliação de laboratórios', 'Cursos/Treinamentos', 'Material de Referência', 'Ensaios de Proficiência', 'Administrativo/Financeiro', 'Apoio Técnico', 'Bônus Metrologia', 'Outros'] as $key => $area)
                                <div class="my-2">
                                    <input class="form-check-input" type="checkbox" name="areas[]" value="{{ $area }}"
                                        id="area{{ $key }}" {{ in_array($area, old('areas', [])) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="area{{ $key }}">
                                        {{ $area }}
                                    </label>
                                </div>
                            @endforeach
                        </div>
                        <div class="d-grid my-3">
                            <button id="btnEnviar" class="btn btn-primary" type="submit">ENVIAR</button>
                        </div>
                    </div>
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </form>
            </div>
        </div>
    </div>
    {{-- formulario --}}


    {{-- mapa --}}
    <div class="container-fluid SiteMapbox ">
        <iframe
            src="https://www.google.com/maps/embed?pb=!1m14!1m8!1m3!1d6909.986166011413!2d-51.177472!3d-30.008355!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x9519776178b124d7%3A0xa2ee25f04980c5b9!2sR.%20Santa%20Catarina%2C%2040%20-%20Santa%20Maria%20Goretti%2C%20Porto%20Alegre%20-%20RS%2C%2091030-330!5e0!3m2!1spt-BR!2sbr!4v1696857669341!5m2!1spt-BR!2sbr"
            style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
    </div>
    {{-- mapa --}}
@endsection
