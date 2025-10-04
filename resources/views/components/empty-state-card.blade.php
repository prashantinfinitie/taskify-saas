@php
    $prefix = null;
    $currentRoute = Route::current();
    if ($currentRoute) {
        $uriSegments = explode('/', $currentRoute->uri());
        $prefix = count($uriSegments) > 1 ? $uriSegments[0] : '/';
    }

@endphp

<div class="{{ Request::is($prefix . '/home') ? ' ' : 'card' }} empty-state text-center">
    <div class="card-body">
        <div class="misc-wrapper">
            <h2 class="mx-2 mb-2">
                {{ get_label(strtolower($type), $type) . ' ' . get_label('not_found', 'Not Found') }}
            </h2>
            <p class="mx-2 mb-4">{{ get_label('oops!', 'Oops!') }} 😖
                {{ get_label('data_does_not_exists', 'Data does not exists') }}.
            </p>

            @if (Request::is('*favorite*'))
                <a class="btn btn-primary" href="{{ route('projects.index') }}">
                    {{ get_label('projects', 'Projects') }}
                </a>
            @else
                @php
                    $typeLower = strtolower($type);
                    $typeSlug = str_replace(' ', '-', $typeLower);
                    $modalTargets = [
                        'todos' => '#create_todo_modal',
                        'tags' => '#create_tag_modal',
                        'status' => '#create_status_modal',
                        'leave-requests' => '#create_leave_request_modal',
                        'contract-types' => '#create_contract_type_modal',
                        'contracts' => '#create_contract_modal',
                        'payment-methods' => '#create_pm_modal',
                        'allowances' => '#create_allowance_modal',
                        'deductions' => '#create_deduction_modal',
                        'notes' => '#create_note_modal',
                        'timesheet' => '#timerModal',
                        'taxes' => '#create_tax_modal',
                        'units' => '#create_unit_modal',
                        'items' => '#create_item_modal',
                        'expense-types' => '#create_expense_type_modal',
                        'expenses' => '#create_expense_modal',
                        'payments' => '#create_payment_modal',
                        'priorities' => '#create_priority_modal',
                        'projects' => '#create_project_offcanvas',
                        'tasks' => '#create_task_offcanvas',
                        'workspaces' => '#createWorkspaceModal',
                        'meetings' => '#createMeetingModal',
                        'announcements' => '#create_announcement_modal',
                        'task-lists' => '#create_task_list_modal',
                        'email-templates' => '#createTemplateModal',
                        'custom-fields' => '#add_field_modal',
                        'candidates' => '#candidateModal',
                        'candidate-status' => '#createStatusModal',
                        'interviews' => '#createInterviewModal'
                    ];
                    $javascriptVoidTypes = array_merge(array_keys($modalTargets), [
                        'todos',
                        'tags',
                        'status',
                        'leave-requests',
                        'contract-types',
                        'payment-methods',
                        'allowances',
                        'deductions',
                        'notes',
                        'timesheet',
                        'taxes',
                        'units',
                        'items',
                        'expense-types',
                        'expenses',
                        'payments',
                        'projects',
                        'tasks',
                        'workspaces',
                        'meetings',
                        'task-lists',
                        'announcements',
                        'email-templates',
                        'custom-fields',
                        'candidates',
                        'interviews'
                    ]);
                @endphp


                <a class="btn btn-primary m-1"
                @if ($typeSlug === 'projects')
                    href="javascript:void(0)"
                    data-bs-toggle="offcanvas"
                    data-bs-target="#create_project_offcanvas"
                @elseif ($typeSlug === 'tasks')
                    href="javascript:void(0)"
                    data-bs-toggle="offcanvas"
                    data-bs-target="#create_task_offcanvas"
                @else
                    href="{{ in_array($typeSlug, $javascriptVoidTypes) ? 'javascript:void(0)' : '/' . $prefix . (isset($link) && !empty($link) ? '/' . $link : '/' . $typeSlug . '/create') }}"
                    @if (isset($modalTargets[$typeSlug])) data-bs-toggle="modal"
                    data-bs-target="{{ $modalTargets[$typeSlug] }}" @endif
                @endif>
                    {{ get_label('create_now', 'Create now') }}
                </a>
            @endif



            <div class="mt-3">
                <img src="{{ asset('/storage/no-result.png') }}" alt="page-misc-error-light" width="500"
                    class="img-fluid" data-app-dark-img="illustrations/page-misc-error-dark.png"
                    data-app-light-img="illustrations/page-misc-error-light.png" />
            </div>
        </div>

    </div>
</div>