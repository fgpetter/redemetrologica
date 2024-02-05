@props([
  'label',
  'name',
  'value' => null,
  'type' => 'text',
  'required' => null,
  'id' => null,
  'placeholder' => null,
  'uppercase' => false
])

<label class="form-label">{{$label}}</label>
<input 
  {{ $attributes->class(['form-control']) }}
  type={{$type}} 
  name={{ $name }} 
  value="{{ $value }}"
  @if ($required) {{$required}} @endif
  @if ($id) id={{$id}} @endif
  @if ($placeholder) placeholder={{$placeholder}} @endif
  @if ($uppercase) oninput="this.value = this.value.toUpperCase()" @endif
>