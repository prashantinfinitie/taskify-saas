@extends('layout')

@section('title')
    <?= get_label('update_todo', 'Update todo') ?>
@endsection
@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between mt-4">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-style1">
                        <li class="breadcrumb-item">
                            <a href="{{ route('home.index') }}"><?= get_label('home', 'Home') ?></a>
                        </li>
                        <li class="breadcrumb-item">
                            <a href="{{ route('todos.index') }}"><?= get_label('todos', 'Todos') ?></a>
                        </li>
                        <li class="breadcrumb-item">
                            <?= $todo->title ?>
                        </li>
                        <li class="breadcrumb-item active">
                            <?= get_label('update', 'Update') ?>
                        </li>
                    </ol>
                </nav>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <form action="{{ route('todos.update', ['id' => $todo->id]) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="row">
                        <div class="mb-3 col-md-6">
                            <label for="title" class="form-label"><?= get_label('title', 'Title') ?> <span
                                    class="asterisk">*</span></label>
                            <input class="form-control" type="text" id="title" name="title"
                                placeholder="Enter Title" value="{{ $todo->title }}">
                            @error('title')
                                <p class="text-danger text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="mb-3 col-md-6">
                            <label class="form-label" for="priority"><?= get_label('priority', 'Priority') ?> <span
                                    class="asterisk">*</span></label>
                            <div class="input-group">
                                <select class="form-select" id="priority" name="priority">
                                    <option value="low" <?php if ($todo->priority == 'low') {
                                        echo 'selected';
                                    } ?>><?= get_label('low', 'Low') ?></option>
                                    <option value="medium" <?php if ($todo->priority == 'medium') {
                                        echo 'selected';
                                    } ?>><?= get_label('medium', 'Medium') ?></option>
                                    <option value="high" <?php if ($todo->priority == 'high') {
                                        echo 'selected';
                                    } ?>><?= get_label('high', 'High') ?></option>
                                </select>
                            </div>
                        </div>
                        @error('priority')
                            <p class="text-danger text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label"><?= get_label('description', 'Description') ?> <span
                                class="asterisk">*</span></label>
                        <textarea class="form-control" id="description" name="description" placeholder="Enter Description">{{ $todo->description }}</textarea>
                        @error('description')
                            <p class="text-danger text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="mt-2">
                        <button type="submit" class="btn btn-primary me-2"
                            id="showToastPlacement"><?= get_label('update', 'Update') ?></button>
                        <button type="reset"
                            class="btn btn-outline-secondary"><?= get_label('cancel', 'Cancel') ?></button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
