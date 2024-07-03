@props([
    'label',
    'name',
    'value' => null,
    'type' => 'text',
    'required' => null,
    'id' => null,
    'placeholder' => null,
    'uppercase' => false,
    'pattern' => null,
    'title' => null,
    'tooltip' => null,
    'readonly' => false,
    'list' => null,
])

<label class="form-label">{!! $label !!} {!! ($required ? '<span class="text-danger-emphasis"> * </span>' : '') !!}</label>
@if ($tooltip)
  <span data-bs-toggle="tooltip" data-bs-html="true" title="{{ $tooltip }}">
  <i class="ri-information-line align-middle text-warning-emphasis" style="font-size: 1rem"></i></span>
@endif
<input {{ $attributes->class(['form-control']) }} type="{{ $type }}" name="{{ $name }}"
    @if ($value) value="{{ $value }}" @endif
    @if ($required) required @endif 
    @if ($id) id={{ $id }} @endif
    @if ($placeholder) placeholder="{{ $placeholder }}" @endif
    @if ($uppercase) oninput="this.value = this.value.toUpperCase()" @endif
    @if ($pattern) pattern='{{ $pattern }}' title="{{ $title }}" @endif
    @if ($list) list={{ $list }} @endif
    @readonly($readonly) >