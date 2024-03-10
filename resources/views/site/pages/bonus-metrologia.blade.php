@extends('site.layouts.layout-site')
@section('title')
  Bônus Metrologia
@endsection
@section('content')
  {{-- banner inicial --}}
  <div class="card text-bg-dark">
    <img src="{{ asset('build\images\site\BANNER-BONUS-METRO-1920x575-1.png') }}" class="card-img" alt="...">
  </div>
  <div class="container my-5">
    <div class="row justify-content-center align-items-center text-center">
      <div class="col">
        <h3><b>O SEBRAE ESTÁ SEMPRE AO LADO DA MICRO E PEQUENA EMPRESA!</b></h3>
      </div>
    </div>
  </div>
  {{-- banner inicial --}}


  <div class=" position-relative container-fluid  text-center row  py-5">
    <div class="col-12 SiteTitulo d-flex align-items-center justify-content-center ">
      <h1 class=" ">BÔNUS METROLOGIA</h1>
    </div>
    <div class="position-absolute top-50 start-50 translate-middle col-12 SiteTitulo--sombra ">
      <p class="">BÔNUS</p>
    </div>
  </div>
  <div class="container my-1">
    <div class="row justify-content-center align-items-center text-center">
      <div class="col">
        <p><b>O Bônus Metrologia é uma solução do Sebrae/RS, em parceria com a Rede Metrológica, que
            possibilita as micro e pequenas empresas acessar, a menor custo, os serviços de calibração
            de instrumentos de medição, análises em produtos e matérias primas diversas, ensaios e
            outros testes disponíveis nos diversos laboratórios reconhecidos e contratados pela Rede
            Metrológica do Rio Grande do Sul.
            Beneficiários: micro e pequenas empresas, micro e pequenos produtores rurais e
            microempreendedores individuais dos setores da indústria, comércio, serviços e agronegócios
            do Rio Grande do Sul.
            O valor máximo do atendimento por cliente é de R$ 5.000,00 (cinco mil reais) ao ano, dos
            quais o SEBRAE/RS apoia com 60%.
            O valor mínimo de cada atendimento é de R$ 100,00 (cem reais).</b></p>
      </div>
    </div>
  </div>
  <div class="container">
    <div class="row">
      <div class="col">
        <div class="card" style="width: 50rem;">
          <div class="card-body">
            <h5 class="card-title">Requisitos para atendimento:</h5>

            <ul>
              <li class="card-text">A micro e pequena empresa e o micro e pequeno produtor rural ter
                um
                faturamento anual de até R$ 4.800.000,00;</li>
              <li class="card-text">O microempreendedor individual ter um faturamento anual de até R$
                81.000,00;</li>
              <li class="card-text">Possuir Inscrição Estadual válida e ser o titular da propriedade,
                no caso de produtor rural:</li>
              <li class="card-text">Não estar inadimplente com o SEBRAE/RS;</li>
              <li class="card-text">Ter realizado seu cadastro ou atualização junto ao SEBRAE/RS
                através do 0800 570 0800;</li>
              <li class="card-text">Ter saldo disponível no ano no Bônus Metrologia;</li>
              <li class="card-text">Laboratório escolhido ter saldo no Bônus Metrologia.</li>
            </ul>
          </div>
        </div>
      </div>
    </div>
  </div>


  <div class="SiteCards__bgimage p-5 text-center mb-5 text-white "
    style="background-size: cover; background-image: url('{{ asset('build/images/site/banner-associe-se.png') }}');height:100%; width:100%;">
    <div class="container">
      <h1 class="text-white"><b>Encontre um laboratório</b></h1>
      <div class="my-2">
        <a href="/laboratorios-reconhecidos" type="button" class="btn  btn-light btn-lg">Laboratórios
          reconhecidos</a>
      </div>
    </div>
  </div>
@endsection
