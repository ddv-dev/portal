@foreach($customFields as $field)
    @php
        $name = "custom_fields[{$field->id}]";
        $old  = old("custom_fields.{$field->id}", $field->default_value);
        $required = $field->is_required ? 'required' : '';
    @endphp

    <label class="rm-form__full">
        {{ $field->name }}@if($field->is_required) <span class="rm-req">*</span>@endif

        @switch($field->field_format)
            @case('string')
            @case('link')
                <input type="text" name="{{ $name }}" value="{{ $old }}" {{ $required }}>
                @break

            @case('text')
                <textarea name="{{ $name }}" rows="4" {{ $required }}>{{ $old }}</textarea>
                @break

            @case('int')
            @case('float')
                <input type="number" step="{{ $field->field_format === 'float' ? 'any' : '1' }}"
                       name="{{ $name }}" value="{{ $old }}" {{ $required }}>
                @break

            @case('date')
                <input type="date" name="{{ $name }}" value="{{ $old }}" {{ $required }}>
                @break

            @case('bool')
                <select name="{{ $name }}" {{ $required }}>
                    <option value="">—</option>
                    <option value="1" @selected((string)$old === '1')>Да</option>
                    <option value="0" @selected((string)$old === '0')>Нет</option>
                </select>
                @break

            @case('list')
                <select name="{{ $name }}" {{ $required }}>
                    <option value="">—</option>
                    @foreach($field->possible_values as $v)
                        <option value="{{ $v }}" @selected((string)$old === (string)$v)>{{ $v }}</option>
                    @endforeach
                </select>
                @break

            @case('enumeration')
                {{-- значения приходят из enumerations, для простоты оставим текстовый ввод --}}
                <input type="text" name="{{ $name }}" value="{{ $old }}" {{ $required }}>
                @break

            @case('user')
                <input type="number" name="{{ $name }}" value="{{ $old }}" placeholder="ID пользователя" {{ $required }}>
                @break

            @default
                <input type="text" name="{{ $name }}" value="{{ $old }}" {{ $required }}>
        @endswitch
    </label>
@endforeach

