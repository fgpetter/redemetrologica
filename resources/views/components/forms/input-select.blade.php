@props([
  'label',
  'tooltip' => null,
  'name', 
  'id' => null,
  'required' => null,
  'helper' => null,
  'errorBag' => null
])

<label class="form-label">{!! $label !!} {!! ($required ? '<span class="text-danger-emphasis">*</span>' : '') !!}</label>
@if($tooltip)
  <span data-bs-toggle="tooltip" data-bs-html="true" title="{{ $tooltip }}">
  <i class="ri-information-line align-middle text-warning-emphasis" style="font-size: 1rem"></i></span>
@endif
<select class="form-select" name={{ $name }} 
  @if ($id) id={{ $id }} @endif 
  @if ($required) required @endif>
    {{ $slot }}
</select>

@error($name, $errorBag) <div class="text-warning">{{ $message }}</div> @enderror