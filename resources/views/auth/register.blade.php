<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-preloader="disable" data-theme="default" data-bs-theme="light">

<head>

  <meta charset="utf-8" />
  <title> Cadastre-se | Rede Metrológica RS</title>
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
                      <img src="{{ asset('build\images\site\LOGO_REDE_COLOR.png') }}" style="max-width: 12vw" class="card-img mb-3" alt="Rede Metrológica">
                      <h5 class="fs-3xl">Crie seu cadastro</h5>
                      <p class="mb-0 mt-3">Já tem uma conta ? <a href="auth-signin" class="fw-semibold text-primary text-decoration-underline"> Faça Login </a> </p>
                    </div>
                    <div class="p-2 mt-3">
                      <form class="needs-validation" novalidate id="signup" method="POST" action="{{ route('register') }}">
                      @csrf
                        <div class="mb-3">
                          <label for="username" class="form-label">Nome <span class="text-danger">*</span></label>
                          <input type="text" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name') }}" 
                            id="username" required>
                          @error('name')
                          <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                          </span>
                          @enderror
                        </div>
                        <div class="mb-3">
                          <label for="useremail" class="form-label">Email <span class="text-danger">*</span></label>
                          <input type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" 
                            id="useremail" required>
                          @error('email')
                          <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                          </span>
                          @enderror
                        </div>
                        <div class="mb-2">
                          <label for="password-input" class="form-label">Senha <span class="text-danger">*</span></label>
                          <input type="password" class="form-control @error('password') is-invalid @enderror" name="password" 
                            id="password-input" placeholder="Enter password" required>
                          @error('password')
                          <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                          </span>
                          @enderror
                        </div>

                        <div class="mb-2">
                          <label for="confirm-password-input" class="form-label">Repita a senha <span class="text-danger">*</span></label>
                          <input type="password" class="form-control @error('password') is-invalid @enderror" name="password_confirmation" 
                            id="confirm-password-input" placeholder="Enter password" required>
                          @error('password')
                          <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                          </span>
                          @enderror
                        </div>

  
                        <div class="mb-4">
                          <p class="mb-0 fs-xs text-muted fst-italic">By registering you agree to the Steex <a href="pages-term-conditions" class="text-primary text-decoration-underline fst-normal fw-medium">Terms of Use</a></p>
                        </div>
  
                        <div id="password-contain" class="p-3 bg-light mb-2 rounded">
                          <h5 class="fs-sm">Password must contain:</h5>
                          <p id="pass-length" class="invalid fs-xs mb-2">Mínimo de <b>8 caracteres</b></p>
                          <p id="pass-lower" class="invalid fs-xs mb-2">Letras <b>minúsculas</b></p>
                          <p id="pass-upper" class="invalid fs-xs mb-2">Letras <b>maiúsculas</b></p>
                          <p id="pass-number" class="invalid fs-xs mb-2">Um ou mais <b>números</b> (0-9)</p>
                          <p id="pass-confirm" class="invalid fs-xs mb-0">Senhas <b>combinam</b></p>
                        </div>
  
                        <div class="mt-4">
                          <button class="btn btn-primary w-100" type="submit">Sign Up</button>
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
<script src="{{ URL::asset('build/js/pages/passowrd-create.init.js') }}"></script>

@endsection
  @include('layouts.vendor-scripts')
</body>

</html>