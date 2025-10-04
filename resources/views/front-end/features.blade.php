@extends('front-end.layout')
@section('title')
    {{ get_label('features', 'Features') }}
@endsection
@section('content')
      

    <section class="section" id="features">
        <div class="container position-relative z-index-2">
            <div class="col-md-8 mx-auto text-center feature-section fade-up">
                <span class="badge bg-gradient-dark mb-3">{{ get_label('features', 'Features') }}</span>
                <h3 class="feature-title">
                    {{ get_label('taskify_features_heading', 'Every Feature Your Team Needs To Complete Work Faster') }}
                </h3>
                <p class="text-center feature-description mt-3 mb-5">
                    {{ get_label('taskify_features_subheading', 'Streamline your team\'s workflow and boost productivity with our comprehensive set of features.') }}
                </p>
            </div>

            <div class="row mt-4">
                @if (!empty($frontend_general_settings['features']))
                    @foreach ($frontend_general_settings['features'] as $index => $feature)
                        @if ($index % 4 == 0 && $index > 0)
                            </div>
                            <div class="row mt-4">
                        @endif
                        <div class="col-md-6 col-lg-3 mb-4 fade-up">
                            <div class="glass-card card-body text-center p-4">
                                @if (!empty($feature['icon']))
                                    <img src="{{ asset($feature['icon']) }}" alt="{{ $feature['title'] }}" class="icon-size"
                                        loading="lazy" />
                                @else
                                    <img src="{{ asset('assets/front-end/img/icons/task.svg') }}" alt="{{ $feature['title'] }}"
                                        class="icon-size" loading="lazy" />
                                @endif
                                <h4 class="mt-3 mb-2 feature-title">{{ $feature['title'] }}</h4>
                                <p class="feature-description">
                                    {{ $feature['description'] }}
                                </p>
                            </div>
                        </div>
                        @if ($index == count($frontend_general_settings['features']) - 1)
                            </div>
                        @endif
                    @endforeach
                @else
                <div class="row mt-4">
                    <div class="col-12 text-center">
                        <p class="feature-description">No features have been defined yet.</p>
                    </div>
                </div>
            @endif
        </div>
        </div>
    </section>
@endsection