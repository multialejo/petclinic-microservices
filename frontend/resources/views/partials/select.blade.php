{{-- Port de templates/fragments/selectField.html. Params: label, name, items [{id, name}], selected? (nombre; el valor de cada opción es el nombre, como el monolito) --}}
@php($current = (string) old($name, $selected ?? ''))
      <div class="{{ 'form-group'.($errors->has($name) ? ' has-error' : '') }}">
        <label for="{{ $name }}" class="col-sm-2 control-label">{{ $label }}</label>

        <div class="col-sm-10">
          <select id="{{ $name }}" name="{{ $name }}">
@foreach ($items as $item)
            <option value="{{ $item['name'] }}"@if ($current === (string) $item['name']) selected="selected"@endif>{{ $item['name'] }}</option>
@endforeach
          </select>
@include('partials.errors', ['name' => $name])
        </div>
      </div>
