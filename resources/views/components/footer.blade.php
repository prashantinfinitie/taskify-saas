<!-- Footer -->
<div id="section-not-to-print">
    <footer class="content-footer footer bg-footer-theme mt-2 container-fluid">
        <div class="container-fluid d-flex flex-wrap justify-content-between flex-md-row flex-column">
            <!-- Left-hand side: Copyright, Version, and Support link -->
            <div class="mb-md-0 d-flex align-items-start">
                © {{ date('Y') }},  {!! $general_settings['footer_text'] !!}
                
                <p class="ms-2 footer-text">v{{ get_current_version() }}</p>

                <!-- Support Link -->
                @hasRole('admin')
                <a href="{{ route('support.index') }}" class="ms-3 text-decoration-none">
                    <p class="ms-2 footer-text text-primary">{{ get_label('support', 'Support') }}</p>
                </a>
                @endhasRole
            </div>
        </div>
    </footer>
</div>
<!-- / Footer -->
