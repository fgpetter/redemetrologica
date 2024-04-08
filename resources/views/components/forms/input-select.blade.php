@props([
    'label', 
    'name', 
    'id' => null,
    'required' => null
])

<label class="form-label">{!! $label !!} {!! ($required ? '<span class="text-danger-emphasis">*</span>' : '') !!}</label>
<select class="form-select" name={{ $name }} @if ($id) id={{ $id }} @endif>
    {{ $slot }}
</select>
