@extends('layout')

@section('title')
    <?= get_label('edit_plan', 'Edit Plan') ?>
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
                            <?= get_label('edit_plan', 'Edit Plan') ?>
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
                <form method="POST" action="{{ route('plans.update', $plan->id) }}" id="plan-update-form"
                    enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <div class="col-md-12">
                        <h2 class="mb-4"><?= get_label('edit_plan', 'Edit Plan') ?></h2>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="planName" class="form-label bold"><?= get_label('name', 'Name:') ?></label> <span class="asterisk">*</span>
                                <input type="text" class="form-control" id="planName"
                                    placeholder="Enter a descriptive name" name ="name" value="{{ $plan->name }}"
                                    required>
                            </div>
                            <div class="col-md-6">
                                <label for="planDescription"
                                    class="form-label bold"><?= get_label('description', 'Description:') ?></label> <span class="asterisk">*</span>
                                <textarea class="form-control" id="planDescription" rows="3" placeholder="Provide a clear and concise overview"
                                    required>{{ $plan->description }}</textarea>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-3">
                                <label for="maxProjects"
                                    class="form-label bold"><?= get_label('max_projects', 'Maximum Projects:') ?></label> <span class="asterisk">*</span>
                                <input type="number" class="form-control" id="maxProjects" min="-1"
                                    placeholder="Enter a number" value="{{ $plan->max_projects }}" required>
                            </div>
                            <div class="col-md-3">
                                <label for="maxClients"
                                    class="form-label bold"><?= get_label('max_clients', 'Maximum Clients:') ?></label> <span class="asterisk">*</span>
                                <input type="number" class="form-control" id="maxClients" min="-1"
                                    placeholder="Enter a number" value="{{ $plan->max_clients }}" required>
                            </div>
                            <div class="col-md-3">
                                <label for="maxTeamMembers"
                                    class="form-label bold"><?= get_label('max_team_members', 'Maximum Team Members:') ?></label> <span class="asterisk">*</span>
                                <input type="number" class="form-control" id="maxTeamMembers" min="-1"
                                    placeholder="Enter a number" value="{{ $plan->max_team_members }}" required>
                            </div>
                            <div class="col-md-3">
                                <label for="maxWorkshops"
                                    class="form-label bold"><?= get_label('max_workspaces', 'Maximum Workspaces:') ?></label> <span class="asterisk">*</span>

                                <input type="number" class="form-control" id="maxWorkshops" min="-1"
                                    placeholder="Enter a number" value="{{ $plan->max_worksapces }}" required>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-12">
                                <label class="form-label"><?= get_label('plan_tenure', 'Plan Tenure:') ?></label>
                                <div class="alert alert-primary alert-dismissible">
                                    <i class="bx bx-info-circle"></i> <?= get_label('tenure_note', 'If it is a paid plan, the Monthly plan is required, while Yearly and Lifetime plans are optional.') ?>
                                </div>
                            </div>
                        
                            <!-- Switch Button for All Tenures -->
                            <div class="col-md-12 mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input tenure-switch" type="checkbox" id="allTenuresSwitch"
                                        {{ $plan->plan_type === 'free' ? '' : 'checked' }}>
                                    <label class="form-check-label bold"
                                        for="allTenuresSwitch"><?= get_label('paid', 'Paid') ?></label>
                                    <small id="freePlanText" class="form-text text-dark">If you want to make this plan
                                        free, turn this off. </small>
                                    <input type="hidden" name="plan_type" id='plan_type' value="{{ $plan->plan_type }}">
                                </div>
                            </div>
                            
                            <!-- Monthly Tenure - Required -->
                            <div class="col-md-4 monthly_tenure">
                                <div class="card">
                                    <div class="card-body">
                                        <h5 class="card-title"><?= get_label('monthly', 'Monthly') ?> <span class="asterisk">*</span></h5>
                                        <div class="mb-3">
                                            <label for="monthlyPrice"
                                                class="form-label"><?= get_label('price', 'Price:') ?></label>
                                            <input type="number" class="form-control tenure-price" id="monthly_price"
                                                min="0" value="{{ $plan->monthly_price }}" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="monthlyDiscountedPrice"
                                                class="form-label"><?= get_label('discounted_price', 'Discounted Price:') ?></label>
                                            <input type="number" class="form-control tenure-discounted-price"
                                                id="monthly_discounted_price" min="0"
                                                value="{{ $plan->monthly_discounted_price }}">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            {{-- @dd($plan); --}}
                           <!-- Yearly Tenure - Optional -->
<div class="col-md-4 yearly_tenure">
    <div class="card">
        <div class="card-body">
            <h5 class="card-title">
                <?= get_label('yearly', 'Yearly') ?> 
                <small class="text-muted ms-2">({{ get_label('optional','Optional') }})</small>
            </h5>
                        <div class="mb-3">
                <label for="yearlyPrice" class="form-label"><?= get_label('price', 'Price:') ?></label>
                <input type="number" class="form-control tenure-price" id="yearly_price" min="0" value="{{ $plan->yearly_price && $plan->yearly_price != '0.00' ? $plan->yearly_price : '' }}">
            </div>
            <div class="mb-3">
                <label for="yearlyDiscountedPrice" class="form-label"><?= get_label('discounted_price', 'Discounted Price:') ?></label>
                <input type="number" class="form-control tenure-discounted-price" id="yearly_discounted_price" min="0" value="{{ $plan->yearly_discounted_price && $plan->yearly_discounted_price != '0.00' ? $plan->yearly_discounted_price : '' }}">
            </div>
        </div>
    </div>
</div>

<!-- Lifetime Tenure - Optional -->
<div class="col-md-4 lifetime_tenure">
    <div class="card">
        <div class="card-body">
            <h5 class="card-title"><?= get_label('lifetime', 'Lifetime') ?> <small class="text-muted ms-2">({{ get_label('optional','Optional') }})</small></h5>
            <div class="mb-3">
                <label for="lifetimePrice" class="form-label"><?= get_label('price', 'Price:') ?></label>
                <input type="number" class="form-control tenure-price" id="lifetime_price" min="0" value="{{ $plan->lifetime_price && $plan->lifetime_price != '0.00' ? $plan->lifetime_price : '' }}">
            </div>
            <div class="mb-3">
                <label for="lifetimeDiscountedPrice" class="form-label"><?= get_label('discounted_price', 'Discounted Price:') ?></label>
                <input type="number" class="form-control tenure-discounted-price" id="lifetime_discounted_price" min="0" value="{{ $plan->lifetime_discounted_price && $plan->lifetime_discounted_price != '0.00' ? $plan->lifetime_discounted_price : '' }}">
            </div>
        </div>
    </div>
</div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-12">
                                <label for="module_selection" class="form-label bold"><?= get_label('module_selection', 'Module Selection:') ?></label> <span class="asterisk">*</span>
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
                                <div class="row " id="moduleCheckboxes">
                                    <?php foreach (config('taskify.modules') as $module => $data) : ?>

                                    <div class="col-md-4 mt-3  mb-3">
                                        <div class="card mb-3">
                                            <h5
                                                class="card-header bg-transparent border border-secondary-subtle rounded-2 p-3 h-100 alert alert-dark mb-0 ">
                                                <i class="<?= $data['icon'] ?>"></i>
                                                <?= get_label($module, ucfirst($module)) ?>
                                            </h5>
                                            <div class="card-body">
                                                <p class="card-text"><?= $data['description'] ?></p>
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input module-checkbox" type="checkbox"
                                                        id="module<?= ucfirst($module) ?>" value="<?= $module ?>"
                                                        {{ in_array($module, $plan->modules) ? 'checked' : '' }}>
                                                    <label class="form-check-label bold"
                                                        for="module<?= ucfirst($module) ?>">Enabled</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-6">
                                <label for="status" class="form-label mb-2 me-4"><?= get_label('status', 'Status:') ?></label>
                                <div class="btn-group" role="group" aria-label="Basic radio toggle button group">
                                    <input type="radio" class="btn-check status" name="status" id="active"
                                        value="active" {{ $plan->status === 'active' ? 'checked' : '' }}>
                                    <label class="btn btn-outline-primary" for="active">Active</label>
                                    <input type="radio" class="btn-check status" name="status" id="inactive"
                                        value="inactive" {{ $plan->status === 'inactive' ? 'checked' : '' }}>
                                    <label class="btn btn-outline-primary" for="inactive">Inactive</label>
                                </div>
                            </div>

                            @if ($plan->image)
                                <div class="col-6 ">
                                    <label for="plan_image"
                                        class="form-check-label bold">{{ get_label('plan_image', 'Plan Image') }}</label>
                                    <div class = "img-thumbnail">
                                        <img src = "{{ asset('/storage/' . $plan->image) }}" alt ="No Image"
                                            height = "100px">
                                    </div>
                                    <input type="file" class="form-control mt-2" id="planImage" name="plan_image"
                                        accept="image/*">
                                </div>
                            @endif

                        </div>
                        <button type="submit" class="btn btn-primary mt-3 mb-3"
                            id="updatePlanButton"><?= get_label('update_plan_button', 'Update Plan') ?></button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script src="{{ asset('assets/js/pages/plans.js') }}"></script>
@endsection
