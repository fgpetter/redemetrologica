<!-- JAVASCRIPT -->
<script src="{{ URL::asset('build/libs/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
<script src="{{ URL::asset('build/libs/simplebar/simplebar.min.js') }}"></script>
<script src="{{ URL::asset('build/js/plugins.js') }}"></script>
<script>
  if (document.querySelectorAll("[toast-list]").length > 0){
    console.log("[toast-list]")
    document.writeln(`<script src="{{ asset('build/libs/toastify-js/src/toastify.js') }}"><\/script>`)
  }

  if (document.querySelectorAll("[data-provider]").length > 0){
    console.log("[data-provider]")
    document.writeln(`<script src="{{ asset('build/libs/flatpickr/flatpickr.min.js') }}"><\/script>`);
  }

  if (document.querySelectorAll("[data-choices]").length > 0){
    console.log("[data-choices]")
    document.writeln(`<script src="{{ asset('build/libs/choices.js/public/assets/scripts/choices.min.js') }}"><\/script>`);
  }

</script>

@yield('script')
