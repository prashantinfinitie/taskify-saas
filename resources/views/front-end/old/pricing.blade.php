@extends('front-end.layout')
@section('title')
    {{ get_label('pricing_plans', 'Pricing Plans') }}

@endsection
@section('content')
    <section class="section-py">
        <div class="bg-gradient-primary position-relative  border-radius-xl w-100">

            <img src="/assets/front-end/img/gallery/waves-white.svg" alt="pattern-lines"
                class="position-absolute start-0 top-md-0 w-100 opacity-6">
            <div class="container pb-lg-9 pb-8  pt-7 postion-relative z-index-2">
                <div class="row mb-5">
                    <div class="col-md-8 mx-auto text-center">
                        <span class="badge bg-gradient-dark mb-2">{{ get_label('pricing', 'Pricing') }}</span>
                        <h3 class="text-white">{{ get_label('see_our_pricing', 'See our pricing') }}</h3>
                        <p class="text-white">
                            {{ get_label('seePricingDesc', 'You have Free Unlimited Updates and Premium Support on each package.') }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-n9">
            @if (count($plans) > 0)
                @php
                    // Check if any plans have non-zero prices for yearly and lifetime options
                    $hasYearlyPlans = false;
                    $hasLifetimePlans = false;

                    foreach ($plans as $plan) {
                        if ($plan->yearly_price > 0 || ($plan->plan_type === 'free' && $plan->yearly_price == 0)) {
                            $hasYearlyPlans = true;
                        }

                        if ($plan->lifetime_price > 0 || ($plan->plan_type === 'free' && $plan->lifetime_price == 0)) {
                            $hasLifetimePlans = true;
                        }
                    }

                    // Count how many pricing options are available
                    $availablePricingOptions = 1; // Monthly is always available
                    if ($hasYearlyPlans)
                        $availablePricingOptions++;
                    if ($hasLifetimePlans)
                        $availablePricingOptions++;
                @endphp

                <div class="container">
                    @if ($availablePricingOptions > 1)
                        <div class="row">
                            <div class="col-md-4 col-12 mx-auto text-center">
                                <div class="nav-wrapper">
                                    <ul class="nav nav-pills nav-fill flex-row p-1 position-relative" id="tabs-pricing-4"
                                        role="tablist">
                                        <li class="nav-item">
                                            <a class="nav-link mb-0 active" id="tabs-iconpricing-tab-1" data-bs-toggle="tab"
                                                href="#monthly" role="tab" aria-controls="monthly" aria-selected="true">
                                                {{ get_label('monthly', 'Monthly') }}
                                            </a>
                                        </li>
                                        @if ($hasYearlyPlans)
                                            <li class="nav-item">
                                                <a class="nav-link mb-0" id="tabs-iconpricing-tab-2" data-bs-toggle="tab" href="#yearly"
                                                    role="tab" aria-controls="yearly" aria-selected="false">
                                                    {{ get_label('annual', 'Annual') }}
                                                </a>
                                            </li>
                                        @endif
                                        @if ($hasLifetimePlans)
                                            <li class="nav-item">
                                                <a class="nav-link mb-0" id="tabs-iconpricing-tab-3" data-bs-toggle="tab"
                                                    href="#lifetime" role="tab" aria-controls="lifetime" aria-selected="false">
                                                    {{ get_label('lifetime', 'Lifetime') }}
                                                </a>
                                            </li>
                                        @endif
                                    </ul>
                                </div>
                            </div>
                        </div>
                    @endif
                    <div class="tab-content tab-space">
                        <div class="tab-pane active" id="monthly">
                            <div class="row mt-5">
                                @foreach ($plans as $plan)
                                    <div class="col-md-4 col-lg-4 col-sm-6 mb-lg-0 mb-4 gap-2 pb-3">
                                        <div class="card shadow-lg">
                                            <div class="card-header text-sm-start text-center pt-4 pb-3 px-4">
                                                <h5 class="mb-1">{{ $plan->name }}</h5>
                                                <h3 class="font-weight-bolder mt-3">
                                                    <small class="text-secondary font-weight-bold">
                                                        @if($plan->monthly_discounted_price > 0 && $plan->monthly_discounted_price < $plan->monthly_price)
                                                                <strike>{{ format_currency($plan->monthly_price) }}</strike></small>
                                                            {{ format_currency($plan->monthly_discounted_price) }} <small
                                                                class="text-sm text-secondary font-weight-bold">/
                                                                {{get_label('monthly_price', 'Monthly Price')}}</small>
                                                        @else
                                                        {{ format_currency($plan->monthly_price) }} <small
                                                            class="text-sm text-secondary font-weight-bold">/
                                                            {{get_label('monthly_price', 'Monthly Price')}}
                                                        </small>
                                                    @endif
                                                    </small>
                                                </h3>
                                                <p class="text-lighter small text-black-50 ">{{$plan->description}}</p>
                                                @if($plan->monthly_price == 0)
                                                    <a href="{{route('login')}}"
                                                        class="btn btn-outline-primary btn-sm w-100 text-center border-radius-md mt-4 mb-2">{{get_label('buy_now', 'Buy now')}}</a>
                                                @else
                                                    <a href="{{route('login')}}"
                                                        class="btn btn-sm bg-gradient-dark w-100 text-center border-radius-md mt-4 mb-2">{{get_label('buy_now', 'Buy now')}}</a>
                                                @endif
                                            </div>

                                            <hr class="horizontal dark my-0">
                                            <div class="card-body pt-0">
                                                <div class="justify-content-start d-flex px-2 py-1">
                                                    <ul class="list-unstyled mb-4">
                                                        <li class="mb-2">
                                                            <i class="fas fa-check-circle me-2 text-primary text-gradient"></i>
                                                            <span
                                                                class="fw-semibold">{{ get_label('max_projects', 'Max Projects') }}:</span>
                                                            {!! $plan->max_projects == -1 ? '<span class="fw-semibold">Unlimited</span>' : '<span class="fw-semibold">' . $plan->max_projects . '</span>' !!}
                                                        </li>
                                                        <li class="mb-2">
                                                            <i class="fas fa-check-circle me-2 text-primary text-gradient"></i>
                                                            <span
                                                                class="fw-semibold">{{ get_label('max_clients', 'Max Clients') }}:</span>
                                                            {!! $plan->max_clients == -1 ? '<span class="fw-semibold">Unlimited</span>' : '<span class="fw-semibold">' . $plan->max_clients . '</span>' !!}
                                                        </li>
                                                        <li class="mb-2">
                                                            <i class="fas fa-check-circle me-2 text-primary text-gradient"></i>
                                                            <span
                                                                class="fw-semibold">{{ get_label('max_team_members', 'Max Team Members') }}:</span>
                                                            {!! $plan->max_team_members == -1 ? '<span class="fw-semibold">Unlimited</span>' : '<span class="fw-semibold">' . $plan->max_team_members . '</span>' !!}
                                                        </li>
                                                        <li class="mb-2">
                                                            <i class="fas fa-check-circle me-2 text-primary text-gradient"></i>
                                                            <span
                                                                class="fw-semibold">{{ get_label('max_workspaces', 'Max Workspaces') }}:</span>
                                                            {!! $plan->max_worksapces == -1 ? '<span class="fw-semibold">Unlimited</span>' : '<span class="fw-semibold">' . $plan->max_worksapces . '</span>' !!}
                                                        </li>
                                                        @if ($plan->modules)
                                                            <li class="mb-2">
                                                                <i class="fas fa-check-circle me-2 text-primary text-gradient"></i>
                                                                <span class="fw-semibold">{{ get_label('modules', 'Modules') }}</span>
                                                                <ul class="list-unstyled m-3 my-2 ps-0 text-smallcaps">
                                                                    @php
                                                                        $modules = json_decode($plan->modules);
                                                                        $checkedModules = [];
                                                                        $uncheckedModules = [];

                                                                        foreach (config('taskify.modules') as $moduleName => $moduleData) {
                                                                            $included = in_array($moduleName, $modules);
                                                                            if ($included) {
                                                                                $checkedModules[] = [
                                                                                    'name' => $moduleName,
                                                                                    'icon' => $moduleData['icon'],
                                                                                ];
                                                                            } else {
                                                                                $uncheckedModules[] = [
                                                                                    'name' => $moduleName,
                                                                                    'icon' => $moduleData['icon'],
                                                                                ];
                                                                            }
                                                                        }

                                                                        $sortedModules = array_merge($checkedModules, $uncheckedModules);
                                                                    @endphp
                                                                    @foreach ($sortedModules as $module)
                                                                        @php
                                                                            $iconClass = in_array($module['name'], $modules) ? 'fas fa-check-circle text-success' : 'fas fa-times-circle text-danger';
                                                                        @endphp
                                                                        <li class="mb-2 text-dark">
                                                                            <i class="{{ $iconClass }} me-2"></i>
                                                                            <i class="{{ $module['icon'] }}"></i>
                                                                            {{ ucfirst($module['name']) }}
                                                                        </li>
                                                                    @endforeach
                                                                </ul>
                                                            </li>
                                                        @endif
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        @if ($hasYearlyPlans)
                            <div class="tab-pane" id="yearly">
                                <div class="row mt-5">
                                    @foreach ($plans as $plan)
                                        @if ($plan->yearly_price > 0 || ($plan->plan_type === 'free' && $plan->yearly_price == 0))
                                            <div class="col-md-4 col-lg-4 col-sm-6 mb-lg-0 mb-4 gap-2 pb-3">
                                                <div class="card shadow-lg">
                                                    <div class="card-header text-sm-start text-center pt-4 pb-3 px-4">
                                                        <h5 class="mb-1">{{ $plan->name }}</h5>
                                                        <h3 class="font-weight-bolder mt-3">
                                                            <small class="text-secondary font-weight-bold">
                                                                @if($plan->yearly_discounted_price > 0 && $plan->yearly_discounted_price < $plan->yearly_price)
                                                                        <strike>{{ format_currency($plan->yearly_price) }}</strike></small>
                                                                    {{ format_currency($plan->yearly_discounted_price) }} <small
                                                                        class="text-sm text-secondary font-weight-bold">/
                                                                        {{get_label('yearly_price', 'Yearly Price')}}</small>
                                                                @else
                                                                {{ format_currency($plan->yearly_price) }} <small
                                                                    class="text-sm text-secondary font-weight-bold">/
                                                                    {{get_label('yearly_price', 'Yearly Price')}}
                                                                </small>
                                                            @endif
                                                            </small>
                                                        </h3>
                                                        <p class="text-lighter small text-black-50 ">{{$plan->description}}</p>
                                                        @if($plan->yearly_price == 0)
                                                            <a href="{{route('login')}}"
                                                                class="btn btn-outline-primary btn-sm w-100 text-center border-radius-md mt-4 mb-2">{{get_label('buy_now', 'Buy now')}}</a>
                                                        @else
                                                            <a href="{{route('login')}}"
                                                                class="btn btn-sm bg-gradient-dark w-100 text-center border-radius-md mt-4 mb-2">{{get_label('buy_now', 'Buy now')}}</a>
                                                        @endif
                                                    </div>
                                                    <hr class="horizontal dark my-0">
                                                    <div class="card-body pt-0">
                                                        <div class="justify-content-start d-flex px-2 py-1">
                                                            <ul class="list-unstyled mb-4">
                                                                <li class="mb-2">
                                                                    <i class="fas fa-check-circle me-2 text-primary text-gradient"></i>
                                                                    <span
                                                                        class="fw-semibold">{{ get_label('max_projects', 'Max Projects') }}:</span>
                                                                    {!! $plan->max_projects == -1 ? '<span class="fw-semibold">Unlimited</span>' : '<span class="fw-semibold">' . $plan->max_projects . '</span>' !!}
                                                                </li>
                                                                <li class="mb-2">
                                                                    <i class="fas fa-check-circle me-2 text-primary text-gradient"></i>
                                                                    <span
                                                                        class="fw-semibold">{{ get_label('max_clients', 'Max Clients') }}:</span>
                                                                    {!! $plan->max_clients == -1 ? '<span class="fw-semibold">Unlimited</span>' : '<span class="fw-semibold">' . $plan->max_clients . '</span>' !!}
                                                                </li>
                                                                <li class="mb-2">
                                                                    <i class="fas fa-check-circle me-2 text-primary text-gradient"></i>
                                                                    <span
                                                                        class="fw-semibold">{{ get_label('max_team_members', 'Max Team Members') }}:</span>
                                                                    {!! $plan->max_team_members == -1 ? '<span class="fw-semibold">Unlimited</span>' : '<span class="fw-semibold">' . $plan->max_team_members . '</span>' !!}
                                                                </li>
                                                                <li class="mb-2">
                                                                    <i class="fas fa-check-circle me-2 text-primary text-gradient"></i>
                                                                    <span
                                                                        class="fw-semibold">{{ get_label('max_workspaces', 'Max Workspaces') }}:</span>
                                                                    {!! $plan->max_worksapces == -1 ? '<span class="fw-semibold">Unlimited</span>' : '<span class="fw-semibold">' . $plan->max_worksapces . '</span>' !!}
                                                                </li>
                                                                @if ($plan->modules)
                                                                    <li class="mb-2">
                                                                        <i class="fas fa-check-circle me-2 text-primary text-gradient"></i>
                                                                        <span class="fw-semibold">{{ get_label('modules', 'Modules') }}</span>
                                                                        <ul class="list-unstyled m-3 my-2 ps-0 text-smallcaps">
                                                                            @php
                                                                                $modules = json_decode($plan->modules);
                                                                                $checkedModules = [];
                                                                                $uncheckedModules = [];
                                                                                foreach (config('taskify.modules') as $moduleName => $moduleData) {
                                                                                    $included = in_array($moduleName, $modules);
                                                                                    if ($included) {
                                                                                        $checkedModules[] = [
                                                                                            'name' => $moduleName,
                                                                                            'icon' => $moduleData['icon'],
                                                                                        ];
                                                                                    } else {
                                                                                        $uncheckedModules[] = [
                                                                                            'name' => $moduleName,
                                                                                            'icon' => $moduleData['icon'],
                                                                                        ];
                                                                                    }
                                                                                }
                                                                                $sortedModules = array_merge($checkedModules, $uncheckedModules);
                                                                            @endphp
                                                                            @foreach ($sortedModules as $module)
                                                                                @php
                                                                                    $iconClass = in_array($module['name'], $modules) ? 'fas fa-check-circle text-success' : 'fas fa-times-circle text-danger';
                                                                                @endphp
                                                                                <li class="mb-2 text-dark">
                                                                                    <i class="{{ $iconClass }} me-2"></i>
                                                                                    <i class="{{ $module['icon'] }}"></i>
                                                                                    {{ ucfirst($module['name']) }}
                                                                                </li>
                                                                            @endforeach
                                                                        </ul>
                                                                    </li>
                                                                @endif
                                                            </ul>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        @if ($hasLifetimePlans)
                            <div class="tab-pane" id="lifetime">
                                <div class="row mt-5">
                                    @foreach ($plans as $plan)
                                        @if ($plan->lifetime_price > 0 || ($plan->plan_type === 'free' && $plan->lifetime_price == 0))
                                            <div class="col-md-4 col-lg-4 col-sm-6 mb-lg-0 mb-4 gap-2 pb-3">
                                                <div class="card shadow-lg">
                                                    <div class="card-header text-sm-start text-center pt-4 pb-3 px-4">
                                                        <h5 class="mb-1">{{ $plan->name }}</h5>
                                                        <h3 class="font-weight-bolder mt-3">
                                                            <small class="text-secondary font-weight-bold">
                                                                @if($plan->lifetime_discounted_price > 0 && $plan->lifetime_discounted_price < $plan->lifetime_price)
                                                                        <strike>{{ format_currency($plan->lifetime_price) }}</strike></small>
                                                                    {{ format_currency($plan->lifetime_discounted_price) }} <small
                                                                        class="text-sm text-secondary font-weight-bold">/
                                                                        {{get_label('lifetime_price', 'Lifetime Price')}}</small>
                                                                @else
                                                                {{ format_currency($plan->lifetime_price) }} <small
                                                                    class="text-sm text-secondary font-weight-bold">/
                                                                    {{get_label('lifetime_price', 'Lifetime Price')}}
                                                                </small>
                                                            @endif
                                                            </small>
                                                        </h3>
                                                        <p class="text-lighter small text-black-50 ">{{$plan->description}}</p>
                                                        @if($plan->lifetime_price == 0)
                                                            <a href="{{route('login')}}"
                                                                class="btn btn-outline-primary btn-sm w-100 text-center border-radius-md mt-4 mb-2">{{get_label('buy_now', 'Buy now')}}</a>
                                                        @else
                                                            <a href="{{route('login')}}"
                                                                class="btn btn-sm bg-gradient-dark w-100 text-center border-radius-md mt-4 mb-2">{{get_label('buy_now', 'Buy now')}}</a>
                                                        @endif
                                                    </div>
                                                    <hr class="horizontal dark my-0">
                                                    <div class="card-body pt-0">
                                                        <div class="justify-content-start d-flex px-2 py-1">
                                                            <ul class="list-unstyled mb-4">
                                                                <li class="mb-2">
                                                                    <i class="fas fa-check-circle me-2 text-primary text-gradient"></i>
                                                                    <span
                                                                        class="fw-semibold">{{ get_label('max_projects', 'Max Projects') }}:</span>
                                                                    {!! $plan->max_projects == -1 ? '<span class="fw-semibold">Unlimited</span>' : '<span class="fw-semibold">' . $plan->max_projects . '</span>' !!}
                                                                </li>
                                                                <li class="mb-2">
                                                                    <i class="fas fa-check-circle me-2 text-primary text-gradient"></i>
                                                                    <span
                                                                        class="fw-semibold">{{ get_label('max_clients', 'Max Clients') }}:</span>
                                                                    {!! $plan->max_clients == -1 ? '<span class="fw-semibold">Unlimited</span>' : '<span class="fw-semibold">' . $plan->max_clients . '</span>' !!}
                                                                </li>
                                                                <li class="mb-2">
                                                                    <i class="fas fa-check-circle me-2 text-primary text-gradient"></i>
                                                                    <span
                                                                        class="fw-semibold">{{ get_label('max_team_members', 'Max Team Members') }}:</span>
                                                                    {!! $plan->max_team_members == -1 ? '<span class="fw-semibold">Unlimited</span>' : '<span class="fw-semibold">' . $plan->max_team_members . '</span>' !!}
                                                                </li>
                                                                <li class="mb-2">
                                                                    <i class="fas fa-check-circle me-2 text-primary text-gradient"></i>
                                                                    <span
                                                                        class="fw-semibold">{{ get_label('max_workspaces', 'Max Workspaces') }}:</span>
                                                                    {!! $plan->max_worksapces == -1 ? '<span class="fw-semibold">Unlimited</span>' : '<span class="fw-semibold">' . $plan->max_worksapces . '</span>' !!}
                                                                </li>
                                                                @if ($plan->modules)
                                                                    <li class="mb-2">
                                                                        <i class="fas fa-check-circle me-2 text-primary text-gradient"></i>
                                                                        <span class="fw-semibold">{{ get_label('modules', 'Modules') }}</span>
                                                                        <ul class="list-unstyled m-3 my-2 ps-0 text-smallcaps">
                                                                            @php
                                                                                $modules = json_decode($plan->modules);
                                                                                $checkedModules = [];
                                                                                $uncheckedModules = [];
                                                                                foreach (config('taskify.modules') as $moduleName => $moduleData) {
                                                                                    $included = in_array($moduleName, $modules);
                                                                                    if ($included) {
                                                                                        $checkedModules[] = [
                                                                                            'name' => $moduleName,
                                                                                            'icon' => $moduleData['icon'],
                                                                                        ];
                                                                                    } else {
                                                                                        $uncheckedModules[] = [
                                                                                            'name' => $moduleName,
                                                                                            'icon' => $moduleData['icon'],
                                                                                        ];
                                                                                    }
                                                                                }
                                                                                $sortedModules = array_merge($checkedModules, $uncheckedModules);
                                                                            @endphp
                                                                            @foreach ($sortedModules as $module)
                                                                                @php
                                                                                    $iconClass = in_array($module['name'], $modules) ? 'fas fa-check-circle text-success' : 'fas fa-times-circle text-danger';
                                                                                @endphp
                                                                                <li class="mb-2 text-dark">
                                                                                    <i class="{{ $iconClass }} me-2"></i>
                                                                                    <i class="{{ $module['icon'] }}"></i>
                                                                                    {{ ucfirst($module['name']) }}
                                                                                </li>
                                                                            @endforeach
                                                                        </ul>
                                                                    </li>
                                                                @endif
                                                            </ul>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            @else
                <div class="container mb-8 mt-8 py-3">
                    <div class="alert bg-gray-300 h4 text-center">
                        <i class="fas fa-exclamation-circle"></i> <span class=" text-black">
                            {{ get_label('no_plans_available', 'No Plans Available') }}</span>

                    </div>
                </div>
            @endif
    </section>

@endsection