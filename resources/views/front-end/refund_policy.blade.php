@extends('front-end.layout')
@section('title')
    <?= get_label('refund_policy', 'Refund Policy') ?>
@endsection
@section('content')

    <section class="section">
        <div class="container mt-6 py-5">
            <div class="row align-items-center">
                <div class="col-md-12">
                    <h1 class="fs-4 fs-md-5 fs-xxl-4">
                        {{ get_label('refund_policy', 'Refund Policy') }}</h1>
                    <p class = "font-size-18 text-black">
                        {!! $refund_policy['refund_policy'] !!}
                    </p>
                </div>
            </div>
        </div>
    </section>

@endsection
