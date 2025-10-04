@extends('layout')
@section('title')
    {{ get_label('frontend_general_settings', 'Frontend General Settings') }}
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
                            <?= get_label('settings', 'Settings') ?>
                        </li>
                        <li class="breadcrumb-item active">
                            <?= get_label('frontend_general_settings', 'Frontend General Settings') ?>
                        </li>
                    </ol>
                </nav>
            </div>
        </div>
        <div class="card">
            
    <div class="card-header">
        <h4 class="">{{get_label('theme_settings', 'Theme Settings')}}</h4>
    </div>
    <div class="card-body">
        <form action="{{ route('settings.store_theme_settings') }}" class="form-submit-event"
            method="POST">
            <input type="hidden" name="redirect_url" value="">
            @csrf
            @method('PUT')

            <!-- Theme Selection Section -->
            <div class="row mb-4">
                <div class="col-12">
                    <h5 class="border-bottom pb-2">
                        <?= get_label('theme_selection', 'Theme Selection') ?>
                    </h5>
                </div>

                <div class="mb-3 col-md-6">
                    <label for="active_theme" class="form-label">
                        <?= get_label('active_theme', 'Active Theme') ?>
                        <span class="asterisk">*</span>
                    </label>
                    <select class="form-select" id="active_theme" name="active_theme" required>
                      <option value="new" {{ ($active_theme === 'new') ? 'selected' : '' }}>
                          <?= get_label('modern_theme', 'Modern Theme') ?>
                      </option>
                      <option value="old" {{ ($active_theme === 'old') ? 'selected' : '' }}>
                          <?= get_label('classic_theme', 'Classic Theme') ?>
                      </option>
                    </select>
                    @error('active_theme')
                        <p class="text-danger text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-3 col-md-6">
                    <label class="form-label">
                        <?= get_label('current_theme', 'Current Theme') ?>
                    </label>
                    <div class="form-control-plaintext">
                        <span class="badge bg-primary">
                            {{ $active_theme === 'new' ? get_label('modern_theme', 'Modern Theme') : get_label('classic_theme', 'Classic Theme') }}
                        </span>
                    </div>
                </div>
            </div>

           
            <div class="mt-4">
                <button type="submit" class="btn btn-primary me-2" id="submit_btn">
                    <?= get_label('update', 'Update') ?>
                </button>
                <button type="reset" class="btn btn-outline-secondary">
                    <?= get_label('cancel', 'Cancel') ?>
                </button>
            </div>
        </form>
    </div>
</div>
        <div class="mt-4"> </div>

        <div class="card">
            <div class="card-header">
                <h4 class="">{{get_label('frontend_general_settings', 'Frontend General Settings')}}</h4>
            </div>
            <div class="card-body">
                <form action="{{ route('settings.store_frontend_general_settings') }}" class="form-submit-event"
                    method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="redirect_url" value="">
                    @csrf
                    @method('PUT')

                    <!-- Company Section -->
                    <div class="row mb-4">

                        <div class="mb-3 col-md-12">
                            <label for="company_title" class="form-label"><?= get_label('company_title', 'Company Title') ?>
                                <span class="asterisk">*</span></label>
                            <input class="form-control" type="text" id="company_title" name="company_title"
                                placeholder="Enter company title" value="{{ $frontend_general_settings['company_title'] }}">
                            @error('settings.company_title')
                                <p class="text-danger text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="mb-3 col-md-12">
                            <label for="company_description"
                                class="form-label"><?= get_label('company_description', 'Company Description') ?></label>
                            <textarea class="form-control" id="company_description" name="company_description" rows="3"
                                placeholder="Enter company description">{{ $frontend_general_settings['company_description'] }}</textarea>
                            @error('settings.company_description')
                                <p class="text-danger text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Feature Sections -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h5 class="border-bottom pb-2">
                                <?= get_label('feature_sections', 'Feature Sections') ?>
                            </h5>
                        </div>


                        <div class="mb-3 col-md-6">
                            <label for="feature1_title"
                                class="form-label"><?= get_label('feature1_title', 'Feature 1 Title') ?></label>
                            <input class="form-control" type="text" id="feature1_title" name="feature1_title"
                                placeholder="Enter feature 1 title"
                                value="{{ $frontend_general_settings['feature1_title'] }}">
                            @error('settings.feature1_title')
                                <p class="text-danger text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="mb-3 col-md-6">
                            <label for="feature1_description"
                                class="form-label"><?= get_label('feature1_description', 'Feature 1 Description') ?></label>
                            <textarea class="form-control" id="feature1_description" name="feature1_description" rows="2"
                                placeholder="Enter feature 1 description">{{ $frontend_general_settings['feature1_description'] }}</textarea>
                            @error('settings.feature1_description')
                                <p class="text-danger text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>


                        <div class="mb-3 col-md-6">
                            <label for="feature2_title"
                                class="form-label"><?= get_label('feature2_title', 'Feature 2 Title') ?></label>
                            <input class="form-control" type="text" id="feature2_title" name="feature2_title"
                                placeholder="Enter feature 2 title"
                                value="{{ $frontend_general_settings['feature2_title'] }}">
                            @error('settings.feature2_title')
                                <p class="text-danger text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="mb-3 col-md-6">
                            <label for="feature2_description"
                                class="form-label"><?= get_label('feature2_description', 'Feature 2 Description') ?></label>
                            <textarea class="form-control" id="feature2_description" name="feature2_description" rows="2"
                                placeholder="Enter feature 2 description">{{ $frontend_general_settings['feature2_description'] }}</textarea>
                            @error('settings.feature2_description')
                                <p class="text-danger text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>


                        <div class="mb-3 col-md-6">
                            <label for="feature3_title"
                                class="form-label"><?= get_label('feature3_title', 'Feature 3 Title') ?></label>
                            <input class="form-control" type="text" id="feature3_title" name="feature3_title"
                                placeholder="Enter feature 3 title"
                                value="{{ $frontend_general_settings['feature3_title'] }}">
                            @error('settings.feature3_title')
                                <p class="text-danger text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="mb-3 col-md-6">
                            <label for="feature3_description"
                                class="form-label"><?= get_label('feature3_description', 'Feature 3 Description') ?></label>
                            <textarea class="form-control" id="feature3_description" name="feature3_description" rows="2"
                                placeholder="Enter feature 3 description">{{ $frontend_general_settings['feature3_description'] }}</textarea>
                            @error('settings.feature3_description')
                                <p class="text-danger text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>


                        <div class="mb-3 col-md-6">
                            <label for="feature4_title"
                                class="form-label"><?= get_label('feature4_title', 'Feature 4 Title') ?></label>
                            <input class="form-control" type="text" id="feature4_title" name="feature4_title"
                                placeholder="Enter feature 4 title"
                                value="{{ $frontend_general_settings['feature4_title'] }}">
                            @error('settings.feature4_title')
                                <p class="text-danger text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="mb-3 col-md-6">
                            <label for="feature4_description"
                                class="form-label"><?= get_label('feature4_description', 'Feature 4 Description') ?></label>
                            <textarea class="form-control" id="feature4_description" name="feature4_description" rows="2"
                                placeholder="Enter feature 4 description">{{ $frontend_general_settings['feature4_description'] }}</textarea>
                            @error('settings.feature4_description')
                                <p class="text-danger text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Team Collaboration Section -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h5 class="border-bottom pb-2">
                                <?= get_label('team_collaboration', 'Team Collaboration') ?>
                            </h5>
                        </div>
                        <div class="mb-3 col-md-6">
                            <label for="team_collab_title"
                                class="form-label"><?= get_label('team_collab_title', 'Team Collaboration Title') ?></label>
                            <input class="form-control" type="text" id="team_collab_title" name="team_collab_title"
                                placeholder="Enter team collaboration title"
                                value="{{ $frontend_general_settings['team_collab_title'] }}">
                            @error('settings.team_collab_title')
                                <p class="text-danger text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="mb-3 col-md-6">
                            <label for="team_collab_image"
                                class="form-label"><?= get_label('team_collab_image', 'Team Collaboration Image') ?>
                                @if(isset($frontend_general_settings['team_collab_image']) && $frontend_general_settings['team_collab_image'])
                                    <a data-bs-toggle="tooltip" data-bs-placement="right"
                                        data-bs-original-title="<?= get_label('view_current_image', 'View current image') ?>"
                                        href="{{ asset($frontend_general_settings['team_collab_image']) }}"
                                        data-lightbox="team_collab_image"
                                        data-title="<?= get_label('current_team_collab_image', 'Current Team Collaboration Image') ?>">
                                        <i class='bx bx-show-alt'></i>
                                    </a>
                                @endif
                            </label>
                            <input type="file" accept="image/*" class="form-control" name="team_collab_image_file">
                            <small class="text-muted">
                                {{ get_label('supported_formats', 'Supported formats: JPEG, JPG, PNG, GIF, SVG (max 2MB)') }}
                            </small>
                            @error('team_collab_image_file')
                                <p class="text-danger text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="mb-3 col-md-12">
                            <label for="team_collab_description"
                                class="form-label"><?= get_label('team_collab_description', 'Team Collaboration Description') ?></label>
                            <textarea class="form-control" id="team_collab_description" name="team_collab_description"
                                rows="3"
                                placeholder="Enter team collaboration description">{{ $frontend_general_settings['team_collab_description'] }}</textarea>
                            @error('settings.team_collab_description')
                                <p class="text-danger text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- About Section -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h5 class="border-bottom pb-2"><?= get_label('about_section', 'About Section') ?>
                            </h5>
                        </div>
                        <div class="mb-3 col-md-12">
                            <label for="about_section_title1"
                                class="form-label"><?= get_label('about_section_title1', 'About Section Title 1') ?></label>
                            <input class="form-control" type="text" id="about_section_title1" name="about_section_title1"
                                placeholder="Enter about section title"
                                value="{{ $frontend_general_settings['about_section_title1'] }}">
                            @error('settings.about_section_title1')
                                <p class="text-danger text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="mb-3 col-md-12">
                            <label for="about_section_title2"
                                class="form-label"><?= get_label('about_section_title2', 'About Section Title 2') ?></label>
                            <input class="form-control" type="text" id="about_section_title2" name="about_section_title2"
                                placeholder="Enter about section title"
                                value="{{ $frontend_general_settings['about_section_title2'] }}">
                            @error('settings.about_section_title2')
                                <p class="text-danger text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="mb-3 col-md-12">
                            <label for="about_section_description"
                                class="form-label"><?= get_label('about_section_description', 'About Section Description') ?></label>
                            <textarea class="form-control" id="about_section_description" name="about_section_description"
                                rows="3"
                                placeholder="Enter about section description">{{ $frontend_general_settings['about_section_description'] }}</textarea>
                            @error('settings.about_section_description')
                                <p class="text-danger text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Info Cards Section -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h5 class="border-bottom pb-2"><?= get_label('info_cards', 'Info Cards') ?></h5>
                        </div>

                        <div class="mb-3 col-md-4">
                            <label for="info_card1_icon" class="form-label">
                                <?= get_label('info_card1_icon', 'Info Card 1 Icon') ?>
                                @if(isset($frontend_general_settings['info_card1_icon']) && $frontend_general_settings['info_card1_icon'])
                                    <a data-bs-toggle="tooltip" data-bs-placement="right"
                                        data-bs-original-title="<?= get_label('view_current_icon', 'View current icon') ?>"
                                        href="{{ asset($frontend_general_settings['info_card1_icon']) }}"
                                        data-lightbox="info_card1_icon"
                                        data-title="<?= get_label('current_icon', 'Current Icon') ?>">
                                        <i class='bx bx-show-alt'></i>
                                    </a>
                                @endif
                            </label>
                            <input type="file" accept="image/*" class="form-control" name="info_card1_icon_file">
                            <div class="mt-1">
                                <small class="text-muted">
                                    {{ get_label('supported_formats', 'Supported formats: JPEG, JPG, PNG, GIF, SVG (max 2MB)') }}
                                </small>
                            </div>
                            @error('info_card1_icon_file')
                                <p class="text-danger text-xs mt-1">{{ $message }}</p>
                            @enderror

                            <label for="info_card1_description" class="form-label mt-3">
                                <?= get_label('info_card1_description', 'Info Card 1 Description') ?>
                            </label>
                            <textarea class="form-control" id="info_card1_description" name="info_card1_description"
                                rows="2"
                                placeholder="Enter card 1 description">{{ $frontend_general_settings['info_card1_description'] }}</textarea>
                            @error('settings.info_card1_description')
                                <p class="text-danger text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-3 col-md-4">
                            <label for="info_card2_icon" class="form-label">
                                <?= get_label('info_card2_icon', 'Info Card 2 Icon') ?>
                                @if(isset($frontend_general_settings['info_card2_icon']) && $frontend_general_settings['info_card2_icon'])
                                    <a data-bs-toggle="tooltip" data-bs-placement="right"
                                        data-bs-original-title="<?= get_label('view_current_icon', 'View current icon') ?>"
                                        href="{{ asset($frontend_general_settings['info_card2_icon']) }}"
                                        data-lightbox="info_card2_icon"
                                        data-title="<?= get_label('current_icon', 'Current Icon') ?>">
                                        <i class='bx bx-show-alt'></i>
                                    </a>
                                @endif
                            </label>
                            <input type="file" accept="image/*" class="form-control" name="info_card2_icon_file">
                            <div class="mt-1">
                                <small class="text-muted">
                                    {{ get_label('supported_formats', 'Supported formats: JPEG, JPG, PNG, GIF, SVG (max 2MB)') }}
                                </small>
                            </div>
                            @error('info_card2_icon_file')
                                <p class="text-danger text-xs mt-1">{{ $message }}</p>
                            @enderror

                            <label for="info_card2_description" class="form-label mt-3">
                                <?= get_label('info_card2_description', 'Info Card 2 Description') ?>
                            </label>
                            <textarea class="form-control" id="info_card2_description" name="info_card2_description"
                                rows="2"
                                placeholder="Enter card 2 description">{{ $frontend_general_settings['info_card2_description'] }}</textarea>
                            @error('settings.info_card2_description')
                                <p class="text-danger text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-3 col-md-4">
                            <label for="info_card3_icon" class="form-label">
                                <?= get_label('info_card3_icon', 'Info Card 3 Icon') ?>
                                @if(isset($frontend_general_settings['info_card3_icon']) && $frontend_general_settings['info_card3_icon'])
                                    <a data-bs-toggle="tooltip" data-bs-placement="right"
                                        data-bs-original-title="<?= get_label('view_current_icon', 'View current icon') ?>"
                                        href="{{ asset($frontend_general_settings['info_card3_icon']) }}"
                                        data-lightbox="info_card3_icon"
                                        data-title="<?= get_label('current_icon', 'Current Icon') ?>">
                                        <i class='bx bx-show-alt'></i>
                                    </a>
                                @endif
                            </label>
                            <input type="file" accept="image/*" class="form-control" name="info_card3_icon_file">
                            <div class="mt-1">
                                <small class="text-muted">
                                    {{ get_label('supported_formats', 'Supported formats: JPEG, JPG, PNG, GIF, SVG (max 2MB)') }}
                                </small>
                            </div>
                            @error('info_card3_icon_file')
                                <p class="text-danger text-xs mt-1">{{ $message }}</p>
                            @enderror

                            <label for="info_card3_description" class="form-label mt-3">
                                <?= get_label('info_card3_description', 'Info Card 3 Description') ?>
                            </label>
                            <textarea class="form-control" id="info_card3_description" name="info_card3_description"
                                rows="2"
                                placeholder="Enter card 3 description">{{ $frontend_general_settings['info_card3_description'] }}</textarea>
                            @error('settings.info_card3_description')
                                <p class="text-danger text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>


                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary me-2" id="submit_btn">
                            <?= get_label('update', 'Update') ?>
                        </button>
                        <button type="reset" class="btn btn-outline-secondary">
                            <?= get_label('cancel', 'Cancel') ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
        <div class="mt-4"> </div>
        <div class="card">
            <div class="card-header">
                <h4>About Us General Settings</h4>

            </div>
            <div class="card-body">
                <form action="{{ route('settings.store_frontend_about_us_general') }}" class="form-submit-event"
                    method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="redirect_url" value="">
                    @csrf
                    @method('PUT')

                    <!-- Header Section -->
                    <div class="row mb-4">
                        {{-- <div class="col-12">
                            <h5 class="text-primary border-bottom pb-2">{{ get_label('header_section', 'Header Section') }}
                            </h5>
                        </div> --}}
                        <div class="mb-3 col-md-6">
                            <label for="header_subtitle"
                                class="form-label">{{ get_label('header_subtitle', 'Header Subtitle') }} <span
                                    class="asterisk">*</span></label>
                            <input class="form-control" type="text" id="header_subtitle" name="header_subtitle"
                                placeholder="Enter header subtitle"
                                value="{{ $frontend_general_settings['header_subtitle'] }}">
                            @error('settings.header_subtitle')
                                <p class="text-danger text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="mb-3 col-md-6">
                            <label for="header_title" class="form-label">{{ get_label('header_title', 'Header Title') }}
                                <span class="asterisk">*</span></label>
                            <input class="form-control" type="text" id="header_title" name="header_title"
                                placeholder="Enter header title" value="{{ $frontend_general_settings['header_title'] }}">
                            @error('settings.header_title')
                                <p class="text-danger text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="mb-3 col-md-12">
                            <label for="header_description"
                                class="form-label">{{ get_label('header_description', 'Header Description') }}</label>
                            <textarea class="form-control" id="header_description" name="header_description" rows="3"
                                placeholder="Enter header description">{{ $frontend_general_settings['header_description'] }}</textarea>
                            @error('settings.header_description')
                                <p class="text-danger text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Info Cards Section -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h5 class="border-bottom pb-2">{{ get_label('info_cards', 'Info Cards') }}</h5>
                        </div>
                        <!-- Info Card 1 -->
                        <div class="mb-3 col-md-4">

                            <label for="info_about_us_card1_title"
                                class="form-label mt-3">{{ get_label('info_about_us_card1_title', 'Info Card About us 1 Title') }}</label>
                            <input class="form-control" type="text" id="info_about_us_card1_title"
                                name="info_about_us_card1_title" placeholder="Enter card 1 title"
                                value="{{ $frontend_general_settings['info_about_us_card1_title'] }}">
                            @error('settings.info_about_us_card1_title')
                                <p class="text-danger text-xs mt-1">{{ $message }}</p>
                            @enderror
                            <label for="info_about_us_card1_description"
                                class="form-label mt-3">{{ get_label('info_about_us_card1_description', 'Info Card 1 Description') }}</label>
                            <textarea class="form-control" id="info_about_us_card1_description"
                                name="info_about_us_card1_description" rows="2"
                                placeholder="Enter card 1 description">{{ $frontend_general_settings['info_about_us_card1_description'] }}</textarea>
                            @error('settings.info_about_us_card1_description')
                                <p class="text-danger text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <!-- Info Card 2 -->
                        <div class="mb-3 col-md-4">

                            <label for="info_about_us_card2_title"
                                class="form-label mt-3">{{ get_label('info_about_us_card2_title', 'Info Card 2 Title') }}</label>
                            <input class="form-control" type="text" id="info_about_us_card2_title"
                                name="info_about_us_card2_title" placeholder="Enter card 2 title"
                                value="{{ $frontend_general_settings['info_about_us_card2_title'] }}">
                            @error('settings.info_about_us_card2_title')
                                <p class="text-danger text-xs mt-1">{{ $message }}</p>
                            @enderror
                            <label for="info_about_us_card2_description"
                                class="form-label mt-3">{{ get_label('info_about_us_card2_description', 'Info Card 2 Description') }}</label>
                            <textarea class="form-control" id="info_about_us_card2_description"
                                name="info_about_us_card2_description" rows="2"
                                placeholder="Enter card 2 description">{{ $frontend_general_settings['info_about_us_card2_description'] }}</textarea>
                            @error('settings.info_about_us_card2_description')
                                <p class="text-danger text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <!-- Info Card 3 -->
                        <div class="mb-3 col-md-4">

                            <label for="info_about_us_card3_title"
                                class="form-label mt-3">{{ get_label('info_about_us_card3_title', 'Info Card 3 Title') }}</label>
                            <input class="form-control" type="text" id="info_about_us_card3_title"
                                name="info_about_us_card3_title" placeholder="Enter card 3 title"
                                value="{{ $frontend_general_settings['info_about_us_card3_title'] }}">
                            @error('settings.info_about_us_card3_title')
                                <p class="text-danger text-xs mt-1">{{ $message }}</p>
                            @enderror
                            <label for="info_about_us_card3_description"
                                class="form-label mt-3">{{ get_label('info_about_us_card3_description', 'Info Card 3 Description') }}</label>
                            <textarea class="form-control" id="info_about_us_card3_description"
                                name="info_about_us_card3_description" rows="2"
                                placeholder="Enter card 3 description">{{ $frontend_general_settings['info_about_us_card3_description'] }}</textarea>
                            @error('settings.info_about_us_card3_description')
                                <p class="text-danger text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Project Management Section -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h5 class="border-bottom pb-2">
                                {{ get_label('project_management', 'Project Management') }}
                            </h5>
                        </div>
                        <div class="mb-3 col-md-6">
                            <label for="project_management_subtitle"
                                class="form-label">{{ get_label('project_management_subtitle', 'Project Management Subtitle') }}</label>
                            <input class="form-control" type="text" id="project_management_subtitle"
                                name="project_management_subtitle" placeholder="Enter project management subtitle"
                                value="{{ $frontend_general_settings['project_management_subtitle'] }}">
                            @error('settings.project_management_subtitle')
                                <p class="text-danger text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="mb-3 col-md-6">
                            <label for="project_management_title"
                                class="form-label">{{ get_label('project_management_title', 'Project Management Title') }}</label>
                            <input class="form-control" type="text" id="project_management_title"
                                name="project_management_title" placeholder="Enter project management title"
                                value="{{ $frontend_general_settings['project_management_title'] }}">
                            @error('settings.project_management_title')
                                <p class="text-danger text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="mb-3 col-md-12">
                            <label for="project_management_description"
                                class="form-label">{{ get_label('project_management_description', 'Project Management Description') }}</label>
                            <textarea class="form-control" id="project_management_description"
                                name="project_management_description" rows="3"
                                placeholder="Enter project management description">{{ $frontend_general_settings['project_management_description'] }}</textarea>
                            @error('settings.project_management_description')
                                <p class="text-danger text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-3 col-md-6">
                            <label for="project_management_feature1"
                                class="form-label">{{ get_label('project_management_feature1', 'Feature 1') }}</label>
                            <input class="form-control" type="text" id="project_management_feature1"
                                name="project_management_feature1" placeholder="Enter feature 1"
                                value="{{ $frontend_general_settings['project_management_feature1'] }}">
                            @error('settings.project_management_feature1')
                                <p class="text-danger text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="mb-3 col-md-6">
                            <label for="project_management_feature2"
                                class="form-label">{{ get_label('project_management_feature2', 'Feature 2') }}</label>
                            <input class="form-control" type="text" id="project_management_feature2"
                                name="project_management_feature2" placeholder="Enter feature 2"
                                value="{{ $frontend_general_settings['project_management_feature2'] }}">
                            @error('settings.project_management_feature2')
                                <p class="text-danger text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="mb-3 col-md-6">
                            <label for="project_management_feature3"
                                class="form-label">{{ get_label('project_management_feature3', 'Feature 3') }}</label>
                            <input class="form-control" type="text" id="project_management_feature3"
                                name="project_management_feature3" placeholder="Enter feature 3"
                                value="{{ $frontend_general_settings['project_management_feature3'] }}">
                            @error('settings.project_management_feature3')
                                <p class="text-danger text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="mb-3 col-md-6">
                            <label for="project_management_feature4"
                                class="form-label">{{ get_label('project_management_feature4', 'Feature 4') }}</label>
                            <input class="form-control" type="text" id="project_management_feature4"
                                name="project_management_feature4" placeholder="Enter feature 4"
                                value="{{ $frontend_general_settings['project_management_feature4'] }}">
                            @error('settings.project_management_feature4')
                                <p class="text-danger text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="mb-3 col-md-6">
                            <label for="project_management_image" class="form-label">
                                {{ get_label('project_management_image', 'Project Management Image') }}
                                @if(isset($frontend_general_settings['project_management_image']) && $frontend_general_settings['project_management_image'])
                                    <a data-bs-toggle="tooltip" data-bs-placement="right"
                                        data-bs-original-title="{{ get_label('view_current_image', 'View current image') }}"
                                        href="{{ asset($frontend_general_settings['project_management_image']) }}"
                                        data-lightbox="project_management_image"
                                        data-title="{{ get_label('current_image', 'Current Image') }}">
                                        <i class='bx bx-show-alt'></i>
                                    </a>
                                @endif
                            </label>
                            <input type="file" accept="image/*" class="form-control" name="project_management_image_file">
                            <small class="text-muted">
                                {{ get_label('supported_formats', 'Supported formats: JPEG, JPG, PNG, GIF, SVG (max 2MB)') }}
                            </small>
                            @error('project_management_image_file')
                                <p class="text-danger text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Task Management Section -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h5 class="border-bottom pb-2">
                                {{ get_label('task_management', 'Task Management') }}
                            </h5>
                        </div>
                        <div class="mb-3 col-md-6">
                            <label for="task_management_subtitle"
                                class="form-label">{{ get_label('task_management_subtitle', 'Task Management Subtitle') }}</label>
                            <input class="form-control" type="text" id="task_management_subtitle"
                                name="task_management_subtitle" placeholder="Enter task management subtitle"
                                value="{{ $frontend_general_settings['task_management_subtitle'] }}">
                            @error('settings.task_management_subtitle')
                                <p class="text-danger text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="mb-3 col-md-6">
                            <label for="task_management_title"
                                class="form-label">{{ get_label('task_management_title', 'Task Management Title') }}</label>
                            <input class="form-control" type="text" id="task_management_title" name="task_management_title"
                                placeholder="Enter task management title"
                                value="{{ $frontend_general_settings['task_management_title'] }}">
                            @error('settings.task_management_title')
                                <p class="text-danger text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="mb-3 col-md-12">
                            <label for="task_management_description"
                                class="form-label">{{ get_label('task_management_description', 'Task Management Description') }}</label>
                            <textarea class="form-control" id="task_management_description"
                                name="task_management_description" rows="3"
                                placeholder="Enter task management description">{{ $frontend_general_settings['task_management_description'] }}</textarea>
                            @error('settings.task_management_description')
                                <p class="text-danger text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-3 col-md-6">
                            <label for="task_management_feature1"
                                class="form-label">{{ get_label('task_management_feature1', 'Feature 1') }}</label>
                            <input class="form-control" type="text" id="task_management_feature1"
                                name="task_management_feature1" placeholder="Enter feature 1"
                                value="{{ $frontend_general_settings['task_management_feature1']}}">
                            @error('settings.task_management_feature1')
                                <p class="text-danger text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="mb-3 col-md-6">
                            <label for="task_management_feature2"
                                class="form-label">{{ get_label('task_management_feature2', 'Feature 2') }}</label>
                            <input class="form-control" type="text" id="task_management_feature2"
                                name="task_management_feature2" placeholder="Enter feature 2"
                                value="{{ $frontend_general_settings['task_management_feature2'] }}">
                            @error('settings.task_management_feature2')
                                <p class="text-danger text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="mb-3 col-md-6">
                            <label for="task_management_feature3"
                                class="form-label">{{ get_label('task_management_feature3', 'Feature 3') }}</label>
                            <input class="form-control" type="text" id="task_management_feature3"
                                name="task_management_feature3" placeholder="Enter feature 3"
                                value="{{ $frontend_general_settings['task_management_feature3'] }}">
                            @error('settings.task_management_feature3')
                                <p class="text-danger text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="mb-3 col-md-6">
                            <label for="task_management_feature4"
                                class="form-label">{{ get_label('task_management_feature4', 'Feature 4') }}</label>
                            <input class="form-control" type="text" id="task_management_feature4"
                                name="task_management_feature4" placeholder="Enter feature 4"
                                value="{{ $frontend_general_settings['task_management_feature4'] }}">
                            @error('settings.task_management_feature4')
                                <p class="text-danger text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="mb-3 col-md-6">
                            <label for="task_management_image" class="form-label">
                                {{ get_label('task_management_image', 'Task Management Image') }}
                                @if(isset($frontend_general_settings['task_management_image']) && $frontend_general_settings['task_management_image'])
                                    <a data-bs-toggle="tooltip" data-bs-placement="right"
                                        data-bs-original-title="{{ get_label('view_current_image', 'View current image') }}"
                                        href="{{ asset($frontend_general_settings['task_management_image']) }}"
                                        data-lightbox="task_management_image"
                                        data-title="{{ get_label('current_image', 'Current Image') }}">
                                        <i class='bx bx-show-alt'></i>
                                    </a>
                                @endif
                            </label>
                            <input type="file" accept="image/*" class="form-control" name="task_management_image_file">
                            <small class="text-muted">
                                {{ get_label('supported_formats', 'Supported formats: JPEG, JPG, PNG, GIF, SVG (max 2MB)') }}
                            </small>
                            @error('task_management_image_file')
                                <p class="text-danger text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Team Collaboration Section -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h5 class="border-bottom pb-2">
                                {{ get_label('team_collaboration', 'Team Collaboration') }}
                            </h5>
                        </div>
                        <div class="mb-3 col-md-6">
                            <label for="team_collaboration_subtitle"
                                class="form-label">{{ get_label('team_collaboration_subtitle', 'Team Collaboration Subtitle') }}</label>
                            <input class="form-control" type="text" id="team_collaboration_subtitle"
                                name="team_collaboration_subtitle" placeholder="Enter team collaboration subtitle"
                                value="{{ $frontend_general_settings['team_collaboration_subtitle'] }}">
                            @error('settings.team_collaboration_subtitle')
                                <p class="text-danger text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="mb-3 col-md-6">
                            <label for="team_collaboration_title"
                                class="form-label">{{ get_label('team_collaboration_title', 'Team Collaboration Title') }}</label>
                            <input class="form-control" type="text" id="team_collaboration_title"
                                name="team_collaboration_title" placeholder="Enter team collaboration title"
                                value="{{ $frontend_general_settings['team_collaboration_title'] }}">
                            @error('settings.team_collaboration_title')
                                <p class="text-danger text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="mb-3 col-md-12">
                            <label for="team_collaboration_description"
                                class="form-label">{{ get_label('team_collaboration_description', 'Team Collaboration Description') }}</label>
                            <textarea class="form-control" id="team_collaboration_description"
                                name="team_collaboration_description" rows="3"
                                placeholder="Enter team collaboration description">{{ $frontend_general_settings['team_collaboration_description'] }}</textarea>
                            @error('settings.team_collaboration_description')
                                <p class="text-danger text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-3 col-md-6">
                            <label for="team_collaboration_feature1"
                                class="form-label">{{ get_label('team_collaboration_feature1', 'Feature 1') }}</label>
                            <input class="form-control" type="text" id="team_collaboration_feature1"
                                name="team_collaboration_feature1" placeholder="Enter feature 1"
                                value="{{ $frontend_general_settings['team_collaboration_feature1'] }}">
                            @error('settings.team_collaboration_feature1')
                                <p class="text-danger text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="mb-3 col-md-6">
                            <label for="team_collaboration_feature2"
                                class="form-label">{{ get_label('team_collaboration_feature2', 'Feature 2') }}</label>
                            <input class="form-control" type="text" id="team_collaboration_feature2"
                                name="team_collaboration_feature2" placeholder="Enter feature 2"
                                value="{{ $frontend_general_settings['team_collaboration_feature2'] }}">
                            @error('settings.team_collaboration_feature2')
                                <p class="text-danger text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="mb-3 col-md-6">
                            <label for="team_collaboration_feature3"
                                class="form-label">{{ get_label('team_collaboration_feature3', 'Feature 3') }}</label>
                            <input class="form-control" type="text" id="team_collaboration_feature3"
                                name="team_collaboration_feature3" placeholder="Enter feature 3"
                                value="{{ $frontend_general_settings['team_collaboration_feature3'] }}">
                            @error('settings.team_collaboration_feature3')
                                <p class="text-danger text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="mb-3 col-md-6">
                            <label for="team_collaboration_feature4"
                                class="form-label">{{ get_label('team_collaboration_feature4', 'Feature 4') }}</label>
                            <input class="form-control" type="text" id="team_collaboration_feature4"
                                name="team_collaboration_feature4" placeholder="Enter feature 4"
                                value="{{ $frontend_general_settings['team_collaboration_feature4'] }}">
                            @error('settings.team_collaboration_feature4')
                                <p class="text-danger text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="mb-3 col-md-6">
                            <label for="team_collaboration_image" class="form-label">
                                {{ get_label('team_collaboration_image', 'Team Collaboration Image') }}
                                @if(isset($frontend_general_settings['team_collaboration_image']) && $frontend_general_settings['team_collaboration_image'])
                                    <a data-bs-toggle="tooltip" data-bs-placement="right"
                                        data-bs-original-title="{{ get_label('view_current_image', 'View current image') }}"
                                        href="{{ asset($frontend_general_settings['team_collaboration_image']) }}"
                                        data-lightbox="team_collaboration_image"
                                        data-title="{{ get_label('current_image', 'Current Image') }}">
                                        <i class='bx bx-show-alt'></i>
                                    </a>
                                @endif
                            </label>
                            <input type="file" accept="image/*" class="form-control" name="team_collaboration_image_file">

                            <small class="text-muted">
                                {{ get_label('supported_formats', 'Supported formats: JPEG, JPG, PNG, GIF, SVG (max 2MB)') }}
                            </small>

                            @error('team_collaboration_image_file')
                                <p class="text-danger text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Increased Productivity Section -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h5 class="border-bottom pb-2">
                                {{ get_label('increased_productivity', 'Increased Productivity') }}
                            </h5>
                        </div>
                        <div class="mb-3 col-md-6">
                            <label for="increased_productivity_subtitle"
                                class="form-label">{{ get_label('increased_productivity_subtitle', 'Increased Productivity Subtitle') }}</label>
                            <input class="form-control" type="text" id="increased_productivity_subtitle"
                                name="increased_productivity_subtitle" placeholder="Enter increased productivity subtitle"
                                value="{{ $frontend_general_settings['increased_productivity_subtitle'] }}">
                            @error('settings.increased_productivity_subtitle')
                                <p class="text-danger text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="mb-3 col-md-6">
                            <label for="increased_productivity_title"
                                class="form-label">{{ get_label('increased_productivity_title', 'Increased Productivity Title') }}</label>
                            <input class="form-control" type="text" id="increased_productivity_title"
                                name="increased_productivity_title" placeholder="Enter increased productivity title"
                                value="{{ $frontend_general_settings['increased_productivity_title'] }}">
                            @error('settings.increased_productivity_title')
                                <p class="text-danger text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="mb-3 col-md-12">
                            <label for="increased_productivity_description"
                                class="form-label">{{ get_label('increased_productivity_description', 'Increased Productivity Description') }}</label>
                            <textarea class="form-control" id="increased_productivity_description"
                                name="increased_productivity_description" rows="3"
                                placeholder="Enter increased productivity description">{{ $frontend_general_settings['increased_productivity_description'] }}</textarea>
                            @error('settings.increased_productivity_description')
                                <p class="text-danger text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-3 col-md-6">
                            <label for="increased_productivity_feature1"
                                class="form-label">{{ get_label('increased_productivity_feature1', 'Feature 1') }}</label>
                            <input class="form-control" type="text" id="increased_productivity_feature1"
                                name="increased_productivity_feature1" placeholder="Enter feature 1"
                                value="{{ $frontend_general_settings['increased_productivity_feature1'] }}">
                            @error('settings.increased_productivity_feature1')
                                <p class="text-danger text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="mb-3 col-md-6">
                            <label for="increased_productivity_feature2"
                                class="form-label">{{ get_label('increased_productivity_feature2', 'Feature 2') }}</label>
                            <input class="form-control" type="text" id="increased_productivity_feature2"
                                name="increased_productivity_feature2" placeholder="Enter feature 2"
                                value="{{ $frontend_general_settings['increased_productivity_feature2'] }}">
                            @error('settings.increased_productivity_feature2')
                                <p class="text-danger text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="mb-3 col-md-6">
                            <label for="increased_productivity_feature3"
                                class="form-label">{{ get_label('increased_productivity_feature3', 'Feature 3') }}</label>
                            <input class="form-control" type="text" id="increased_productivity_feature3"
                                name="increased_productivity_feature3" placeholder="Enter feature 3"
                                value="{{ $frontend_general_settings['increased_productivity_feature3'] }}">
                            @error('settings.increased_productivity_feature3')
                                <p class="text-danger text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="mb-3 col-md-6">
                            <label for="increased_productivity_feature4"
                                class="form-label">{{ get_label('increased_productivity_feature4', 'Feature 4') }}</label>
                            <input class="form-control" type="text" id="increased_productivity_feature4"
                                name="increased_productivity_feature4" placeholder="Enter feature 4"
                                value="{{ $frontend_general_settings['increased_productivity_feature4'] }}">
                            @error('settings.increased_productivity_feature4')
                                <p class="text-danger text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="mb-3 col-md-6">
                            <label for="increased_productivity_image" class="form-label">
                                {{ get_label('increased_productivity_image', 'Increased Productivity Image') }}
                                @if(isset($frontend_general_settings['increased_productivity_image']) && $frontend_general_settings['increased_productivity_image'])
                                    <a data-bs-toggle="tooltip" data-bs-placement="right"
                                        data-bs-original-title="{{ get_label('view_current_image', 'View current image') }}"
                                        href="{{ asset($frontend_general_settings['increased_productivity_image']) }}"
                                        data-lightbox="increased_productivity_image"
                                        data-title="{{ get_label('current_image', 'Current Image') }}">
                                        <i class='bx bx-show-alt'></i>
                                    </a>
                                @endif
                            </label>
                            <input type="file" accept="image/*" class="form-control"
                                name="increased_productivity_image_file">
                            <small class="text-muted">
                                {{ get_label('supported_formats', 'Supported formats: JPEG, JPG, PNG, GIF, SVG (max 2MB)') }}
                            </small>
                            @error('increased_productivity_image_file')
                                <p class="text-danger text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Carousel Images Section -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h5 class="border-bottom pb-2">
                                {{ get_label('carousel_images', 'Carousel Images') }}
                            </h5>
                        </div>
                        <div class="mb-3 col-md-6">
                            <label for="system_overview" class="form-label">
                                {{ get_label('system_overview', 'System Overview') }}
                            </label>
                            <input class="form-control" type="text" id="system_overview" name="system_overview"
                                placeholder="Enter system overview title"
                                value="{{ $frontend_general_settings['system_overview'] ?? 'System Overview' }}">

                            @error('settings.system_overview')
                                <p class="text-danger text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="mb-3 col-md-6">
                            <label for="discover_our_system" class="form-label">
                                {{ get_label('discover_our_system', 'Discover Our System') }}
                            </label>
                            <input class="form-control" type="text" id="discover_our_system" name="discover_our_system"
                                placeholder="Enter discover our system title"
                                value="{{ $frontend_general_settings['discover_our_system'] ?? 'Discover Our System' }}">

                            @error('settings.discover_our_system')
                                <p class="text-danger text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <!-- Carousel Image 1 -->
                        <div class="mb-3 col-md-6">
                            <label for="carousel_image1_file" class="form-label">
                                {{ get_label('carousel_image1', 'Carousel Image 1') }}
                                @if(isset($frontend_general_settings['carousel_image1_file']) && $frontend_general_settings['carousel_image1_file'])
                                    <a data-bs-toggle="tooltip" data-bs-placement="right"
                                        data-bs-original-title="{{ get_label('view_current_image', 'View current image') }}"
                                        href="{{ asset($frontend_general_settings['carousel_image1_file']) }}"
                                        data-lightbox="carousel_image1"
                                        data-title="{{ get_label('current_image', 'Current Image') }}">
                                        <i class='bx bx-show-alt'></i>
                                    </a>
                                @endif
                            </label>
                            <input type="file" accept="image/*" class="form-control" name="carousel_image1_file">
                            <small class="text-muted">
                                {{ "Supported formats: JPEG, JPG, PNG, SVG (max 2MB)" }}
                            </small>
                            @error('carousel_image1_file')
                                <p class="text-danger text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <!-- Carousel Image 2 -->
                        <div class="mb-3 col-md-6">
                            <label for="carousel_image2" class="form-label">
                                {{ get_label('carousel_image2', 'Carousel Image 2') }}
                                @if(isset($frontend_general_settings['carousel_image2_file']) && $frontend_general_settings['carousel_image2_file'])
                                    <a data-bs-toggle="tooltip" data-bs-placement="right"
                                        data-bs-original-title="{{ get_label('view_current_image', 'View current image') }}"
                                        href="{{ asset($frontend_general_settings['carousel_image2_file']) }}"
                                        data-lightbox="carousel_image2"
                                        data-title="{{ get_label('current_image', 'Current Image') }}">
                                        <i class='bx bx-show-alt'></i>
                                    </a>
                                @endif
                            </label>
                            <input type="file" accept="image/*" class="form-control" name="carousel_image2_file">
                            <small class="text-muted">
                                {{ "Supported formats: JPEG, JPG, PNG, SVG (max 2MB)" }}
                            </small>
                            @error('carousel_image2_file')
                                <p class="text-danger text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <!-- Carousel Image 3 -->
                        <div class="mb-3 col-md-6">
                            <label for="carousel_image3" class="form-label">
                                {{ get_label('carousel_image3', 'Carousel Image 3') }}
                                @if(isset($frontend_general_settings['carousel_image3_file']) && $frontend_general_settings['carousel_image3_file'])
                                    <a data-bs-toggle="tooltip" data-bs-placement="right"
                                        data-bs-original-title="{{ get_label('view_current_image', 'View current image') }}"
                                        href="{{ asset($frontend_general_settings['carousel_image3_file']) }}"
                                        data-lightbox="carousel_image3"
                                        data-title="{{ get_label('current_image', 'Current Image') }}">
                                        <i class='bx bx-show-alt'></i>
                                    </a>
                                @endif
                            </label>
                            <input type="file" accept="image/*" class="form-control" name="carousel_image3_file">
                            <small class="text-muted">
                                {{ "Supported formats: JPEG, JPG, PNG, SVG (max 2MB)" }}
                            </small>
                            @error('carousel_image3_file')
                                <p class="text-danger text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <!-- Carousel Image 4 -->
                        <div class="mb-3 col-md-6">
                            <label for="carousel_image4" class="form-label">
                                {{ get_label('carousel_image4', 'Carousel Image 4') }}
                                @if(isset($frontend_general_settings['carousel_image4_file']) && $frontend_general_settings['carousel_image4_file'])
                                    <a data-bs-toggle="tooltip" data-bs-placement="right"
                                        data-bs-original-title="{{ get_label('view_current_image', 'View current image') }}"
                                        href="{{ asset($frontend_general_settings['carousel_image4_file']) }}"
                                        data-lightbox="carousel_image4"
                                        data-title="{{ get_label('current_image', 'Current Image') }}">
                                        <i class='bx bx-show-alt'></i>
                                    </a>
                                @endif
                            </label>
                            <input type="file" accept="image/*" class="form-control" name="carousel_image4_file">
                            <small class="text-muted">
                                {{ "Supported formats: JPEG, JPG, PNG, SVG (max 2MB)" }}
                            </small>
                            @error('carousel_image4_file')
                                <p class="text-danger text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Submit and Cancel Buttons -->
                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary me-2"
                            id="submit_btn">{{ get_label('update', 'Update') }}</button>
                        <button type="reset" class="btn btn-outline-secondary">{{ get_label('cancel', 'Cancel') }}</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="mt-4"> </div>

        <div class="card">
            <div class="card-header">
                <h4>{{ get_label('features_management', 'Features Management') }}</h4>
            </div>
            <div class="card-body">
                <div class="alert alert-info medium" role="alert">
<strong>Note:</strong> For high-quality SVG icons, you can refer to the following resources:  
<a href="https://heroicons.com/" target="_blank" rel="noopener noreferrer">Heroicons</a>,  
<a href="https://iamvector.com/" target="_blank" rel="noopener noreferrer">IAmVector</a>, and  
<a href="https://icons8.com/icons/set/svg" target="_blank" rel="noopener noreferrer">Icons8</a>.
        </div>
                <form action="{{ route('settings.store_feature_settings') }}" method="POST" enctype="multipart/form-data"
                    class="form-submit-event" id="features-form">
                    @csrf
                    @method('PUT')

                    <!-- Feature Items Section -->
                    <div class="row mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="mb-0">{{ get_label('feature_items', 'Feature Items') }}</h5>

                            <button type="button" id="addFeature" class="btn btn-sm btn-primary" data-bs-toggle="tooltip"
                                data-bs-original-title="<?= get_label('add_feature', 'Add Feature') ?>"
                                data-feature-count="{{ isset($frontend_general_settings['features']) ? count($frontend_general_settings['features']) : 1 }}">
                                <i class='bx bx-plus'></i>
                            </button>
                        </div>

                        <!-- Features Container -->
                        <div id="featuresContainer">
                            @forelse ($frontend_general_settings['features'] as $index => $feature)
                                <div class="feature-item border rounded p-3 mb-3 position-relative" data-index="{{ $index }}">
                                    <!-- Header with title and remove button -->
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <h6 class="mb-0 text-primary">{{ get_label('feature_item', 'Feature Item') }}
                                            {{ $index + 1 }}</h6>
                                        <button type="button" class="btn btn-danger btn-sm remove-feature"
                                            onclick="removeFeature(this)" data-bs-toggle="tooltip"
                                            data-bs-original-title="{{ get_label('remove_feature', 'Remove Feature') }}">
                                            <i class='bx bx-trash'></i>
                                        </button>
                                    </div>

                                    <div class="row">
                                        <div class="mb-3 col-md-4">
                                            <label class="form-label">{{ get_label('feature_title', 'Feature Title') }}</label>
                                            <input type="text" class="form-control" name="features[{{ $index }}][title]"
                                                placeholder="{{ get_label('feature_title_placeholder', 'e.g., Project Management') }}"
                                                value="{{ $feature['title'] ?? '' }}" required>
                                            @error("features.{$index}.title")
                                                <p class="text-danger text-xs mt-1">{{ $message }}</p>
                                            @enderror
                                        </div>
                                        <div class="mb-3 col-md-8">
                                            <label
                                                class="form-label">{{ get_label('feature_description', 'Feature Description') }}</label>
                                            <textarea class="form-control" name="features[{{ $index }}][description]" rows="2"
                                                placeholder="{{ get_label('feature_description_placeholder', 'Brief description of the feature...') }}"
                                                required>{{ $feature['description'] ?? '' }}</textarea>
                                            @error("features.{$index}.description")
                                                <p class="text-danger text-xs mt-1">{{ $message }}</p>
                                            @enderror
                                        </div>
                                        <div class="mb-3 col-md-6">
                                            <label class="form-label">
                                                {{ get_label('feature_icon', 'Feature Icon') }}
                                                @if (!empty($feature['icon']))
                                                    <a data-bs-toggle="tooltip" data-bs-placement="right"
                                                        data-bs-original-title="{{ get_label('view_current_icon', 'View current icon') }}"
                                                        href="{{ asset($feature['icon']) }}"
                                                        data-lightbox="feature_icon_{{ $index }}"
                                                        data-title="{{ get_label('current_icon', 'Current Icon') }}">
                                                        <i class='bx bx-show-alt'></i>
                                                    </a>
                                                @endif
                                            </label>
                                            <input type="file" accept="image/*" class="form-control"
                                                name="features[{{ $index }}][icon]" {{ empty($feature['icon']) ? 'required' : '' }}>
                                            <small class="text-muted">
                                                {{  'Supported formats:  SVG (max 2MB)' }}
                                            </small>
                                            @error("features.{$index}.icon")
                                                <p class="text-danger text-xs mt-1">{{ $message }}</p>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="feature-item border rounded p-3 mb-3 position-relative" data-index="0">
                                    <!-- Header with title and remove button -->
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <h6 class="mb-0 text-primary">{{ get_label('feature_item', 'Feature Item') }} 1</h6>
                                        <button type="button" class="btn btn-danger btn-sm remove-feature"
                                            onclick="removeFeature(this)" data-bs-toggle="tooltip"
                                            data-bs-original-title="{{ get_label('remove_feature', 'Remove Feature') }}">
                                            <i class='bx bx-trash'></i>
                                        </button>
                                    </div>

                                    <div class="row">
                                        <div class="mb-3 col-md-4">
                                            <label class="form-label">{{ get_label('feature_title', 'Feature Title') }}</label>
                                            <input type="text" class="form-control" name="features[0][title]"
                                                placeholder="{{ get_label('feature_title_placeholder', 'e.g., Project Management') }}"
                                                required>
                                            @error('features.0.title')
                                                <p class="text-danger text-xs mt-1">{{ $message }}</p>
                                            @enderror
                                        </div>
                                        <div class="mb-3 col-md-8">
                                            <label
                                                class="form-label">{{ get_label('feature_description', 'Feature Description') }}</label>
                                            <textarea class="form-control" name="features[0][description]" rows="2"
                                                placeholder="{{ get_label('feature_description_placeholder', 'Brief description of the feature...') }}"
                                                required></textarea>
                                            @error('features.0.description')
                                                <p class="text-danger text-xs mt-1">{{ $message }}</p>
                                            @enderror
                                        </div>
                                        <div class="mb-3 col-md-6">
                                            <label class="form-label">{{ get_label('feature_icon', 'Feature Icon') }}</label>
                                            <input type="file" accept="image/*" class="form-control" name="features[0][icon]">
                                            <small class="text-muted">
                                                {{ get_label('supported_formats', 'Supported formats: JPG, PNG, SVG') }}
                                            </small>
                                            @error('features.0.icon')
                                                <p class="text-danger text-xs mt-1">{{ $message }}</p>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            @endforelse
                        </div>
                    </div>

                    <!-- Submit and Cancel Buttons -->
                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary me-2" id="submit_btn">
                            {{ get_label('update', 'Update') }}
                        </button>
                        <button type="reset" class="btn btn-outline-secondary">
                            {{ get_label('cancel', 'Cancel') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
    <script src="{{ asset('assets/js/pages/frontend-general-settings.js') }}"></script>
@endsection