@props([
    'label',
    'name',
    'value' => 1,
    'title' => null,
    'tooltip' => null,
    'checked' => false,
])

<label class="form-label mb-0"> &nbsp; </label>
<div class="form-check bg-light rounded check-bg" style="padding: 0.65rem 1.8rem 0.65rem;">
  <input {{ $attributes->class(['form-check-input']) }} 
  name="{{ $name }}" 
  id="{{ 'check-'.$name }}" 
  @if ($value) value="{{ $value }}" @endif
  @checked($checked)
  type="checkbox" >
  <label class="form-check-label" for="{{ 'check-'.$name }}"
    @if($tooltip) data-bs-toggle="tooltip" data-bs-html="true" title="{{ $tooltip }}" @endif
    >{{ $label }}</label>
</div>