<!-- JAVASCRIPT -->
<script src="{{ URL::asset('build/libs/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
<script src="{{ URL::asset('build/libs/sweetalert2/sweetalert2.all.min.js') }}"></script>
<script src="{{ URL::asset('build/js/pages/imask.js') }}"></script>
<script src="{{ URL::asset('build/js/jquery-3.7.1.min.js') }}"></script>
<script src="{{ URL::asset('build/js/jquery.mask.min.js') }}"></script>
<script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/mask@3.x.x/dist/cdn.min.js"></script>
{{-- Driver.js --}}
<script defer src="https://cdn.jsdelivr.net/npm/driver.js@latest/dist/driver.js.iife.js"></script>
{{-- tom-select --}}
<script defer src="https://cdn.jsdelivr.net/npm/tom-select@2.6.1/dist/js/tom-select.complete.min.js"></script>

@if( !in_array(\Request::getRequestUri(), ['/register', '/login', '/forgot-password']) )
<script src="{{ URL::asset('build/libs/simplebar/simplebar.min.js') }}"></script>
<script src="{{ URL::asset('build/js/app.js') }}"></script>
@endif

<script src="{{ URL::asset('build/js/custom.js') }}"></script>

<script>
  function loadJsLib() {
    const toastListExists = document.querySelector('[toast-list]');
    const dataProviderExists = document.querySelector('[data-provider]');
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
  }
  document.addEventListener('DOMContentLoaded', loadJsLib);
</script>
@yield('script')

