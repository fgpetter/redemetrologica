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
                    <p class="mb-0 mt-3">Voltar a tela de <a href="/login" class="fw-semibold text-secondary text-decoration-underline"> login </a> </p>
                  </div>
                  <div class="p-2 mt-3">
                    @if( session('status') )
                      <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('status') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                      </div>
                    @endif

                    <div>
                        <form method="POST" action="{{ route('password.update') }}">
                            @csrf
                            <input type="hidden" name="token" value="{{ $token }}">
    
                            <div class="mb-3">
                                <label for="email" class="form-label">Usuário <small class="text-muted">(email)</small></label>
                                <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                    value="{{ old('email') }}" id="email" name="email" placeholder="Digite seu email">
                                @error('email')<span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
    
                            <div class="mb-3">
                                <label for="password-input" class="form-label">Senha</label>
                                <input type="password" class="form-control @error('password') is-invalid @enderror" 
                                    value="{{ old('password') }}" id="password-input" name="password" placeholder="Digite sua senha">
                                @error('password')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
    
                            <div class="mb-3">
                                <label for="confirm-password-input" class="form-label">Confirme a senha</label>
                                <input type="password" class="form-control @error('password_confirmation') is-invalid @enderror" 
                                    value="{{ old('password_confirmation') }}" id="confirm-password-input" name="password_confirmation" placeholder="Confirme sua senha">
                                @error('password_confirmation')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
    
                            <div class="row mb-0">
                                <div class="col-md-6 offset-md-4">
                                    <button type="submit" class="btn btn-primary">Alterar senha</button>
                                </div>
                            </div>
                        </form>
    
                    </div>

                    <div id="password-contain" class="p-3 bg-light mb-2 rounded mt-3">
                        <h5 class="fs-sm">Password must contain:</h5>
                        <p id="pass-length" class="invalid fs-xs mb-2">Mínimo de <b>8 caracteres</b></p>
                        <p id="pass-lower" class="invalid fs-xs mb-2">Letras <b>minúsculas</b></p>
                        <p id="pass-upper" class="invalid fs-xs mb-2">Letras <b>maiúsculas</b></p>
                        <p id="pass-number" class="invalid fs-xs mb-2">Um ou mais <b>números</b> (0-9)</p>
                        <p id="pass-confirm" class="invalid fs-xs mb-0">Senhas <b>combinam</b></p>
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
<script src="{{ URL::asset('build/js/pages/passowrd-create.init.js') }}"></script>
@endsection
  @include('layouts.vendor-scripts')
</body>

</html>