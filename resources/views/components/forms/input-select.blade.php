@props(['label', 'name', 'id' => null])

<label class="form-label">{!! $label !!}</label>
<select class="form-select" name={{ $name }} @if ($id) id={{ $id }} @endif>
    {{ $slot }}
</select>
