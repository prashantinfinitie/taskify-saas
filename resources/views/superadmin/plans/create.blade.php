@extends('layout')

@section('title')
    <?= get_label('create_plan', 'Create Plan') ?>
@endsection

@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between mt-4">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-style1">
                        <li class="breadcrumb-item">
                            <a href="{{ route('superadmin.panel') }}"><?= get_label('home', 'Home') ?></a>
                        </li>
                        <li class="breadcrumb-item">
                            <a href="{{ route('plans.index') }}"><?= get_label('plans', 'Plans') ?></a>
                        </li>

                        <li class="breadcrumb-item active">
                            <?= get_label('create_plan', 'Create Plan') ?>
                        </li>

                    </ol>
                </nav>
            </div>
            <div>
                <a href="{{ route('plans.index') }}"><button type="button" class="btn btn-sm btn-primary"
                        data-bs-toggle="tooltip" data-bs-placement="left"
                        data-bs-original-title="<?= get_label('plans', 'Plans') ?>"><i
                            class='bx bx-list-ul'></i></button></a>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <form method="POST" action="{{ route('plans.store') }}" id="plan-create-form"
                    enctype="multipart/form-data">
                    @csrf
                    <div class="col-md-12">
                        <h2 class="mb-4">{{ get_label('create_new_plan', 'Create a New Plan') }}</h2>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="planName" class="form-label bold"><?= get_label('name', 'Name:') ?><span
                                        class="asterisk">*</span></label>
                                <input type="text" class="form-control" id="planName"
                                    placeholder="Enter a descriptive name" required>
                            </div>
                            <div class="col-md-6">
                                <label for="planDescription"
                                    class="form-label bold"><?= get_label('description', 'Description:') ?><span
                                        class="asterisk">*</span></label>
                                <textarea class="form-control" id="planDescription" rows="3" placeholder="Provide a clear and concise overview"
                                    required></textarea>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-12">
                                <div class="alert alert-primary alert-dismissible" role="alert">
                                    For unlimited, use the value -1!
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close">
                                    </button>
                                </div>
                            </div>


                            <div class="col-md-3">
                                <label for="maxProjects"
                                    class="form-label bold"><?= get_label('max_projects', 'Maximum Projects:') ?><span
                                        class="asterisk">*</span></label>
                                <input type="number" class="form-control" id="maxProjects" min="-1"
                                    placeholder="Enter a number" required>
                            </div>
                            <div class="col-md-3">
                                <label for="maxClients"
                                    class="form-label bold"><?= get_label('max_clients', 'Maximum Clients:') ?><span
                                        class="asterisk">*</span></label>
                                <input type="number" class="form-control" id="maxClients" min="-1"
                                    placeholder="Enter a number" required>
                            </div>
                            <div class="col-md-3">
                                <label for="maxTeamMembers"
                                    class="form-label bold"><?= get_label('max_team_members', 'Maximum Team Members:') ?><span
                                        class="asterisk">*</span></label>
                                <input type="number" class="form-control" id="maxTeamMembers" min="-1"
                                    placeholder="Enter a number" required>
                            </div>
                            <div class="col-md-3">
                                <label for="maxWorkshops"
                                    class="form-label bold"><?= get_label('max_workspaces', 'Maximum Workspaces:') ?><span
                                        class="asterisk">*</span></label>
                                <input type="number" class="form-control" id="maxWorkshops" min="-1"
                                    placeholder="Enter a number" required>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <label class="form-label"><?= get_label('plan_tenure', 'Plan Tenure:') ?></label>
                                <div class="alert alert-primary alert-dismissible">
                                    <i class="bx bx-info-circle"></i>
                                    <?= get_label('tenure_note', 'If it is a paid plan, the Monthly plan is required, while Yearly and Lifetime plans are optional.') ?>
                                </div>
                            </div>

                            <!-- Switch Button for All Tenures -->
                            <div class="col-md-12 mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input tenure-switch" type="checkbox" id="allTenuresSwitch"
                                        checked>
                                    <label class="form-check-label bold"
                                        for="allTenuresSwitch"><?= get_label('paid', 'Paid') ?></label>
                                    <small id="freePlanText"
                                        class="form-text text-dark"><?= get_label('if_you_want_to_make_this_plan_free_turn_this_off', 'If you want to make this plan free turn this off') ?>
                                    </small>
                                    <input type="hidden" name="plan_type" id='plan_type' value="paid">
                                </div>
                            </div>

                            <!-- Monthly Tenure - Required -->
                            <div class="col-md-4 monthly_tenure">
                                <div class="card">
                                    <div class="card-body">
                                        <h5 class="card-title"><?= get_label('monthly', 'Monthly') ?> <span
                                                class="asterisk">*</span></h5>
                                        <div class="mb-3">
                                            <label for="monthlyPrice"
                                                class="form-label"><?= get_label('price', 'Price:') ?></label>
                                            <input type="number" class="form-control tenure-price min_0"
                                                id="monthly_price" min="0" required>
                                        </div>
                                        <div class="mb-3">
                                            <div class="col">
                                                <label for="monthlyDiscountedPrice"
                                                    class="form-label"><?= get_label('discounted_price', 'Discounted Price:') ?></label>
                                                <input type="number" class="form-control tenure-discounted-price min_0"
                                                    id="monthly_discounted_price" min="0">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Yearly Tenure - Optional -->
                            <div class="col-md-4 yearly_tenure">
                                <div class="card">
                                    <div class="card-body">
                                        <h5 class="card-title"><?= get_label('yearly', 'Yearly') ?> <small
                                                class="text-muted ms-2">({{ get_label('optional', 'Optional') }})</small>
                                        </h5>
                                        <div class="mb-3">
                                            <div class="col">
                                                <label for="yearlyPrice"
                                                    class="form-label"><?= get_label('price', 'Price:') ?></label>
                                                <input type="number" class="form-control tenure-price min_0"
                                                    id="yearly_price" min="0">
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <label for="yearlyDiscountedPrice"
                                                class="form-label"><?= get_label('discounted_price', 'Discounted Price:') ?></label>
                                            <input type="number" class="form-control tenure-discounted-price min_0"
                                                id="yearly_discounted_price" min="0">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Lifetime Tenure - Optional -->
                            <div class="col-md-4 lifetime_tenure">
                                <div class="card">
                                    <div class="card-body">
                                        <h5 class="card-title"><?= get_label('lifetime', 'Lifetime') ?> <small
                                                class="text-muted ms-2">({{ get_label('optional', 'Optional') }})</small>
                                        </h5>
                                        <div class="mb-3">
                                            <div class="col">
                                                <label for="lifetimePrice"
                                                    class="form-label"><?= get_label('price', 'Price:') ?></label>
                                                <input type="number" class="form-control tenure-price min_0"
                                                    id="lifetime_price" min="0">
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <label for="lifetimeDiscountedPrice"
                                                class="form-label"><?= get_label('discounted_price', 'Discounted Price:') ?></label>
                                            <input type="number" class="form-control tenure-discounted-price min_0"
                                                id="lifetime_discounted_price" min="0">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>


                        <div class="row mb-3">
                            <div class="col-md-12">
                                <label for="modules"
                                    class="form-label"><?= get_label('module_selection', 'Module Selection:') ?></label>
                                <span class="asterisk">*</span>
                            </div>
                            <div class="col-md-12">
                                <!-- Select All Checkbox -->
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="checkbox" id="select-all-checkbox"
                                        value="">
                                    <label class="form-check-label bold"
                                        for="select-all-checkbox"><?= get_label('select_all', 'Select All') ?></label>
                                </div>
                                <!-- Module Checkboxes -->
                                <div class="row" id="moduleCheckboxes">

                                    <?php foreach (config('taskify.modules') as $module => $data) : ?>

                                    <div class="col-md-4 mb-3 mt-3">
                                        <div class="card mb-3">
                                            <h5
                                                class="card-header border-secondary-subtle rounded-2 h-100 alert alert-dark mb-0 border bg-transparent p-3">
                                                <i class="<?= $data['icon'] ?>"></i>
                                                <?= get_label($module, ucfirst($module)) ?>
                                            </h5>



                                            <div class="card-body">

                                                <p class="card-text">
                                                    <?= get_label(strtolower(str_replace('-', '_', str_replace(' ', '_', $data['description']))), $data['description']) ?>
                                                </p>


                                                <div class="form-check form-switch">
                                                    <input class="form-check-input module-checkbox" type="checkbox"
                                                        id="module<?= ucfirst($module) ?>" value="<?= $module ?>"
                                                        required>
                                                    <label class="form-check-label bold"
                                                        for="module<?= ucfirst($module) ?>">Enabled</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-12">
                                        <label class="form-label mb-2 me-4">{{ get_label('status', 'Status') }}</label>
                                        <div class="btn-group" role="group"
                                            aria-label="Basic radio toggle button group">
                                            <input type="radio" class="btn-check" name="status" id="active"
                                                value="active" checked>
                                            <label class="btn btn-outline-primary"
                                                for="active">{{ get_label('active', 'Active') }}</label>

                                            <input type="radio" class="btn-check" name="status" id="inactive"
                                                value="inactive">
                                            <label class="btn btn-outline-primary"
                                                for="inactive">{{ get_label('inactive', 'Inactive') }}</label>
                                        </div>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-12">
                                        <label for="planImage" class="form-label bold">
                                            {{ get_label('plan_image', 'Plan Image') }}<span class="asterisk">*</span>
                                        </label>
                                        <input type="file" class="form-control mt-2" id="planImage" name="plan_image"
                                            accept="image/*" required>
                                    </div>
                                </div>


                                <button type="submit" class="btn btn-primary mb-3 mt-3"
                                    id="createPlanButton"><?= get_label('create_plan_button', 'Create Plan') ?></button>
                            </div>
                </form>
            </div>
        </div>

    </div>
    <script src="{{ asset('assets/js/pages/plans.js') }}"></script>
@endsection
