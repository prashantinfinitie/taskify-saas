@extends('front-end.layout')
@section('title')
    <?= get_label('terms_and_conditions', 'Terms and Conditions') ?>
@endsection
@section('content')


    <section class="section-py mt-6 py-5">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-12">
                    <h1 class="fs-4 fs-md-5 fs-xxl-4">
                        {{ get_label('terms_and_conditions', 'Terms and Conditions') }}</h1>
                    <p class = "font-size-18">
                        {!! $terms_and_conditions['terms_and_conditions'] !!}
                    </p>
                </div>
            </div>
        </div>
    </section>

@endsection
