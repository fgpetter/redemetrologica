<!doctype html>
<html lang="pt-BR" data-preloader="disable" data-theme="default" data-bs-theme="light">

<head>

  <meta charset="utf-8" />
  <title> Esqueci minha senha | Rede Metrológica RS</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <!-- CSRF Token -->
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <!-- App favicon -->
  <link rel="shortcut icon" href="{{ URL::asset('build/images/favicon.png') }}">

  {{-- font wansome --}}
  <script src="https://kit.fontawesome.com/02f4ca9b8a.js" crossorigin="anonymous"></script>
  @include('layouts.head-css')
</head>

<body>

<section class="auth-page-wrapper py-5 position-relative d-flex align-items-center justify-content-center min-vh-100">
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-lg-8">
        <div class="card mb-0">
          <div class="row g-0 align-items-center">
            <div class="col-lg-8 mx-auto">
              <div class="card mb-0 border-0 shadow-none mb-0">
                <div class="card-body p-sm-5 m-lg-4">
                  <div class="text-center">
                    <img src="{{ asset('build\images\site\LOGO_REDE_COLOR.png') }}" style="max-width: 12vw" class="card-img mb-3" alt="Rede Metrológica RS">
                    <h5 class="fs-3xl">Recuperar senha</h5>
                    <p class="mb-0 mt-3">Voltar a tela de <a href="login" class="fw-semibold text-secondary text-decoration-underline"> login </a> </p>
                  </div>
                  <div class="p-2 mt-3">
                    @if( session('status') )
                      <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('status') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                      </div>
                    @endif
                    <form action="{{ route('send-reset-link-email') }}" method="post">
                      @csrf
                      <div class="mb-3">
                        <label for="username" class="form-label">Usuário <small class="text-muted">(email)</small></label>
                        <input type="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" 
                          id="username" name="email" placeholder="Digite seu email">
                          <span class="text-muted"> Digite seu e-mail de login e será enviado um link para redefinir sua senha.</strong></span>
                        @error('email') <span class="invalid-feedback" role="alert"> {{ $message }}</strong></span> @enderror
                      </div>

                      <div class="mt-4">
                        <button class="btn btn-primary w-100" type="submit">Enviar e-mail</button>
                      </div>

                    </form>
                  </div>
                </div><!-- end card body -->
              </div><!-- end card -->
            </div>
            <!--end col-->
          </div>
          <!--end row-->
        </div>
      </div>
      <!--end col-->
    </div>
    <!--end row-->
  </div>
  <!--end container-->
</section>

@section('script')

<script src="{{ URL::asset('build/js/pages/password-addon.init.js') }}"></script>

@endsection
  @include('layouts.vendor-scripts')
</body>

</html>