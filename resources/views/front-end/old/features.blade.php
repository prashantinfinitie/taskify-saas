@extends('front-end.layout')
@section('title')
    <?= get_label('features', 'Features') ?>
@endsection
@section('content')
    <section class="section" id="features">
        <div class="bg-gradient-primary position-relative border-radius-xl w-100">
            <img src="/assets/front-end/img/gallery/waves-white.svg" alt="pattern-lines"
                class="position-absolute start-0 top-md-0 w-100 opacity-7">
            <div class="container pb-lg-9 pb-7 pt-7 postion-relative z-index-2">
                <div class="row">
                    <div class="col-md-8 mx-auto text-center">
                        <span class="badge bg-gradient-dark mb-2">{{ get_label('features', 'Features') }}</span>
                        <h3 class="text-white">
                            {{ get_label('taskify_features_heading', $general_settings['company_title'] . ' Powerful Features for Efficient Project Management') }}
                        </h3>
                        <p class="text-center text-white fs-0 fs-md-1 mt-3 mb-3">
                            {{ get_label('taskify_features_subheading', 'Streamline your team\'s workflow and boost productivity with our comprehensive set of features.') }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
        <div class="mt-lg-n8 mt-n7">
            <div class="container">
                <div class="row">
                    <div class="col-md-12 mb-4">
                        <div class="card">
                            <div class="card-body text-center">
                                @if (!empty($frontend_general_settings['features']))
                                    @foreach ($frontend_general_settings['features'] as $index => $feature)
                                        @if ($index % 4 == 0)
                                                @if ($index > 0)
                                                    </div>
                                                @endif
                                            <div class="row mb-4 mt-6">
                                        @endif
                                        <div class="col-md-6 col-lg-3 text-center">
                                            @if (!empty($feature['icon']))
                                                <img src="{{ asset($feature['icon']) }}" alt="{{ $feature['title'] }}"
                                                    class="icon-size" />
                                            @else
                                                <img src="{{ asset('assets/front-end/img/icons/task.svg') }}"
                                                    alt="{{ $feature['title'] }}" class="icon-size" />
                                            @endif
                                            <h4 class="mt-3 mb-2">{{ $feature['title'] }}</h4>
                                            <p class="text-muted">
                                                {{ $feature['description'] }}
                                            </p>
                                        </div>
                                        <!-- Close the last row if it's the last feature -->
                                        @if ($index == count($frontend_general_settings['features']) - 1)
                                            </div>
                                        @endif
                                    @endforeach
                                @else
                                <div class="row mb-4 mt-6">
                                    <div class="col-12 text-center">
                                        <p class="text-muted">No features have been defined yet.</p>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
        </div>
    </section>
@endsection