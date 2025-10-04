@php
    $isClient = isClient();
    $for = isset($for) && $for != '' ? $for : 'users';
@endphp
<label class="form-label" for="{{ $name }}">{{ $label }}</label>
<div class="input-group">
    <select class="form-control js-example-basic-multiple" name="{{ $name }}" multiple="multiple" data-placeholder="<?= get_label('type_to_search', 'Type to search') ?>">
        @foreach($items as $item)
            @php
                if ($isClient) {
                    $selected = (isset($authUserId) && $authUserId == $item->id && $for == 'clients') ? 'selected' : '';
                } else {
                    $selected = (isset($authUserId) && $authUserId == $item->id) ? 'selected' : '';
                }
            @endphp
            <option value="{{ $item->id }}" {{ $selected }}>{{ $item->first_name }} {{ $item->last_name }}</option>
        @endforeach
    </select>
</div>
