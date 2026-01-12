@props([
  'label',
  'tooltip' => null,
  'name', 
  'required' => null, 
  'id' => null, 
  'uppercase' => false,
  'helper' => null,
  'sublabel' => null,
])

<div x-data="{ showError: false }">
  <label class="form-label mb-0">{!! $label !!} {!! ($required ? '<span class="text-danger-emphasis"> * </span>' : '') !!}</label>
  @if ($tooltip)
    <span data-bs-toggle="tooltip" data-bs-html="true" title="{{ $tooltip }}">
    <i class="ri-information-line align-middle text-warning-emphasis" style="font-size: 1rem"></i></span>
  @endif
  @if($sublabel) <br> <small class="form-text text-muted">{{ $sublabel }}</small> @endif

  <textarea {{ $attributes->class(['form-control']) }} name={{ $name }} rows="3"
    x-on:invalid="showError = true; $el.focus()"
    x-on:input="showError = false"
    :class="{ 'is-invalid': showError }"
    @if ($id) id={{ $id }} @endif
    @if ($required) required @endif
    @if ($uppercase) oninput="this.value = this.value.toUpperCase()" @endif>{{ $slot }}</textarea>
  <span x-show="showError" x-cloak class="text-danger small">Obrigatório</span>
  @if ($helper) <small class="form-text text-muted">{{ $helper }}</small> @endif
</div>