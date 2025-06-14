<!-- JAVASCRIPT -->
<script src="{{ URL::asset('build/libs/choices.js/public/assets/scripts/choices.min.js') }}"></script>
<script src="{{ URL::asset('build/libs/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
<script src="{{ URL::asset('build/libs/sweetalert2/sweetalert2.all.min.js') }}"></script>
<script src="{{ URL::asset('build/js/pages/imask.js') }}"></script>
<script src="{{ URL::asset('build/js/jquery-3.7.1.min.js') }}"></script>
<script src="{{ URL::asset('build/js/jquery.mask.min.js') }}"></script>
<script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/mask@3.x.x/dist/cdn.min.js"></script>
{{-- Driver.js --}}
<script src="https://cdn.jsdelivr.net/npm/driver.js@latest/dist/driver.js.iife.js"></script>

@if( !in_array(\Request::getRequestUri(), ['/register', '/login', '/forgot-password']) )
<script src="{{ URL::asset('build/libs/simplebar/simplebar.min.js') }}"></script>
<script src="{{ URL::asset('build/js/app.js') }}"></script>
@endif

<script src="{{ URL::asset('build/js/custom.js') }}"></script>

<script>
  function loadJsLib() {
    const toastListExists = document.querySelector('[toast-list]');
    const dataProviderExists = document.querySelector('[data-provider]');
    const dataChoicesExists = document.querySelector('[data-choices]');
    if (toastListExists) {
      console.log('toastListExists')
      const toast = document.createElement('script');
      toast.src = "{{ asset('build/libs/toastify-js/src/toastify.js') }}";
      document.head.appendChild(toast);
    }
    if (dataProviderExists) {
      const flatpick = document.createElement('script');
      flatpick.src = "{{ asset('build/libs/flatpickr/flatpickr.min.js') }}";
      document.head.appendChild(flatpick);
    }
    if (dataChoicesExists) {
      const choices = document.createElement('script');
      choices.src = "{{ asset('build/libs/choices.js/public/assets/scripts/choices.min.js') }}";
      document.head.appendChild(choices);
    }
  }
  document.addEventListener('DOMContentLoaded', loadJsLib);
</script>
@yield('script')

