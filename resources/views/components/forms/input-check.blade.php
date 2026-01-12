@props([
    'label',
    'name',
    'value' => null,
    'required' => null,
    'title' => null,
    'tooltip' => null,
    'readonly' => false,
])

<div x-data="{ showError: false }">
  <label class="form-label">{!! $label !!} {!! ($required ? '<span class="text-danger-emphasis"> * </span>' : '') !!}</label>
  @if ($tooltip)
    <span data-bs-toggle="tooltip" data-bs-html="true" title="{{ $tooltip }}">
    <i class="ri-information-line align-middle text-warning-emphasis" style="font-size: 1rem"></i></span>
  @endif

  <div class="form-check">
    <input {{ $attributes->class(['form-check-input']) }} type="checkbox" name="{{ $name }}" id="{{ $name }}"
      x-on:invalid="showError = true; $el.focus()"
      x-on:change="showError = false"
      :class="{ 'is-invalid': showError }"
      @if ($value) value="{{ $value }}" @endif
      @if ($required) required @endif
      @readonly($readonly)
    >
    <label {{ $attributes->class(['form-check-label']) }} for="{{ $name }}">{{ $label }}</label>
  </div>
  <span x-show="showError" x-cloak class="text-danger small">Obrigatório</span>
</div>