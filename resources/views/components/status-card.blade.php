<!-- meetings -->

<div class="card ">
    <div class="card-body">
        <div class="table-responsive text-nowrap">
            {{ $slot }}
            @if (is_countable($statuses) && count($statuses) > 0)
                <input type="hidden" id="data_type" value="status">

                    <table id="table" data-toggle="table" data-loading-template="loadingTemplate"
                        data-url="{{ route('status.list') }}" data-icons-prefix="bx" data-icons="icons"
                        data-show-refresh="true" data-total-field="total" data-trim-on-search="false"
                        data-data-field="rows" data-page-list="[5, 10, 20, 50, 100, 200]" data-search="true"
                        data-side-pagination="server" data-show-columns="true" data-pagination="true"
                        data-sort-name="id" data-sort-order="desc" data-mobile-responsive="true"
                        data-query-params="queryParams" data-route-prefix="{{ Route::getCurrentRoute()->getPrefix() }}">

                        <thead>
                            <tr>
                                <th data-checkbox="true"></th>
                                <th data-sortable="true" data-field="id"><?= get_label('id', 'ID') ?></th>
                                <th data-sortable="true" data-field="title"><?= get_label('title', 'Title') ?></th>
                                <th data-sortable="true" data-field="color"><?= get_label('preview', 'Preview') ?></th>
                                 @if (isAdminOrHasAllDataAccess())
                        <th data-field="roles_has_access" data-visible="true"><?= get_label('allowed_roles', 'Allowed Roles') ?> <i class='bx bx-info-circle text-primary' title="{{get_label('roles_can_set_status_info_1', 'Including Admin and Roles with All Data Access Permission, Roles That Can Set This Status.')}}"></i></th>
                        @endif
                               <th data-sortable="true" data-field="created_at" data-visible="false"><?= get_label('created_at', 'Created at') ?></th>
                        <th data-sortable="true" data-field="updated_at" data-visible="false"><?= get_label('updated_at', 'Updated at') ?></th>
                        <th data-formatter="actionsFormatter"><?= get_label('actions', 'Actions') ?></th>
                            </tr>
                        </thead>
                    </table>

            @else
                <?php
                $type = 'Status'; ?>
                <x-empty-state-card :type="$type" />
            @endif
        </div>
    </div>
</div>

