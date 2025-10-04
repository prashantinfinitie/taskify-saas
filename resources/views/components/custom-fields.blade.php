@props(['fields','isEdit'])

@if(isset($fields) && count($fields) > 0)

    <div class="row">
        <div class="col-md-12">
            <label class="form-label fw-semibold mb-2 fs-5">
                <?= get_label('custom_fields', 'Custom Fields') ?>
            </label>
            
            <div class="row row-cols-1 row-cols-md-2 g-3">
                @foreach($fields as $field)
                    <div class="col">
                        <label 
                        class="form-label small mb-1 d-block text-truncate w-100" 
                        for="cf_{{ $field->id }}" 
                        title="{{ $field->field_label }}"
                    >
                        {{ $field->field_label }}
                        @if($field->required == '1')
                            <span class="asterisk text-danger">*</span>
                        @endif
                    </label>
                    


                        @php
                            $fieldValue = $values[$field->id] ?? old('custom_fields.'.$field->id);
                            $isRequired = $field->required == '1';
                        @endphp

                        @switch($field->field_type)

                            @case('text')
                            @case('password')
                            @case('number')
                            <input 
                                type="{{ $field->field_type }}" 
                                id="{{ $isEdit ? 'edit_cf_' . $field->id : 'cf_' . $field->id }}" 
                                name="custom_fields[{{ $field->id }}]" 
                                class="form-control form-control-md" 
                                placeholder="{{ $field->field_label }}" 
                                value="{{ $fieldValue }}"
                                @if($isRequired) required @endif
                            >
                            @break

                            @case('textarea')
                            <textarea 
                                id="{{ $isEdit ? 'edit_cf_' . $field->id : 'cf_' . $field->id }}" 
                                name="custom_fields[{{ $field->id }}]" 
                                class="form-control form-control-md" 
                                rows="2" 
                                placeholder="{{ $field->field_label }}"
                                @if($isRequired) required @endif
                            >{{ $fieldValue }}</textarea>
                            @break
                        
                            @case('date')
                            <input 
                                type="text" 
                                id="{{ $isEdit ? 'edit_cf_' . $field->id : 'cf_' . $field->id }}" 
                                name="custom_fields[{{ $field->id }}]" 
                                class="form-control form-control-md custom-datepicker" 
                                placeholder="{{ $field->field_label }}"
                                value="{{ $fieldValue }}"
                                autocomplete="off"
                                @if($isRequired) required @endif
                            >
                            @break

                            @case('select')
                            <select 
                               id="{{ $isEdit ? 'edit_cf_' . $field->id : 'cf_' . $field->id }}" 
                               name="custom_fields[{{ $field->id }}]" 
                               class="form-select form-select-md"
                                
                               @if($isRequired) required @endif
                            >
                                <option value="{{ $field->field_label }}">{{ $field->field_label }}</option>
                                @foreach(json_decode($field->options, true) ?? [] as $option)
                                    <option value="{{ $option }}" {{ $fieldValue == $option ? 'selected' : '' }}>
                                        {{ $option }}
                                    </option>
                                @endforeach
                            </select>
                            @break

                            @case('radio')
                            <div class="d-flex flex-wrap gap-2">
                                @foreach(json_decode($field->options, true) ?? [] as $index => $option)
                                    <div class="form-check me-3">
                                        <input 
                                            type="radio" 
                                            id="{{ $isEdit ? 'edit_cf_' . $field->id . '_' . $index : 'cf_' . $field->id . '_' . $index }}"
                                            name="custom_fields[{{ $field->id }}]" 
                                            value="{{ $option }}" 
                                            class="form-check-input"
                                            {{ $fieldValue == $option ? 'checked' : '' }}
                                            @if($isRequired) required @endif
                                        >
                                        <label class="form-check-label medium" for="{{ $isEdit ? 'edit_cf_' . $field->id . '_' . $index : 'cf_' . $field->id . '_' . $index }}"
                                            >
                                            {{ $option }}
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                            @break

                            @case('checkbox')
                            @php
                                $checkboxValues = is_string($fieldValue) && $fieldValue ? json_decode($fieldValue, true) : [];
                                $checkboxValues = is_array($checkboxValues) ? $checkboxValues : [];
                            @endphp
                            <div class="d-flex flex-wrap gap-2">
                                @foreach(json_decode($field->options, true) ?? [] as $index => $option)
                                    <div class="form-check">
                                        <input 
                                            class="form-check-input" 
                                            type="checkbox" 
                                            name="custom_fields[{{ $field->id }}][]" 
                                            id="customCheck_{{ $field->id }}_{{ $index }}" 
                                            value="{{ $option }}"
                                            {{ in_array($option, $checkboxValues) ? 'checked' : '' }}
                                            @if($isRequired && $index == 0) required @endif
                                        >
                                        <label class="form-check-label medium" for="customCheck_{{ $field->id }}_{{ $index }}">
                                            {{ $option }}
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                            @break

                            @default
                            <p class="text-muted small">Unsupported field type</p>
                            @break

                        @endswitch

                        @error('custom_fields.'.$field->id)
                            <div class="text-danger small">{{ $message }}</div>
                        @enderror
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@endif