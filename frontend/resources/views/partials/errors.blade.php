{{-- Bloque de feedback de templates/fragments/inputField.html y selectField.html. Params: name --}}
@if (! $errors->has($name))
          <span class="fa fa-ok form-control-feedback" aria-hidden="true"></span>
@else
            <span class="fa fa-remove form-control-feedback" aria-hidden="true"></span>
            <span class="help-inline">{!! collect($errors->get($name))->map(fn ($m) => e($m))->join('<br />') !!}</span>
@endif
