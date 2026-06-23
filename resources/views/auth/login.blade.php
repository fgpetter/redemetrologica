<!doctype html>
<html lang="pt-BR" data-preloader="disable" data-theme="default" data-bs-theme="light">

<head>

  <meta charset="utf-8" />
  <title> Login | Rede Metrológica RS</title>
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
                    <h5 class="fs-3xl">Olá, faça seu login</h5>
                    <p class="mb-0 mt-3">Ainda não tem cadastro ? <a href="register" class="fw-semibold text-secondary text-decoration-underline"> Cadastre-se</a> </p>
                  </div>
                  <div class="p-2 mt-3">

                    @error('document')
                      <div class="alert alert-danger alert-top-border" style="font-size: 0.9rem" role="alert">
                        {!! $message !!}
                      </div>
                    @enderror

                    <form action="{{ route('login')}}" method="post">
                      @csrf
                      <div class="mb-3">
                        <label for="username" class="form-label">Usuário <small class="text-muted">(email)</small></label>
                        <input type="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" id="username" name="email" placeholder="Digite seu email">
                        @error('email')
                        <span class="invalid-feedback" role="alert">
                          <strong>{{ $message }}</strong>
                        </span>
                        @enderror
                      </div>

                      <div class="mb-3">
                        <div class="float-end">
                          <a href="/forgot-password" class="text-link">Esqueci minha senha</a>
                        </div>
                        <label class="form-label" for="password-input">Senha <span class="text-danger">*</span></label>
                        <div class="position-relative auth-pass-inputgroup mb-3">
                          <input type="password" class="form-control password-input pe-5 @error('password') is-invalid @enderror" id="password-input"  name="password"  placeholder="Digite sua senha">
                          <button class="btn btn-link position-absolute end-0 top-0 text-decoration-none text-muted password-addon" type="button" id="password-addon"><i class="ri-eye-fill align-middle"></i></button>
                          @error('password')
                          <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                          </span>
                          @enderror
                        </div>
                      </div>

                      <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="remember" value="1" id="auth-remember-check">
                        <label class="form-check-label" for="auth-remember-check">Manter logado</label>
                      </div>

                      <div class="mt-4">
                        <button class="btn btn-primary w-100" type="submit">Login</button>
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

  <!-- helper modal -->
  <div class="modal fade" id="registerLoginHelper" tabindex="-1" aria-labelledby="registerLoginHelperLabel" aria-modal="true">
    <div class="modal-dialog modal-dialog-right">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="registerLoginHelperLabel">Novo sistema de inscrições: <span class="text-primary"> Login </span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <h5>Olá, você é novo por aqui?</h5>
                <p>O sistema de <b>inscrições da Rede Metrológica RS mudou</b> 
                  agora você terá uma área de cliente onde poderá gerenciar todas suas inscrições.
                  <br><br>
                  Se você já é cadastrado, basta fazer o login normalmente.
                  <br>
                  Se você é novo por aqui, clique em <b>Cadastre-se</b> e faça seu cadastro.
                </p>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-light" data-bs-dismiss="modal" onclick="dontShowModalLogin()">Não mostrar novamente</button>
              <button type="button" class="btn btn-primary" data-bs-dismiss="modal">OK</button>
            </div>
        </div>
    </div>
  </div>

</section>

<script src="{{ URL::asset('build/js/pages/password-addon.init.js') }}"></script>
<script>
  document.addEventListener('DOMContentLoaded', function() {

    if (localStorage.getItem('registerLoginHelper') == 'false') {
      return;

    } else {
      const registerLoginHelper = new bootstrap.Modal('#registerLoginHelper');
      registerLoginHelper.show()
    }
  });

  function dontShowModalLogin() {
    localStorage.setItem('registerLoginHelper', 'false');
  }
</script>

  @include('layouts.vendor-scripts')

</body>

</html>