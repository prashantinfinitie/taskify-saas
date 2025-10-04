@extends('layout')

@section('title')
    {{ get_label('lead_forms', 'Lead Forms') }}
@endsection

@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between mb-2 mt-4">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb breadcrumb-style1">
                    <li class="breadcrumb-item">
                        <a href="{{ route('home.index') }}">{{ get_label('home', 'Home') }}</a>
                    </li>
                    <li class="breadcrumb-item">
                        {{ get_label('leads_management', 'Leads Management') }}
                    </li>
                    <li class="breadcrumb-item">
                        <a href="{{ route('lead-forms.index') }}">{{ get_label('lead_forms', 'Lead Forms') }}</a>
                    </li>
                    <li class="breadcrumb-item active">
                        {{ get_label('responses', 'Responses') }}
                    </li>
                </ol>
            </nav>
        </div>
        {{-- @dd($leadForm->count()) --}}
        @if ($leadForm->count() > 0)
        <div class="card">
            <div class="card-body">
                <div class="table-responsive text-nowrap">
                    <input type="hidden" id="data_type" value="lead-form-responses">
                    <table id="table" data-toggle="table" data-url="{{ route('lead-forms.responses.list', $leadForm->id) }}"
                        data-loading-template="loadingTemplate" data-icons-prefix="bx" data-icons="icons"
                        data-show-refresh="true" data-total-field="total" data-trim-on-search="false" data-data-field="rows"
                        data-page-list="[5, 10, 20, 50, 100, 200]" data-search="true" data-side-pagination="server"
                        data-show-columns="true" data-pagination="true" data-sort-name="created_at" data-sort-order="desc"
                        data-mobile-responsive="true">
                        <thead>
                            <tr>
                                <th data-checkbox="true"></th>
                                <th data-field="id" data-sortable="true">{{ get_label('id', 'ID') }}</th>
                                <th data-field="name" data-sortable="true">{{ get_label('name', 'Name') }}</th>
                                <th data-field="email" data-sortable="true">{{ get_label('email', 'Email') }}</th>
                                <th data-field="phone" data-sortable="true">{{ get_label('phone', 'Phone') }}</th>
                                <th data-field="company" data-sortable="true">{{ get_label('company', 'Company') }}
                                </th>
                                <th data-field="submitted_at" data-sortable="true">
                                    {{ get_label('submitted_at', 'Submitted At') }}
                                </th>
                                <th data-field="actions">{{ get_label('actions', 'Actions') }}</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
         @else
            @php $type = 'Lead Forms'; @endphp
            <x-empty-state-card :type="$type" />
        @endif
    </div>

    <script>
        var label_update = '{{ get_label('update', 'Update') }}';
        var label_delete = '{{ get_label('delete', 'Delete') }}';
    </script>
    <script src="{{ asset('assets/js/pages/lead-form.js') }}"></script>
@endsection