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
])

<label class="form-label">{!! $label !!}</label>
<input {{ $attributes->class(['form-control']) }} type="{{ $type }}" name="{{ $name }}"
    @if ($value) value="{{ $value }}" @endif
    @if ($required) required @endif 
    @if ($id) id={{ $id }} @endif
    @if ($placeholder) placeholder="{{ $placeholder }}" @endif
    @if ($uppercase) oninput="this.value = this.value.toUpperCase()" @endif
    @if ($pattern) pattern='{{ $pattern }}' title="{{ $title }}" @endif>