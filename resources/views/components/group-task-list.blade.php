
  @foreach ($taskLists as $taskList)
      <!-- Task List Header -->
      <div class="task-list-header bg-light border-bottom">
          <div class="d-flex justify-content-between align-items-center p-3">
              <div class="d-flex align-items-center gap-2">
                  <button class="btn btn-icon btn-sm btn-ghost-secondary toggle-list" data-list-id="{{ $taskList->id }}">
                      <i class="bx bx-chevron-down fs-4"></i>
                  </button>
                  <h6 class="mb-0">{{ $taskList->name }}</h6>
                  <span class="badge bg-label-primary rounded-pill">{{ count($taskList->tasks) }}</span>
              </div>
          </div>
      </div>

      <!-- Task Table -->
      <div class="task-group card-body" data-list-id="{{ $taskList->id }}">
          <div class="table-responsive">
              <table class="table-bordered table-hover table-striped table">
                  <thead>
                      <tr>
                          <th>{{ get_label('id', 'ID') }}</th>
                          <th>{{ get_label('title', 'Title') }}</th>
                          <th>{{ get_label('description', 'Description') }}</th>
                          <th>{{ get_label('project', 'Project') }}</th>
                          <th>{{ get_label('status', 'Status') }}</th>
                          <th>{{ get_label('priority', 'Priority') }}</th>
                          <th>{{ get_label('users', 'Users') }}</th>
                          <th>{{ get_label('clients', 'Clients') }}</th>
                          <th>{{ get_label('start_date', 'Start Date') }}</th>
                          <th>{{ get_label('due_date', 'Due Date') }}</th>
                          <th>{{ get_label('actions', 'Actions') }}</th>

                      </tr>
                  </thead>
                  <tbody>
                      @if (count($taskList->tasks) == 0)
                          <tr>
                              <td colspan="11" class="text-center">{{ get_label('no_data_found', 'No data found') }}
                              </td>
                          </tr>
                      @endif
                      @foreach ($taskList->tasks as $task)
                          <tr>
                              <td>{{ $task->id }}</td>
                              <td><a href="{{route('tasks.info', ['id' => $task->id])}}" > {{ $task->title }} </a></td>
                              <td>{!! $task->description ? Str::limit($task->description, 50) : '-' !!}</td>
                              <td>{{ $task->project->title }}</td>
                              <td><span class="badge bg-{{ $task->status->color }}">{{ $task->status->title }}</span>
                              </td>
                              <td>
                                  <span class="badge bg-{{ $task->priority ? $task->priority->color : '' }}">
                                      {{ $task->priority ? $task->priority->title : '-' }}
                                  </span>
                              </td>
                              <td>
                                  <ul class="list-unstyled users-list avatar-group d-flex align-items-center m-0">
                                      @foreach ($task->users as $user)
                                          <li class="avatar avatar-sm pull-up">
                                              <a href="{{ route('users.show', ['id' => $user->id]) }}" target="_blank"
                                                  title="{{ $user->first_name }} {{ $user->last_name }}">
                                                  <img src="{{ $user->photo ? asset('storage/' . $user->photo) : asset('storage/photos/no-image.jpg') }}"
                                                      alt="Avatar" class="rounded-circle" />
                                              </a>
                                          </li>
                                      @endforeach
                                  </ul>
                              </td>
                              <td>
                                  <ul class="list-unstyled users-list avatar-group d-flex align-items-center m-0">
                                      @foreach ($task->project->clients as $client)
                                          <li class="avatar avatar-sm pull-up">
                                              <a href="{{ route('clients.profile', ['id' => $client->id]) }}"
                                                  target="_blank"
                                                  title="{{ $client->first_name }} {{ $client->last_name }}">
                                                  <img src="{{ $client->photo ? asset('storage/' . $client->photo) : asset('storage/photos/no-image.jpg') }}"
                                                      alt="Avatar" class="rounded-circle" />
                                              </a>
                                          </li>
                                      @endforeach
                                  </ul>
                              </td>
                              <td>{{ format_date($task->start_date) }}</td>
                              <td>{{ format_date($task->due_date) }}</td>
                              <td>
                                <div class="align-items-center d-flex justify-content-center">
                                  @php
                                      $canCreate = checkPermission('create_tasks');
                                      $canEdit = checkPermission('edit_tasks');
                                      $canDelete = checkPermission('delete_tasks');
                                      $actions = '';
                                      if ($canEdit) {
                                          $actions .=
                                              '<a href="javascript:void(0);" class="edit-task"
                                      data-id="' .
                                              $task->id .
                                              '" title="' .
                                              get_label('update', 'Update') .
                                              '">' .
                                              '<i class="bx bx-edit mx-1"></i>' .
                                              '</a>';
                                      }
                                      if ($canDelete) {
                                          $actions .=
                                              '<button title="' .
                                              get_label('delete', 'Delete') .
                                              '" type="button"
                                      class="btn delete" data-id="' .
                                              $task->id .
                                              '" data-type="tasks"
                                      data-table="task_table">' .
                                              '<i class="bx bx-trash text-danger mx-1"></i>' .
                                              '</button>';
                                      }
                                      if ($canCreate) {
                                          $actions .=
                                              '<a href="javascript:void(0);" class="duplicate"
                                      data-id="' .
                                              $task->id .
                                              '" data-title="' .
                                              $task->title .
                                              '" data-type="tasks"
                                      data-reload="true" title="' .
                                              get_label('duplicate', 'Duplicate') .
                                              '">' .
                                              '<i class="bx bx-copy text-warning mx-2"></i>' .
                                              '</a>';
                                      }
                                      $actions .=
                                          '<a href="javascript:void(0);" class="quick-view"
                                      data-id="' .
                                          $task->id .
                                          '"
                                      title="' .
                                          get_label('quick_view', 'Quick View') .
                                          '">' .
                                          '<i class="bx bx-info-circle mx-3"></i>' .
                                          '</a>';
                                      $actions = $actions ?: '-';
                                      echo $actions;
                                  @endphp
                                </div>
                              </td>
                          </tr>
                      @endforeach
                  </tbody>
              </table>
          </div>
      </div>
  @endforeach
