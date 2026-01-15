@props([
  'label',
  'tooltip' => null,
  'name', 
  'id' => null,
  'required' => null,
  'helper' => null,
  'errorBag' => null
])

<div x-data="{ showError: false }">
  <label class="form-label mb-0">{!! $label !!} {!! ($required ? '<span class="text-danger-emphasis">*</span>' : '') !!}</label>
  @if($tooltip)
    <span data-bs-toggle="tooltip" data-bs-html="true" title="{{ $tooltip }}">
    <i class="ri-information-line align-middle text-warning-emphasis" style="font-size: 1rem"></i></span>
  @endif
  <select {{ $attributes->class(['form-select']) }}" name={{ $name }} 
    x-on:invalid="showError = true; $el.focus()"
    x-on:change="showError = false"
    :class="{ 'is-invalid': showError }"
    @if ($id) id={{ $id }} @endif 
    @if ($required) required @endif>
      {{ $slot }}
  </select>
  <span x-show="showError" x-cloak class="text-danger small">Obrigatório</span>
  @error($name, $errorBag) <div class="text-warning">{{ $message }}</div> @enderror
</div>