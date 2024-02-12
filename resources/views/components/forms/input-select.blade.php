@props(['label', 'name'])

<label class="form-label">{{ $label }}</label>
<select class="form-select" name={{ $name }}>
    {{ $slot }}
</select>
