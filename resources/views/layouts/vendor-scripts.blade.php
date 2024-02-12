<!-- JAVASCRIPT -->
<script src="{{ URL::asset('build/libs/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
<script src="{{ URL::asset('build/libs/simplebar/simplebar.min.js') }}"></script>

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
