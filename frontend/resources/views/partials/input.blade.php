{{-- Port de templates/fragments/inputField.html. Params: label, name, type ('text'|'date'), value?, min? --}}
      <div class="{{ 'form-group'.($errors->has($name) ? ' has-error' : '') }}">
        <label for="{{ $name }}" class="col-sm-2 control-label">{{ $label }}</label>
        <div class="col-sm-10">
          <div>
@if ($type === 'date')
            <input class="form-control" type="date"@if (! empty($min)) min="{{ $min }}"@endif id="{{ $name }}" name="{{ $name }}" value="{{ old($name, $value ?? '') }}" />
@else
            <input class="form-control" type="text" id="{{ $name }}" name="{{ $name }}" value="{{ old($name, $value ?? '') }}" />
@endif
          </div>
@include('partials.errors', ['name' => $name])
        </div>
      </div>
