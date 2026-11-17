{{-- Campo de formulario con el error 422 de la API junto al campo. Params: name, label, type?, value?, attrs? --}}
<div class="mb-3">
    <label for="{{ $name }}" class="form-label">{{ $label }}</label>
    <input id="{{ $name }}" name="{{ $name }}" type="{{ $type ?? 'text' }}"
           value="{{ old($name, $value ?? '') }}" {!! $attrs ?? '' !!}
           class="form-control @error($name) is-invalid @enderror">
    @error($name)
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>
