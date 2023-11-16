/*
Template Name: Steex - Admin & Dashboard Template
Author: Themesbrand
Version: 1.0.0
Website: https://Themesbrand.com/
Contact: Themesbrand@gmail.com
File: Common Plugins Js File
*/

//Common plugins
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