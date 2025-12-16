@props([
    'label',
    'name' => null,
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
    'disabled' => false,
    'list' => null,
    'mask' => null,
])

@php
    $maskAttr = match($mask) {
        'cpf' => 'x-mask="999.999.999-99"',
        'cnpj' => 'x-mask="99.999.999/9999-99"',
        'cpf_cnpj' => 'x-mask:dynamic="$input.replace(/\D/g, \'\').length > 11 ? \'99.999.999/9999-99\' : \'999.999.999-99\'"',
        'cep' => 'x-mask="99999-999"',
        'telefone' => 'x-mask:dynamic="$input.replace(/\D/g, \'\').length === 11 ? \'(99) 9 9999-9999\' : \'(99) 9999-9999\'"',
        'money' => 'x-mask:dynamic="moneyMask($input)"',
        default => null,
    };
@endphp

<div x-data="{ showError: false }">
    <label class="form-label mb-0">{!! $label !!} {!! ($required ? '<span class="text-danger-emphasis"> * </span>' : '') !!}</label>
    @if ($tooltip)
      <span data-bs-toggle="tooltip" data-bs-html="true" title="{{ $tooltip }}">
      <i class="ri-information-line align-middle text-warning-emphasis" style="font-size: 1rem"></i></span>
    @endif
    <input {{ $attributes->class(['form-control']) }} type="{{ $type }}" name="{{ $name }}"
        x-on:invalid="showError = true; $el.focus()"
        x-on:input="showError = false"
        :class="{ 'is-invalid': showError }"
        @if ($value) value="{{ $value }}" @endif
        @if ($required) required @endif 
        @if ($id) id={{ $id }} @endif
        @if ($placeholder) placeholder="{{ $placeholder }}" @endif
        @if ($uppercase) oninput="this.value = this.value.toUpperCase()" @endif
        @if ($pattern) pattern='{{ $pattern }}' title="{{ $title }}" @endif
        @if ($list) list={{ $list }} @endif
        @readonly($readonly) 
        @disabled($disabled) 
        @if ($maskAttr) {!! $maskAttr !!} @endif >
    <span x-show="showError" x-cloak class="text-danger small">Obrigatório</span>
</div>