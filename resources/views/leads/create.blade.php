@extends('layout')
@section('title')
    {{ get_label('create_lead', 'Create Leads') }}
@endsection
@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between mb-2 mt-4">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-style1">
                        <li class="breadcrumb-item">
                            <a href="{{ url('/master-panel/home') }}">
                                {{ get_label('home', 'Home') }}
                            </a>
                        </li>
                        <li class="breadcrumb-item">
                            {{ get_label('leads_management', 'Leads Management') }}
                        </li>
                        <li class="breadcrumb-item">
                            <a href="{{ route('leads.index') }}">
                                {{ get_label('leads', 'Leads') }}
                            </a>
                        </li>
                        <li class="breadcrumb-item active">
                            {{ get_label('create', 'Create') }}
                        </li>
                    </ol>
                </nav>
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <form action="{{ route('leads.store') }}" method="POST" class="form-submit-event"
                    enctype="multipart/form-data">
                    <input type="hidden" name="redirect_url" value="{{ route('leads.index') }}">
                    @csrf
                    <div class="row">
                        <!-- Personal Details -->
                        <div class="col-md-12">
                            <h5>{{ get_label('personal_details', 'Personal Details') }}</h5>
                            <hr>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="first_name" class="form-label">{{ get_label('first_name', 'First Name') }} <span
                                    class="text-danger">*</span></label>
                            <input type="text" name="first_name" class="form-control" required
                                placeholder="{{ get_label('enter_first_name', 'Enter first name') }}"
                                value="{{ old('first_name') }}">
                            @error('first_name')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="last_name" class="form-label">{{ get_label('last_name', 'Last Name') }} <span
                                    class="text-danger">*</span></label>
                            <input type="text" name="last_name" class="form-control" required
                                placeholder="{{ get_label('enter_last_name', 'Enter last name') }}"
                                value="{{ old('last_name') }}">
                            @error('last_name')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label">{{ get_label('email', 'Email') }} <span
                                    class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control" required
                                placeholder="{{ get_label('enter_email', 'Enter email address') }}"
                                value="{{ old('email') }}">
                            @error('email')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label
                                class="form-label"><?= get_label('country_code_and_phone_number', 'Country code and phone number') ?></label>
                            <div class="input-group">
                                <input type="tel" class="form-control" id="phone-input" name="phone">
                                <input type="hidden" name="country_code" id="country_code">
                                <input type="hidden" name="phone" id="phone_number">
                                <input type="hidden" name="country_iso_code" id="country_iso_code" value="">

                            </div>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="lead_sources" class="form-label">{{ get_label('lead_sources', 'Lead Sources') }}
                                <span class="text-danger">*</span></label>
                            <select class="form-select" name="source_id" id="select_lead_source">
                                <option value="">{{ get_label('select_lead_source', 'Select Lead Source') }}</option>
                                @foreach ($lead_sources as $source)
                                    <option value="{{ $source->id }}">{{ $source->name }}</option>
                                @endforeach
                            </select>
                            @error('source_id')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>


                        <div class="col-md-4 mb-3">
                            <label for="lead_stages" class="form-label">{{ get_label('lead_stages', 'Lead Stages') }}
                                <span class="text-danger">*</span> </label>
                            <select class="form-select" name="stage_id" id="selected_stages">
                                <option value="">{{ get_label('select_lead_stage', 'Select Lead Stage') }}</option>
                                @foreach ($lead_stages as $stage)
                                    <option value="{{ $stage->id }}">{{ $stage->name }}</option>
                                @endforeach
                            </select>
                            @error('stage_id')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="assign_to" class="form-label">{{ get_label('assigned_to', 'Assign To') }} <span
                                    class="text-danger">*</span></label>
                            <select name="assigned_to" class="form-select" id="select_lead_assignee" required>
                                <option value="">{{ get_label('select_assignee', 'Assigned To') }}</option>
                                {{-- @foreach ($users as $user)
                                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                                @endforeach --}}
                            </select>
                            @error('assigned_to')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>



                        <!-- Professional Details -->
                        <div class="col-md-12 mt-4">
                            <h5>{{ get_label('professional_details', 'Professional Details') }}</h5>
                            <hr>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="job_title" class="form-label">{{ get_label('job_title', 'Job Title') }}</label>
                            <input type="text" name="job_title" class="form-control"
                                placeholder="{{ get_label('enter_job_title', 'Enter job title') }}">
                            @error('job_title')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="industry" class="form-label">{{ get_label('industry', 'Industry') }}</label>
                            <input type="text" name="industry" class="form-control"
                                placeholder="{{ get_label('enter_industry', 'Enter industry') }}">
                            @error('industry')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="company" class="form-label">{{ get_label('company', 'Company') }} <span
                                    class="text-danger">*</span></label>
                            <input type="text" name="company" class="form-control" required
                                placeholder="{{ get_label('enter_company', 'Enter company name') }}">
                            @error('company')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="website" class="form-label">{{ get_label('website', 'Website') }}</label>
                            <input type="text" name="website" class="form-control"
                                placeholder="{{ get_label('enter_website', 'Enter company website') }}">
                            @error('website')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Social Links -->
                        <div class="col-md-12 mt-4">
                            <h5>{{ get_label('social_links', 'Social Links') }}</h5>
                            <hr>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="linkedin" class="form-label">{{ get_label('linkedin', 'LinkedIn') }}</label>
                            <input type="url" name="linkedin" class="form-control"
                                placeholder="{{ get_label('enter_linkedin_url', 'Enter LinkedIn URL') }}"
                                value="{{ old('linkedin') }}">
                            @error('linkedin')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="instagram" class="form-label">{{ get_label('instagram', 'Instagram') }}</label>
                            <input type="url" name="instagram" class="form-control"
                                placeholder="{{ get_label('enter_instagram_url', 'Enter Instagram URL') }}"
                                value="{{ old('instagram') }}">
                            @error('instagram')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="facebook" class="form-label">{{ get_label('facebook', 'Facebook') }}</label>
                            <input type="url" name="facebook" class="form-control"
                                placeholder="{{ get_label('enter_facebook_url', 'Enter Facebook URL') }}"
                                value="{{ old('facebook') }}">
                            @error('facebook')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="pinterest" class="form-label">{{ get_label('pinterest', 'Pinterest') }}</label>
                            <input type="url" name="pinterest" class="form-control"
                                placeholder="{{ get_label('enter_pinterest_url', 'Enter Pinterest URL') }}"
                                value="{{ old('pinterest') }}">
                            @error('pinterest')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Address -->
                        <div class="col-md-12 mt-4">
                            <h5>{{ get_label('address', 'Address') }}</h5>
                            <hr>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="city" class="form-label">{{ get_label('city', 'City') }}</label>
                            <input type="text" name="city" class="form-control"
                                placeholder="{{ get_label('please_enter_city', 'Please enter city') }}"
                                value="{{ old('city') }}">
                            @error('city')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="state" class="form-label">{{ get_label('state', 'State') }}</label>
                            <input type="text" name="state" class="form-control"
                                placeholder="{{ get_label('please_enter_state', 'Please enter state') }}"
                                value="{{ old('state') }}">
                            @error('state')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="zip" class="form-label">{{ get_label('zip_code', 'Zip Code') }}</label>
                            <input type="number" name="zip" class="form-control"
                                placeholder="{{ get_label('please_enter_zip_code', 'Please enter ZIP code') }}"
                                value="{{ old('zip') }}">
                            @error('zip')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="country" class="form-label">{{ get_label('country', 'Country') }}</label>
                            <input type="text" name="country" class="form-control"
                                placeholder="{{ get_label('please_enter_country', 'Please enter country') }}">
                            @error('country')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                    </div>
                    <!-- Submit Button -->
                    <div class="mt-4 text-start">
                        <button type="submit" class="btn btn-primary me-2"
                            id="submit_btn"><?= get_label('create', 'Create') ?></button>
                        <button type="reset"
                            class="btn btn-outline-secondary"><?= get_label('cancel', 'Cancel') ?></button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script src="{{ asset('assets/js/pages/lead.js') }}"></script>
@endsection
