@extends('front-end.layout')
@section('title')
    {{ get_label('home', 'Home') }}
@endsection
@section('content')
                <link rel="stylesheet" href='assets/lightbox/lightbox.min.css'>
                </link>
                <header class="">
                    <div class="page-header min-vh-90">
                        <div class="container">
                            <div class="row">
                                <div class="col-lg-6 col-md-7 d-flex justify-content-center flex-column">
                                    <h1 class="text-gradient text-primary"> {{ $frontend_general_settings['company_title'] }}</h1>
                                    <h1 class="mb-4"></h1>
                                    <p class="lead pe-2">
                                        {{ !empty($frontend_general_settings['company_description'])
        ? $frontend_general_settings['company_description']
        : get_label('homeDesc1', 'Unleash peak productivity with this system, your one-stop cloud-based
                                            project management platform. Streamline workflows, boost team collaboration, and stay ahead of
                                            deadlines. This system empowers you to effortlessly create tasks, assign them to team members,
                                            and track progress in real-time.') 
                                        }}
                                    </p>

                                    <div class="buttons">
                                        <a href="{{ route('login') }}"
                                            class="btn bg-gradient-primary mt-4">{{ get_label('get_started', 'Get Started') }}</a>
                                        <a href="{{ route('frontend.contact_us') }}"
                                            class="btn text-primary mt-4 shadow-none">{{ get_label('contact_us', 'Contact Us') }}</a>
                                    </div>
                                </div>
                                <div class="col-lg-6 col-md-7 d-flex justify-content-center flex-column">
                                    <div class="img-fluid">
                                        <lottie-player src="/assets/front-end/img/gallery/Animation - 1712315186739.json"
                                            background="transparent" speed="1" loop autoplay>
                                        </lottie-player>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </header>
                <section class="mt-2">
                    <div class="container">
                        <div class="row align-items-center">
                            <div class="col-lg-9">
                                <div class="row justify-content-start">
                                    <div class="col-md-6">
                                        <div class="info">
                                            <div class="icon icon-md">
                                                <svg width="25px" height="25px" viewBox="0 0 40 40" version="1.1"
                                                    xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
                                                    <title>spaceship</title>
                                                    <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                                                        <g transform="translate(-1720.000000, -592.000000)" fill="#2D31FA"
                                                            fill-rule="nonzero">
                                                            <g transform="translate(1716.000000, 291.000000)">
                                                                <g transform="translate(4.000000, 301.000000)">
                                                                    <path
                                                                        d="M39.3,0.706666667 C38.9660984,0.370464027 38.5048767,0.192278529 38.0316667,0.216666667 C14.6516667,1.43666667 6.015,22.2633333 5.93166667,22.4733333 C5.68236407,23.0926189 5.82664679,23.8009159 6.29833333,24.2733333 L15.7266667,33.7016667 C16.2013871,34.1756798 16.9140329,34.3188658 17.535,34.065 C17.7433333,33.98 38.4583333,25.2466667 39.7816667,1.97666667 C39.8087196,1.50414529 39.6335979,1.04240574 39.3,0.706666667 Z M25.69,19.0233333 C24.7367525,19.9768687 23.3029475,20.2622391 22.0572426,19.7463614 C20.8115377,19.2304837 19.9992882,18.0149658 19.9992882,16.6666667 C19.9992882,15.3183676 20.8115377,14.1028496 22.0572426,13.5869719 C23.3029475,13.0710943 24.7367525,13.3564646 25.69,14.31 C26.9912731,15.6116662 26.9912731,17.7216672 25.69,19.0233333 L25.69,19.0233333 Z">
                                                                    </path>
                                                                    <path
                                                                        d="M1.855,31.4066667 C3.05106558,30.2024182 4.79973884,29.7296005 6.43969145,30.1670277 C8.07964407,30.6044549 9.36054508,31.8853559 9.7979723,33.5253085 C10.2353995,35.1652612 9.76258177,36.9139344 8.55833333,38.11 C6.70666667,39.9616667 0,40 0,40 C0,40 0,33.2566667 1.855,31.4066667 Z">
                                                                    </path>
                                                                    <path
                                                                        d="M17.2616667,3.90166667 C12.4943643,3.07192755 7.62174065,4.61673894 4.20333333,8.04166667 C3.31200265,8.94126033 2.53706177,9.94913142 1.89666667,11.0416667 C1.5109569,11.6966059 1.61721591,12.5295394 2.155,13.0666667 L5.47,16.3833333 C8.55036617,11.4946947 12.5559074,7.25476565 17.2616667,3.90166667 L17.2616667,3.90166667 Z"
                                                                        opacity="0.598539807"></path>
                                                                    <path
                                                                        d="M36.0983333,22.7383333 C36.9280725,27.5056357 35.3832611,32.3782594 31.9583333,35.7966667 C31.0587397,36.6879974 30.0508686,37.4629382 28.9583333,38.1033333 C28.3033941,38.4890431 27.4704606,38.3827841 26.9333333,37.845 L23.6166667,34.53 C28.5053053,31.4496338 32.7452344,27.4440926 36.0983333,22.7383333 L36.0983333,22.7383333 Z"
                                                                        id="color-3" opacity="0.598539807"></path>
                                                                </g>
                                                            </g>
                                                        </g>
                                                    </g>
                                                </svg>
                                            </div>
                                            <h5>
                                                {{ !empty($frontend_general_settings['feature1_title'])
        ? $frontend_general_settings['feature1_title']
        : get_label('streamline_projects', 'Streamline Your Projects with') . ' ' . $general_settings['company_title'] 
                                                }}
                                            </h5>
                                                {{-- {{ $general_settings['company_title'] }}</h5> --}}
                                            <p class="asap-sans-serif">
                                                {{ !empty($frontend_general_settings['feature1_description'])
        ? $frontend_general_settings['feature1_description']
        : get_label(
            'streamlineProjectDesc',
            'Take control of your projects and boost team productivity with ' .
            $general_settings['company_title'] .
            ', the all-in-one project management and task management solution. Our cloud-based platform empowers you to effortlessly organize projects, collaborate with your team, and track progress – all in one place.'
        ) 
                                                }}
                                            </p>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="info">
                                            <div class="icon icon-md">
                                                <svg width="25px" height="25px" viewBox="0 0 42 42" version="1.1"
                                                    xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
                                                    <title>briefcase-24</title>
                                                    <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                                                        <g transform="translate(-2170.000000, -292.000000)" fill="#2D31FA"
                                                            fill-rule="nonzero">
                                                            <g transform="translate(1716.000000, 291.000000)">
                                                                <g id="briefcase-24" transform="translate(454.000000, 1.000000)">
                                                                    <path
                                                                        d="M26.25,31.5 L26.25,35 L15.75,35 L15.75,31.5 L1.75,31.5 L1.75,40.25 C1.75,41.216 2.534,42 3.5,42 L38.5,42 C39.466,42 40.25,41.216 40.25,40.25 L40.25,31.5 L26.25,31.5 Z">
                                                                    </path>
                                                                    <path
                                                                        d="M40.25,7 L29.75,7 L29.75,1.75 C29.75,0.784 28.966,0 28,0 L14,0 C13.034,0 12.25,0.784 12.25,1.75 L12.25,7 L1.75,7 C0.784,7 0,7.784 0,8.75 L0,26.25 C0,27.216 0.784,28 1.75,28 L15.75,28 L15.75,22.75 L26.25,22.75 L26.25,28 L40.25,28 C41.216,28 42,27.216 42,26.25 L42,8.75 C42,7.784 41.216,7 40.25,7 Z M26.25,7 L15.75,7 L15.75,3.5 L26.25,3.5 L26.25,7 Z"
                                                                        opacity="0.6"></path>
                                                                </g>
                                                            </g>
                                                        </g>
                                                    </g>
                                                </svg>
                                            </div>
                                            <h5>
                                                {{ !empty($frontend_general_settings['feature2_title'])
        ? $frontend_general_settings['feature2_title']
        : get_label('effortless_organization', 'Effortless Organization') 
                                                }}
                                            </h5>

                                            <p class="asap-sans-serif">
                                                {{ !empty($frontend_general_settings['feature2_description'])
        ? $frontend_general_settings['feature2_description']
        : $general_settings['company_title'] . ' ' . get_label(
            'effortlessOrganizationDesc',
            'provides a centralized hub to create, manage, and track all your projects. Say goodbye to scattered tasks and missed deadlines – our intuitive interface keeps everything organized and accessible.'
        ) 
                                                }}
                                            </p>

                                        </div>
                                    </div>
                                </div>
                                <div class="row justify-content-start">
                                    <div class="col-md-6">
                                        <div class="info">
                                            <div class="icon icon-md">
                                                <svg width="25px" height="25px" viewBox="0 0 45 44" version="1.1"
                                                    xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
                                                    <title>map-big</title>
                                                    <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                                                        <g transform="translate(-2321.000000, -593.000000)" fill="#2D31FA"
                                                            fill-rule="nonzero">
                                                            <g transform="translate(1716.000000, 291.000000)">
                                                                <g id="map-big" transform="translate(605.000000, 302.000000)">
                                                                    <polygon
                                                                        points="31.875 0.71625 24.375 4.46625 24.375 40.53375 31.875 36.78375">
                                                                    </polygon>
                                                                    <polygon
                                                                        points="20.625 4.46625 13.125 0.71625 13.125 36.78375 20.625 40.53375">
                                                                    </polygon>
                                                                    <path
                                                                        d="M9.375,0.81375 L0.909375,5.893125 C0.346875,6.230625 0,6.84 0,7.5 L0,43.125 L9.375,37.06125 L9.375,0.81375 Z"
                                                                        opacity="0.70186942"></path>
                                                                    <path
                                                                        d="M44.090625,5.893125 L35.625,0.81375 L35.625,37.06125 L45,43.125 L45,7.5 C45,6.84 44.653125,6.230625 44.090625,5.893125 Z"
                                                                        opacity="0.70186942"></path>
                                                                </g>
                                                            </g>
                                                        </g>
                                                    </g>
                                                </svg>
                                            </div>
                                            <h5>
                                                {{ !empty($frontend_general_settings['feature3_title'])
        ? $frontend_general_settings['feature3_title']
        : get_label('seamless_collaboration', 'Seamless Collaboration') 
                                                }}
                                            </h5>

                                            <p class="asap-sans-serif">
                                                {{ !empty($frontend_general_settings['feature3_description'])
        ? $frontend_general_settings['feature3_description']
        : get_label(
            'seamlessCollaborationDesc',
            'Foster a collaborative work environment with ' .
            $general_settings['company_title'] .
            '. Assign tasks, share files, and communicate effectively with your team in real-time. Ensure everyone is on the same page and working towards a common goal.'
        ) 
                                                }}
                                            </p>

                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="info">
                                            <div class="icon icon-md">
                                                <svg width="25px" height="25px" viewBox="0 0 42 44" version="1.1"
                                                    xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
                                                    <title>time-alarm</title>
                                                    <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                                                        <g transform="translate(-2319.000000, -440.000000)" fill="#2D31FA"
                                                            fill-rule="nonzero">
                                                            <g transform="translate(1716.000000, 291.000000)">
                                                                <g id="time-alarm" transform="translate(603.000000, 149.000000)">
                                                                    <path
                                                                        d="M18.8086957,4.70034783 C15.3814926,0.343541521 9.0713063,-0.410050841 4.7145,3.01715217 C0.357693695,6.44435519 -0.395898667,12.7545415 3.03130435,17.1113478 C5.53738466,10.3360568 11.6337901,5.54042955 18.8086957,4.70034783 L18.8086957,4.70034783 Z"
                                                                        opacity="0.6"></path>
                                                                    <path
                                                                        d="M38.9686957,17.1113478 C42.3958987,12.7545415 41.6423063,6.44435519 37.2855,3.01715217 C32.9286937,-0.410050841 26.6185074,0.343541521 23.1913043,4.70034783 C30.3662099,5.54042955 36.4626153,10.3360568 38.9686957,17.1113478 Z"
                                                                        opacity="0.6"></path>
                                                                    <path
                                                                        d="M34.3815652,34.7668696 C40.2057958,27.7073059 39.5440671,17.3375603 32.869743,11.0755718 C26.1954189,4.81358341 15.8045811,4.81358341 9.13025701,11.0755718 C2.45593289,17.3375603 1.79420418,27.7073059 7.61843478,34.7668696 L3.9753913,40.0506522 C3.58549114,40.5871271 3.51710058,41.2928217 3.79673036,41.8941824 C4.07636014,42.4955431 4.66004722,42.8980248 5.32153275,42.9456105 C5.98301828,42.9931963 6.61830436,42.6784048 6.98113043,42.1232609 L10.2744783,37.3434783 C16.5555112,42.3298213 25.4444888,42.3298213 31.7255217,37.3434783 L35.0188696,42.1196087 C35.6014207,42.9211577 36.7169135,43.1118605 37.53266,42.5493622 C38.3484064,41.9868639 38.5667083,40.8764423 38.0246087,40.047 L34.3815652,34.7668696 Z M30.1304348,25.5652174 L21,25.5652174 C20.49574,25.5652174 20.0869565,25.1564339 20.0869565,24.6521739 L20.0869565,15.5217391 C20.0869565,15.0174791 20.49574,14.6086957 21,14.6086957 C21.50426,14.6086957 21.9130435,15.0174791 21.9130435,15.5217391 L21.9130435,23.7391304 L30.1304348,23.7391304 C30.6346948,23.7391304 31.0434783,24.1479139 31.0434783,24.6521739 C31.0434783,25.1564339 30.6346948,25.5652174 30.1304348,25.5652174 Z">
                                                                    </path>
                                                                </g>
                                                            </g>
                                                        </g>
                                                    </g>
                                                </svg>
                                            </div>
                                            <h5>
                                                {{ !empty($frontend_general_settings['feature4_title'])
        ? $frontend_general_settings['feature4_title']
        : get_label('visualize_project_health', 'Visualize Project Health') 
                                                }}
                                            </h5>

                                            <p class="asap-sans-serif">
                                                {{ !empty($frontend_general_settings['feature4_description'])
        ? $frontend_general_settings['feature4_description']
        : get_label(
            'visualizeProjectDesc',
            'Get insightful dashboards and reports to monitor project performance and identify areas for improvement.'
        ) 
                                                }}
                                            </p>

                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-3 ms-auto">
                                <div class="card">
                                    <img class="card-img-top" src="{{ !empty($frontend_general_settings['team_collab_image']) ? asset($frontend_general_settings['team_collab_image'])  :  asset('/assets/front-end/img/gallery/teamCollab.jpg') }}">
                                    <div class="position-relative overflow-hidden">
                                        <div class="position-absolute w-100 z-index-1 top-0">
                                            <svg class="waves waves-sm" xmlns="http://www.w3.org/2000/svg"
                                                xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 24 150 40"
                                                preserveAspectRatio="none" shape-rendering="auto">
                                                <defs>
                                                    <path id="card-wave"
                                                        d="M-160 44c30 0 58-18 88-18s 58 18 88 18 58-18 88-18 58 18 88 18 v44h-352z">
                                                    </path>
                                                </defs>
                                                <g class="moving-waves">
                                                    <use xlink:href="#card-wave" x="48" y="-1" fill="rgba(255,255,255,0.30"></use>
                                                    <use xlink:href="#card-wave" x="48" y="3" fill="rgba(255,255,255,0.35)"></use>
                                                    <use xlink:href="#card-wave" x="48" y="5" fill="rgba(255,255,255,0.25)"></use>
                                                    <use xlink:href="#card-wave" x="48" y="8" fill="rgba(255,255,255,0.20)"></use>
                                                    <use xlink:href="#card-wave" x="48" y="13" fill="rgba(255,255,255,0.15)"></use>
                                                    <use xlink:href="#card-wave" x="48" y="16" fill="rgba(255,255,255,0.99)"></use>
                                                </g>
                                            </svg>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                       <h4>
                                            {{ !empty($frontend_general_settings['team_collaboration_title'])
        ? $frontend_general_settings['team_collaboration_title']
        : get_label('team_collaboration', 'Team Collaboration') 
                                            }}
                                        </h4>

                                        <p class="asap-sans-serif">
                                            {{ !empty($frontend_general_settings['team_collaboration_description'])
        ? $frontend_general_settings['team_collaboration_description']
        : get_label(
            'teamCollabDesc',
            'Enhance team productivity and communication with our intuitive collaboration platform, facilitating seamless coordination and information sharing.'
        ) 
                                            }}
                                        </p>

                                        <a href="{{ route('frontend.features') }}" class="text-primary icon-move-right">
                                            {{ get_label('learn_more', 'Learn More.') }}
                                            <i class="fas fa-arrow-right ms-1 text-xs" aria-hidden="true"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
                <section class="py-5">
                    <div class="container">
                        <div class="row">
                            <div class="col-lg-6">
                                <h3 class="text-gradient text-primary mb-0 mt-2">
                                    {{  get_label('read_more_about_us', 'Read More About Us') }}</h3>
                                <h3>{{ get_label('maximizing_efficiency', 'Maximizing Efficiency') }}</h3>
                                <p>{{ get_label(
        'maxEffiencyDesc',
        'Efficiency is paramount in project management. We streamline
                                    processes, foster teamwork, and minimize inefficiencies, ensuring smooth project execution and
                                    success.',
    ) }}
                                </p>
                                <a href="{{ route('frontend.about_us') }}"
                                    class="text-primary icon-move-right">{{ get_label('read_more_about_us', 'Read more About Us') }}
                                    <i class="fas fa-arrow-right ms-1 text-sm" aria-hidden="true"></i>
                                </a>
                            </div>
                            <div class="col-lg-6 mt-lg-0 ps-lg-0 mt-5 ps-0">
                                <div class="info-horizontal p-3">
                                    <div class="icon icon-shape rounded-circle text-center shadow">
                                        <img src="{{ !empty($frontend_general_settings['info_card1_icon_file']) ? asset($frontend_general_settings['info_card1_icon_file']) : asset('assets/front-end/img/icons/project-management.png') }}" class="icon-size"
                                            alt="{{ get_label('project_management', 'Project Management') }}" />
                                    </div>
                                    <div class=" ps-3">
                                        <p class="mb-0 ms-5">
                                            {{  get_label(
        'project_management_desc',
        'Create and manage multiple projects with ease,
                                            ensuring seamless collaboration and organization.',
    ) }}
                                        </p>
                                    </div>
                                </div>
                                <div class="info-horizontal p-3">
                                    <div class="icon icon-shape rounded-circle text-center shadow">
                                        <img src="{{ !empty($frontend_general_settings['info_card2_icon_file']) ? asset($frontend_general_settings['info_card2_icon_file']) : asset('assets/front-end/img/icons/task-tracking.png') }}" class="icon-size"
                                            alt="{{ get_label('task_tracking', 'Task Tracking') }}">
                                    </div>
                                    <div class=" ps-3">
                                        <p class="mb-0 ms-5">
                                            {{ get_label(
        'task_tracking_desc',
        'Assign, prioritize, and track tasks efficiently, keeping
                                            your team on top of their workload.',
    ) }}
                                        </p>
                                    </div>
                                </div>
                                <div class="info-horizontal p-3">
                                    <div class="icon icon-shape rounded-circle text-center shadow">
                                        <img src="{{ !empty($frontend_general_settings['info_card3_icon_file']) ? asset($frontend_general_settings['info_card3_icon_file']) : asset('assets/front-end/img/icons/7.png') }}" class="icon-size"
                                            alt="{{ get_label('user_management', 'User Management') }}" />
                                    </div>
                                    <div class=" ps-3">
                                        <p class="mb-0 ms-5">
                                            {{ get_label(
        'user_management_desc',
        'Manage user roles, permissions, and access levels,
                                            ensuring secure collaboration and data privacy.',
    ) }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>


                @if (count($plans) > 0)
                                            <section class="section-py">
                                                <div class="bg-gradient-primary position-relative border-radius-xl w-100">
                                                    <img src="/assets/front-end/img/gallery/waves-white.svg" alt="pattern-lines"
                                                        class="position-absolute top-md-0 w-100 opacity-6 start-0">
                                                    <div class="pb-lg-9 position-relative z-index-2 container pb-8 pt-7">
                                                        <div class="row z-index-frontend mb-5">
                                                            <div class="col-md-8 mx-auto text-center">
                                                                <span class="badge bg-gradient-dark mb-2">{{ get_label('pricing', 'Pricing') }}</span>
                                                                <h3 class="text-white">{{ get_label('see_our_pricing', 'See our pricing') }}</h3>
                                                                <p class="text-white">
                                                                    {{ get_label(
                        'seePricingDesc',
                        'You have Free Unlimited Updates and Premium Support on each package.',
                    ) }}
                                                                    <a href="{{ route('frontend.pricing') }}"
                                                                        class="small icon-move-right">{{ get_label('explore_more_plans', 'Explore More Plans') }}
                                                                        <i class="fa fa-arrow-right"></i> </a>
                                                                </p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="mt-n9">
                                                    @if ($plans !== null)
                                                        <div class="container">
                                                            @php
                                                                // Check if there are plans with prices for each frequency or free plans
                                                                $hasMonthlyPlans = $plans->where('monthly_price', '>', 0)->count() > 0 || $plans->where('plan_type', 'free')->count() > 0;
                                                                $hasYearlyPlans = $plans->where('yearly_price', '>', 0)->count() > 0 || $plans->where('plan_type', 'free')->count() > 0;
                                                                $hasLifetimePlans = $plans->where('lifetime_price', '>', 0)->count() > 0 || $plans->where('plan_type', 'free')->count() > 0;

                                                                // Count how many tabs we have
                                                                $tabCount = ($hasMonthlyPlans ? 1 : 0) + ($hasYearlyPlans ? 1 : 0) + ($hasLifetimePlans ? 1 : 0);

                                                                // Set default active tab
                                                                $activeTab = $hasMonthlyPlans ? 'monthly' : ($hasYearlyPlans ? 'yearly' : 'lifetime');
                                                            @endphp

                                                            @if ($tabCount > 1)
                                                                <div class="row">
                                                                    <div class="col-md-4 col-12 mx-auto text-center">
                                                                        <div class="nav-wrapper">
                                                                            <ul class="nav nav-pills nav-fill position-relative flex-row p-1" id="tabs-pricing-4"
                                                                                role="tablist">
                                                                                @if ($hasMonthlyPlans)
                                                                                    <li class="nav-item">
                                                                                        <a class="nav-link {{ $activeTab == 'monthly' ? 'active' : '' }} mb-0" id="tabs-iconpricing-tab-1"
                                                                                            data-bs-toggle="tab" href="#monthly" role="tab"
                                                                                            aria-controls="monthly" aria-selected="{{ $activeTab == 'monthly' ? 'true' : 'false' }}">
                                                                                            {{ get_label('monthly', 'Monthly') }}
                                                                                        </a>
                                                                                    </li>
                                                                                @endif
                                                                                @if ($hasYearlyPlans)
                                                                                    <li class="nav-item">
                                                                                        <a class="nav-link {{ $activeTab == 'yearly' ? 'active' : '' }} mb-0" id="tabs-iconpricing-tab-2" data-bs-toggle="tab"
                                                                                            href="#yearly" role="tab" aria-controls="yearly"
                                                                                            aria-selected="{{ $activeTab == 'yearly' ? 'true' : 'false' }}">
                                                                                            {{ get_label('annual', 'Annual') }}
                                                                                        </a>
                                                                                    </li>
                                                                                @endif
                                                                                @if ($hasLifetimePlans)
                                                                                    <li class="nav-item">
                                                                                        <a class="nav-link {{ $activeTab == 'lifetime' ? 'active' : '' }} mb-0" id="tabs-iconpricing-tab-3" data-bs-toggle="tab"
                                                                                            href="#lifetime" role="tab" aria-controls="lifetime"
                                                                                            aria-selected="{{ $activeTab == 'lifetime' ? 'true' : 'false' }}">
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
                                                                @if ($hasMonthlyPlans)
                                                                    <div class="tab-pane {{ $activeTab == 'monthly' ? 'active' : '' }}" id="monthly">
                                                                        <div class="row mt-5">
                                                                            @foreach ($plans->where('monthly_price', '>', 0)->merge($plans->where('plan_type', 'free'))->sortBy(function ($plan) {
                                                                                                                                return $plan->plan_type === 'free' ? 0 : 1;
                                                                                                                            })->unique('id') as $plan)
                                                                                                                                                                                            <div class="col-md-4 col-lg-4 col-sm-6 mb-lg-0 mb-4 gap-2 pb-3">
                                                                                                                                                                                                <div class="card shadow-lg">
                                                                                                                                                                                                    <div class="card-header text-sm-start px-4 pb-3 pt-4 text-center">
                                                                                                                                                                                                        <h5 class="mb-1">{{ $plan->name }}</h5>
                                                                                                                                                                                                        <h3 class="font-weight-bolder mt-3">
                                                                                                                                                                                                            <small class="text-secondary font-weight-bold">
                                                                                                                                                                                                                @if ($plan->monthly_discounted_price > 0 && $plan->monthly_discounted_price < $plan->monthly_price)
                                                                                                                                                                                                                            <strike>{{ format_currency($plan->monthly_price) }}</strike>
                                                                                                                                                                                                                    </small>
                                                                                                                                                                                                                    {{ format_currency($plan->monthly_discounted_price) }} <small
                                                                                                                                                                                                                        class="text-secondary font-weight-bold text-sm">/
                                                                                                                                                                                                                        {{ get_label('monthly_price', 'Monthly Price') }}</small>
                                                                                                                                                                                                                @else
                                                                                                                                                                                                                    {{ format_currency($plan->monthly_price) }} <small
                                                                                                                                                                                                                        class="text-secondary font-weight-bold text-sm">/
                                                                                                                                                                                                                        {{ get_label('monthly_price', 'Monthly Price') }}
                                                                                                                                                                                                                    </small>
                                                                                                                                                                                                                @endif
                                                                                                                                                                                                        </small>
                                                                                                                                                                                                        </h3>
                                                                                                                                                                                                        <p class="text-lighter small text-black-50">{{ $plan->description }}</p>
                                                                                                                                                                                                        @if ($plan->monthly_price == 0)
                                                                                                                                                                                                            <a href="{{ route('login') }}"
                                                                                                                                                                                                                class="btn btn-outline-primary btn-sm w-100 border-radius-md mb-2 mt-4 text-center">{{ get_label('buy_now', 'Buy now') }}</a>
                                                                                                                                                                                                        @else
                                                                                                                                                                                                            <a href="{{ route('login') }}"
                                                                                                                                                                                                                class="btn btn-sm bg-gradient-dark w-100 border-radius-md mb-2 mt-4 text-center">{{ get_label('buy_now', 'Buy now') }}</a>
                                                                                                                                                                                                        @endif
                                                                                                                                                                                                    </div>
                                                                                                                                                                                                    <hr class="horizontal dark my-0">
                                                                                                                                                                                                    <div class="card-body pt-0">
                                                                                                                                                                                                        <div class="justify-content-start d-flex px-2 py-1">
                                                                                                                                                                                                            <ul class="list-unstyled mb-4">
                                                                                                                                                                                                                <li class="mb-2">
                                                                                                                                                                                                                    <i class="fas fa-check-circle text-primary text-gradient me-2"></i>
                                                                                                                                                                                                                    <span class="fw-semibold">{{ get_label('max_projects', 'Max Projects') }}:</span>
                                                                                                                                                                                                                    {!! $plan->max_projects == -1
                                                                                    ? '<span class="fw-semibold">Unlimited</span>'
                                                                                    : '<span class="fw-semibold">' . $plan->max_projects . '</span>' !!}
                                                                                                                                                                                                                </li>
                                                                                                                                                                                                                <li class="mb-2">
                                                                                                                                                                                                                    <i class="fas fa-check-circle text-primary text-gradient me-2"></i>
                                                                                                                                                                                                                    <span class="fw-semibold">{{ get_label('max_clients', 'Max Clients') }}:</span>
                                                                                                                                                                                                                    {!! $plan->max_clients == -1
                                                                                    ? '<span class="fw-semibold">Unlimited</span>'
                                                                                    : '<span class="fw-semibold">' . $plan->max_clients . '</span>' !!}
                                                                                                                                                                                                                </li>
                                                                                                                                                                                                                <li class="mb-2">
                                                                                                                                                                                                                    <i class="fas fa-check-circle text-primary text-gradient me-2"></i>
                                                                                                                                                                                                                    <span
                                                                                                                                                                                                                        class="fw-semibold">{{ get_label(
                                                                                    'max_team_members',
                                                                                    'Max Team Members',
                                                                                ) }}:</span>
                                                                                                                                                                                                                    {!! $plan->max_team_members == -1
                                                                                    ? '<span class="fw-semibold">Unlimited</span>'
                                                                                    : '<span class="fw-semibold">' . $plan->max_team_members . '</span>' !!}
                                                                                                                                                                                                                </li>
                                                                                                                                                                                                                <li class="mb-2">
                                                                                                                                                                                                                    <i class="fas fa-check-circle text-primary text-gradient me-2"></i>
                                                                                                                                                                                                                    <span
                                                                                                                                                                                                                        class="fw-semibold">{{ get_label(
                                                                                    'max_workspaces',
                                                                                    'Max Workspaces',
                                                                                ) }}:</span>
                                                                                                                                                                                                                    {!! $plan->max_worksapces == -1
                                                                                    ? '<span class="fw-semibold">Unlimited</span>'
                                                                                    : '<span class="fw-semibold">' . $plan->max_worksapces . '</span>' !!}
                                                                                                                                                                                                                </li>
                                                                                                                                                                                                                @if ($plan->modules)
                                                                                                                                                                                                                    <li class="mb-2">
                                                                                                                                                                                                                        <i class="fas fa-check-circle text-primary text-gradient me-2"></i>
                                                                                                                                                                                                                        <span class="fw-semibold">{{ get_label('modules', 'Modules') }}</span>
                                                                                                                                                                                                                        <ul class="list-unstyled text-smallcaps m-3 my-2 ps-0">
                                                                                                                                                                                                                            @php
                                                                                                                                                                                                                                $modules = json_decode($plan->modules);
                                                                                                                                                                                                                                $checkedModules = [];
                                                                                                                                                                                                                                $uncheckedModules = [];
                                                                                                                                                                                                                                foreach (
                                                                                                                                                                                                                                    config('taskify.modules')
                                                                                                                                                                                                                                    as $moduleName => $moduleData
                                                                                                                                                                                                                                ) {
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
                                                                                                                                                                                                                                $sortedModules = array_merge(
                                                                                                                                                                                                                                    $checkedModules,
                                                                                                                                                                                                                                    $uncheckedModules,
                                                                                                                                                                                                                                );
                                                                                                                                                                                                                            @endphp
                                                                                                                                                                                                                            @foreach ($sortedModules as $module)
                                                                                                                                                                                                                                @php
                                                                                                                                                                                                                                    $iconClass = in_array($module['name'], $modules)
                                                                                                                                                                                                                                        ? 'fas fa-check-circle text-success'
                                                                                                                                                                                                                                        : 'fas fa-times-circle text-danger';
                                                                                                                                                                                                                                @endphp
                                                                                                                                                                                                                                <li class="text-dark mb-2">
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
                                                                @endif

                                                                @if ($hasYearlyPlans)
                                                                    <div class="tab-pane {{ $activeTab == 'yearly' ? 'active' : '' }}" id="yearly">
                                                                        <div class="row mt-5">
                                                                            @foreach ($plans->where('yearly_price', '>', 0)->merge($plans->where('plan_type', 'free'))->sortBy(function ($plan) {
                                                                                                                                return $plan->plan_type === 'free' ? 0 : 1;
                                                                                                                            })->unique('id') as $plan)
                                                                                                                                                                                            <div class="col-md-4 col-lg-4 col-sm-6 mb-lg-0 mb-4 gap-2 pb-3">
                                                                                                                                                                                                <div class="card shadow-lg">
                                                                                                                                                                                                    <div class="card-header text-sm-start px-4 pb-3 pt-4 text-center">
                                                                                                                                                                                                        <h5 class="mb-1">{{ $plan->name }}</h5>
                                                                                                                                                                                                        <h3 class="font-weight-bolder mt-3">
                                                                                                                                                                                                            <small class="text-secondary font-weight-bold">
                                                                                                                                                                                                                @if ($plan->yearly_discounted_price > 0 && $plan->yearly_discounted_price < $plan->yearly_price)
                                                                                                                                                                                                                            <strike>{{ format_currency($plan->yearly_price) }}</strike>
                                                                                                                                                                                                                    </small>
                                                                                                                                                                                                                    {{ format_currency($plan->yearly_discounted_price) }} <small
                                                                                                                                                                                                                        class="text-secondary font-weight-bold text-sm">/
                                                                                                                                                                                                                        {{ get_label('yearly_price', 'Yearly Price') }}</small>
                                                                                                                                                                                                                @else
                                                                                                                                                                                                                    {{ format_currency($plan->yearly_price) }} <small
                                                                                                                                                                                                                        class="text-secondary font-weight-bold text-sm">/
                                                                                                                                                                                                                        {{ get_label('yearly_price', 'Yearly Price') }}
                                                                                                                                                                                                                    </small>
                                                                                                                                                                                                                @endif
                                                                                                                                                                                                        </small>
                                                                                                                                                                                                        </h3>
                                                                                                                                                                                                        <p class="text-lighter small text-black-50">{{ $plan->description }}</p>
                                                                                                                                                                                                        @if ($plan->yearly_price == 0)
                                                                                                                                                                                                            <a href="{{ route('login') }}"
                                                                                                                                                                                                                class="btn btn-outline-primary btn-sm w-100 border-radius-md mb-2 mt-4 text-center">{{ get_label('buy_now', 'Buy now') }}</a>
                                                                                                                                                                                                        @else
                                                                                                                                                                                                            <a href="{{ route('login') }}"
                                                                                                                                                                                                                class="btn btn-sm bg-gradient-dark w-100 border-radius-md mb-2 mt-4 text-center">{{ get_label('buy_now', 'Buy now') }}</a>
                                                                                                                                                                                                        @endif
                                                                                                                                                                                                    </div>
                                                                                                                                                                                                    <hr class="horizontal dark my-0">
                                                                                                                                                                                                    <div class="card-body pt-0">
                                                                                                                                                                                                        <div class="justify-content-start d-flex px-2 py-1">
                                                                                                                                                                                                            <ul class="list-unstyled mb-4">
                                                                                                                                                                                                                <li class="mb-2">
                                                                                                                                                                                                                    <i class="fas fa-check-circle text-primary text-gradient me-2"></i>
                                                                                                                                                                                                                    <span class="fw-semibold">{{ get_label('max_projects', 'Max Projects') }}:</span>
                                                                                                                                                                                                                    {!! $plan->max_projects == -1
                                                                                    ? '<span class="fw-semibold">Unlimited</span>'
                                                                                    : '<span class="fw-semibold">' . $plan->max_projects . '</span>' !!}
                                                                                                                                                                                                                </li>
                                                                                                                                                                                                                <li class="mb-2">
                                                                                                                                                                                                                    <i class="fas fa-check-circle text-primary text-gradient me-2"></i>
                                                                                                                                                                                                                    <span class="fw-semibold">{{ get_label('max_clients', 'Max Clients') }}:</span>
                                                                                                                                                                                                                    {!! $plan->max_clients == -1
                                                                                    ? '<span class="fw-semibold">Unlimited</span>'
                                                                                    : '<span class="fw-semibold">' . $plan->max_clients . '</span>' !!}
                                                                                                                                                                                                                </li>
                                                                                                                                                                                                                <li class="mb-2">
                                                                                                                                                                                                                    <i class="fas fa-check-circle text-primary text-gradient me-2"></i>
                                                                                                                                                                                                                    <span
                                                                                                                                                                                                                        class="fw-semibold">{{ get_label(
                                                                                    'max_team_members',
                                                                                    'Max Team Members',
                                                                                ) }}:</span>
                                                                                                                                                                                                                    {!! $plan->max_team_members == -1
                                                                                    ? '<span class="fw-semibold">Unlimited</span>'
                                                                                    : '<span class="fw-semibold">' . $plan->max_team_members . '</span>' !!}
                                                                                                                                                                                                                </li>
                                                                                                                                                                                                                <li class="mb-2">
                                                                                                                                                                                                                    <i class="fas fa-check-circle text-primary text-gradient me-2"></i>
                                                                                                                                                                                                                    <span
                                                                                                                                                                                                                        class="fw-semibold">{{ get_label(
                                                                                    'max_workspaces',
                                                                                    'Max Workspaces',
                                                                                ) }}:</span>
                                                                                                                                                                                                                    {!! $plan->max_worksapces == -1
                                                                                    ? '<span class="fw-semibold">Unlimited</span>'
                                                                                    : '<span class="fw-semibold">' . $plan->max_worksapces . '</span>' !!}
                                                                                                                                                                                                                </li>
                                                                                                                                                                                                                @if ($plan->modules)
                                                                                                                                                                                                                    <li class="mb-2">
                                                                                                                                                                                                                        <i class="fas fa-check-circle text-primary text-gradient me-2"></i>
                                                                                                                                                                                                                        <span class="fw-semibold">{{ get_label('modules', 'Modules') }}</span>
                                                                                                                                                                                                                        <ul class="list-unstyled text-smallcaps m-3 my-2 ps-0">
                                                                                                                                                                                                                            @php
                                                                                                                                                                                                                                $modules = json_decode($plan->modules);
                                                                                                                                                                                                                                $checkedModules = [];
                                                                                                                                                                                                                                $uncheckedModules = [];
                                                                                                                                                                                                                                foreach (
                                                                                                                                                                                                                                    config('taskify.modules')
                                                                                                                                                                                                                                    as $moduleName => $moduleData
                                                                                                                                                                                                                                ) {
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
                                                                                                                                                                                                                                $sortedModules = array_merge(
                                                                                                                                                                                                                                    $checkedModules,
                                                                                                                                                                                                                                    $uncheckedModules,
                                                                                                                                                                                                                                );
                                                                                                                                                                                                                            @endphp
                                                                                                                                                                                                                            @foreach ($sortedModules as $module)
                                                                                                                                                                                                                                @php
                                                                                                                                                                                                                                    $iconClass = in_array($module['name'], $modules)
                                                                                                                                                                                                                                        ? 'fas fa-check-circle text-success'
                                                                                                                                                                                                                                        : 'fas fa-times-circle text-danger';
                                                                                                                                                                                                                                @endphp
                                                                                                                                                                                                                                <li class="text-dark mb-2">
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
                                                                @endif

                                                                @if ($hasLifetimePlans)
                                                                    <div class="tab-pane {{ $activeTab == 'lifetime' ? 'active' : '' }}" id="lifetime">
                                                                        <div class="row mt-5">
                                                                            @foreach ($plans->where('lifetime_price', '>', 0)->merge($plans->where('plan_type', 'free'))->sortBy(function ($plan) {
                                                                                                                                return $plan->plan_type === 'free' ? 0 : 1;
                                                                                                                            })->unique('id') as $plan)
                                                                                                                                                                                            <div class="col-md-4 col-lg-4 col-sm-6 mb-lg-0 mb-4 gap-2 pb-3">
                                                                                                                                                                                                <div class="card shadow-lg">
                                                                                                                                                                                                    <div class="card-header text-sm-start px-4 pb-3 pt-4 text-center">
                                                                                                                                                                                                        <h5 class="mb-1">{{ $plan->name }}</h5>
                                                                                                                                                                                                        <h3 class="font-weight-bolder mt-3">
                                                                                                                                                                                                            <small class="text-secondary font-weight-bold">
                                                                                                                                                                                                                @if ($plan->lifetime_discounted_price > 0 && $plan->lifetime_discounted_price < $plan->lifetime_price)
                                                                                                                                                                                                                            <strike>{{ format_currency($plan->lifetime_price) }}</strike>
                                                                                                                                                                                                                    </small>
                                                                                                                                                                                                                    {{ format_currency($plan->lifetime_discounted_price) }} <small
                                                                                                                                                                                                                        class="text-secondary font-weight-bold text-sm">/
                                                                                                                                                                                                                        {{ get_label('lifetime_price', 'Lifetime Price') }}</small>
                                                                                                                                                                                                                @else
                                                                                                                                                                                                                    {{ format_currency($plan->lifetime_price) }} <small
                                                                                                                                                                                                                        class="text-secondary font-weight-bold text-sm">/
                                                                                                                                                                                                                        {{ get_label('lifetime_price', 'Lifetime Price') }}
                                                                                                                                                                                                                    </small>
                                                                                                                                                                                                                @endif
                                                                                                                                                                                                        </small>
                                                                                                                                                                                                        </h3>
                                                                                                                                                                                                        <p class="text-lighter small text-black-50">{{ $plan->description }}</p>
                                                                                                                                                                                                        @if ($plan->lifetime_price == 0)
                                                                                                                                                                                                            <a href="{{ route('login') }}"
                                                                                                                                                                                                                class="btn btn-outline-primary btn-sm w-100 border-radius-md mb-2 mt-4 text-center">{{ get_label('buy_now', 'Buy now') }}</a>
                                                                                                                                                                                                        @else
                                                                                                                                                                                                            <a href="{{ route('login') }}"
                                                                                                                                                                                                                class="btn btn-sm bg-gradient-dark w-100 border-radius-md mb-2 mt-4 text-center">{{ get_label('buy_now', 'Buy now') }}</a>
                                                                                                                                                                                                        @endif
                                                                                                                                                                                                    </div>
                                                                                                                                                                                                    <hr class="horizontal dark my-0">
                                                                                                                                                                                                    <div class="card-body pt-0">
                                                                                                                                                                                                        <div class="justify-content-start d-flex px-2 py-1">
                                                                                                                                                                                                            <ul class="list-unstyled mb-4">
                                                                                                                                                                                                                <li class="mb-2">
                                                                                                                                                                                                                    <i class="fas fa-check-circle text-primary text-gradient me-2"></i>
                                                                                                                                                                                                                    <span class="fw-semibold">{{ get_label('max_projects', 'Max Projects') }}:</span>
                                                                                                                                                                                                                    {!! $plan->max_projects == -1
                                                                                    ? '<span class="fw-semibold">Unlimited</span>'
                                                                                    : '<span class="fw-semibold">' . $plan->max_projects . '</span>' !!}
                                                                                                                                                                                                                </li>
                                                                                                                                                                                                                <li class="mb-2">
                                                                                                                                                                                                                    <i class="fas fa-check-circle text-primary text-gradient me-2"></i>
                                                                                                                                                                                                                    <span class="fw-semibold">{{ get_label('max_clients', 'Max Clients') }}:</span>
                                                                                                                                                                                                                    {!! $plan->max_clients == -1
                                                                                    ? '<span class="fw-semibold">Unlimited</span>'
                                                                                    : '<span class="fw-semibold">' . $plan->max_clients . '</span>' !!}
                                                                                                                                                                                                                </li>
                                                                                                                                                                                                                <li class="mb-2">
                                                                                                                                                                                                                    <i class="fas fa-check-circle text-primary text-gradient me-2"></i>
                                                                                                                                                                                                                    <span
                                                                                                                                                                                                                        class="fw-semibold">{{ get_label(
                                                                                    'max_team_members',
                                                                                    'Max Team Members',
                                                                                ) }}:</span>
                                                                                                                                                                                                                    {!! $plan->max_team_members == -1
                                                                                    ? '<span class="fw-semibold">Unlimited</span>'
                                                                                    : '<span class="fw-semibold">' . $plan->max_team_members . '</span>' !!}
                                                                                                                                                                                                                </li>
                                                                                                                                                                                                                <li class="mb-2">
                                                                                                                                                                                                                    <i class="fas fa-check-circle text-primary text-gradient me-2"></i>
                                                                                                                                                                                                                    <span
                                                                                                                                                                                                                        class="fw-semibold">{{ get_label(
                                                                                    'max_workspaces',
                                                                                    'Max Workspaces',
                                                                                ) }}:</span>
                                                                                                                                                                                                                    {!! $plan->max_worksapces == -1
                                                                                    ? '<span class="fw-semibold">Unlimited</span>'
                                                                                    : '<span class="fw-semibold">' . $plan->max_worksapces . '</span>' !!}
                                                                                                                                                                                                                </li>
                                                                                                                                                                                                                @if ($plan->modules)
                                                                                                                                                                                                                    <li class="mb-2">
                                                                                                                                                                                                                        <i class="fas fa-check-circle text-primary text-gradient me-2"></i>
                                                                                                                                                                                                                        <span class="fw-semibold">{{ get_label('modules', 'Modules') }}</span>
                                                                                                                                                                                                                        <ul class="list-unstyled text-smallcaps m-3 my-2 ps-0">
                                                                                                                                                                                                                            @php
                                                                                                                                                                                                                                $modules = json_decode($plan->modules);
                                                                                                                                                                                                                                $checkedModules = [];
                                                                                                                                                                                                                                $uncheckedModules = [];
                                                                                                                                                                                                                                foreach (
                                                                                                                                                                                                                                    config('taskify.modules')
                                                                                                                                                                                                                                    as $moduleName => $moduleData
                                                                                                                                                                                                                                ) {
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
                                                                                                                                                                                                                                $sortedModules = array_merge(
                                                                                                                                                                                                                                    $checkedModules,
                                                                                                                                                                                                                                    $uncheckedModules,
                                                                                                                                                                                                                                );
                                                                                                                                                                                                                            @endphp
                                                                                                                                                                                                                            @foreach ($sortedModules as $module)
                                                                                                                                                                                                                                @php
                                                                                                                                                                                                                                    $iconClass = in_array($module['name'], $modules)
                                                                                                                                                                                                                                        ? 'fas fa-check-circle text-success'
                                                                                                                                                                                                                                        : 'fas fa-times-circle text-danger';
                                                                                                                                                                                                                                @endphp
                                                                                                                                                                                                                                <li class="text-dark mb-2">
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
                                                                @endif

                                                                <!-- If no pricing plans have price > 0 or free plans, show this message -->
                                                                @if (!$hasMonthlyPlans && !$hasYearlyPlans && !$hasLifetimePlans)
                                                                    <div class="container mb-8 mt-8 py-3">
                                                                        <div class="alert bg-gradient-warning h4 text-center">
                                                                            <i class="fas fa-exclamation-circle"></i> <span class="text-black">
                                                                                {{ get_label('no_plans_available', 'No Plans Available') }}</span>
                                                                        </div>
                                                                    </div>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    @else
                                                        <div class="container mb-8 mt-8 py-3">
                                                            <div class="alert bg-gradient-warning h4 text-center">
                                                                <i class="fas fa-exclamation-circle"></i> <span class="text-black">
                                                                    {{ get_label('no_plans_available', 'No Plans Available') }}</span>
                                                            </div>
                                                        </div>
                                                    @endif
                                                </div>
                                            </section>
                @endif

                <section class="py-4">
                    <div class="container">
                        <div class="row my-5">
                            <div class="col-md-6 mx-auto text-center">
                                <h2>{{ get_label('frequently_asked_questions', 'Frequently Asked Questions') }}
                                </h2>
                                <p>{{ get_label(
        'faqsDesc',
        "A lot of people don't appreciate the moment until it’s passed. I'm not trying my hardest, and I'm
                                not trying to do",
    ) }}
                                </p>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-10 mx-auto">
                                <div class="accordion" id="accordionRental">
                                    <div class="accordion-item mb-3">
                                        <h5 class="accordion-header" id="headingOne">
                                            <button class="accordion-button border-bottom font-weight-bold collapsed" type="button"
                                                data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="false"
                                                aria-controls="collapseOne">
                                                <?= get_label('what_is_a_project_management_system', 'What is a project management system?') ?>
                                                <i class="collapse-close fa fa-chevron-down position-absolute end-0 me-3 pt-1 text-xs"
                                                    aria-hidden="true"></i>
                                                <i class="collapse-open fa fa-chevron-up position-absolute end-0 me-3 pt-1 text-xs"
                                                    aria-hidden="true"></i>
                                            </button>
                                        </h5>
                                        <div id="collapseOne" class="accordion-collapse collapse" aria-labelledby="headingOne"
                                            data-bs-parent="#accordionRental">
                                            <div class="accordion-body text-dark opacity-8 text-sm">
                                                <?= get_label('what_is_a_project_management_system_answer', 'A project management system is a software tool designed to help teams plan, execute, and manage projects from initiation to completion. It facilitates collaboration, task allocation, scheduling, and tracking of project progress.') ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="accordion-item mb-3">
                                        <h5 class="accordion-header" id="headingTwo">
                                            <button class="accordion-button border-bottom font-weight-bold collapsed" type="button"
                                                data-bs-toggle="collapse" data-bs-target="#collapseTwo" aria-expanded="false"
                                                aria-controls="collapseTwo">
                                                <?= get_label('key_features_of_project_management_system', 'What are the key features of a project management system?') ?>
                                                <i class="collapse-close fa fa-chevron-down position-absolute end-0 me-3 pt-1 text-xs"
                                                    aria-hidden="true"></i>
                                                <i class="collapse-open fa fa-chevron-up position-absolute end-0 me-3 pt-1 text-xs"
                                                    aria-hidden="true"></i>
                                            </button>
                                        </h5>
                                        <div id="collapseTwo" class="accordion-collapse collapse" aria-labelledby="headingTwo"
                                            data-bs-parent="#accordionRental">
                                            <div class="accordion-body text-dark opacity-8 text-sm">
                                                <?= get_label('key_features_of_project_management_system_answer', 'Key features typically include task management, team collaboration, project planning and scheduling, time tracking, file sharing, reporting and analytics, and integration with other tools.') ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="accordion-item mb-3">
                                        <h5 class="accordion-header" id="headingThree">
                                            <button class="accordion-button border-bottom font-weight-bold collapsed" type="button"
                                                data-bs-toggle="collapse" data-bs-target="#collapseThree" aria-expanded="false"
                                                aria-controls="collapseThree">
                                                <?= get_label('benefits_of_project_management_system', 'How does a project management system benefit businesses?') ?>
                                                <i class="collapse-close fa fa-chevron-down position-absolute end-0 me-3 pt-1 text-xs"
                                                    aria-hidden="true"></i>
                                                <i class="collapse-open fa fa-chevron-up position-absolute end-0 me-3 pt-1 text-xs"
                                                    aria-hidden="true"></i>
                                            </button>
                                        </h5>
                                        <div id="collapseThree" class="accordion-collapse collapse" aria-labelledby="headingThree"
                                            data-bs-parent="#accordionRental">
                                            <div class="accordion-body text-dark opacity-8 text-sm">
                                                <?= get_label('benefits_of_project_management_system_answer', 'Project management systems improve productivity and efficiency by streamlining workflows, enabling better communication and collaboration among team members, providing transparency into project progress, and facilitating effective resource allocation.') ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="accordion-item mb-3">
                                        <h5 class="accordion-header" id="headingFour">
                                            <button class="accordion-button border-bottom font-weight-bold collapsed" type="button"
                                                data-bs-toggle="collapse" data-bs-target="#collapseFour" aria-expanded="false"
                                                aria-controls="collapseFour">
                                                <?= get_label('task_management_in_project_management_system', 'What is task management in the context of project management systems?') ?>
                                                <i class="collapse-close fa fa-chevron-down position-absolute end-0 me-3 pt-1 text-xs"
                                                    aria-hidden="true"></i>
                                                <i class="collapse-open fa fa-chevron-up position-absolute end-0 me-3 pt-1 text-xs"
                                                    aria-hidden="true"></i>
                                            </button>
                                        </h5>
                                        <div id="collapseFour" class="accordion-collapse collapse" aria-labelledby="headingFour"
                                            data-bs-parent="#accordionRental">
                                            <div class="accordion-body text-dark opacity-8 text-sm">
                                                <?= get_label('task_management_in_project_management_system_answer', 'Task management involves creating, assigning, tracking, and organizing individual tasks within a project. It helps ensure that team members are aware of their responsibilities and deadlines, and allows for better coordination and prioritization of work.') ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="accordion-item mb-3">
                                        <h5 class="accordion-header" id="headingFifth">
                                            <button class="accordion-button border-bottom font-weight-bold" type="button"
                                                data-bs-toggle="collapse" data-bs-target="#collapseFifth" aria-expanded="true"
                                                aria-controls="collapseFifth">
                                                <?= get_label('task_management_contribution_to_project_success', 'How does task management contribute to project success?') ?>
                                                <i class="collapse-close fa fa-chevron-down position-absolute end-0 me-3 pt-1 text-xs"
                                                    aria-hidden="true"></i>
                                                <i class="collapse-open fa fa-chevron-up position-absolute end-0 me-3 pt-1 text-xs"
                                                    aria-hidden="true"></i>
                                            </button>
                                        </h5>
                                        <div id="collapseFifth" class="accordion-collapse show collapse"
                                            aria-labelledby="headingFifth" data-bs-parent="#accordionRental">
                                            <div class="accordion-body text-dark opacity-8 text-sm">
                                                <?= get_label('task_management_contribution_to_project_success_answer', 'Effective task management ensures that project activities are completed on time and within budget, minimizes delays and bottlenecks, identifies potential issues early on, and enables efficient resource utilization.') ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="accordion-item mb-3">
                                        <!-- FAQ 6 -->
                                        <h5 class="accordion-header" id="headingSix">
                                            <button class="accordion-button border-bottom font-weight-bold collapsed" type="button"
                                                data-bs-toggle="collapse" data-bs-target="#collapseSix" aria-expanded="false"
                                                aria-controls="collapseSix">
                                                <?= get_label('multiple_projects_handling', 'Can a project management system handle multiple projects simultaneously?') ?>
                                                <i class="collapse-close fa fa-chevron-down position-absolute end-0 me-3 pt-1 text-xs"
                                                    aria-hidden="true"></i>
                                                <i class="collapse-open fa fa-chevron-up position-absolute end-0 me-3 pt-1 text-xs"
                                                    aria-hidden="true"></i>
                                            </button>
                                        </h5>
                                        <div id="collapseSix" class="accordion-collapse collapse" aria-labelledby="headingSix"
                                            data-bs-parent="#accordionRental">
                                            <div class="accordion-body text-dark opacity-8 text-sm">
                                                <?= get_label('multiple_projects_handling_answer', 'Yes, most project management systems are designed to support the management of multiple projects concurrently. They typically provide features for organizing projects into separate workspaces or folders, allowing teams to easily switch between projects.') ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="accordion-item mb-3">
                                        <!-- FAQ 6 -->
                                        <h5 class="accordion-header" id="headingSeven">
                                            <button class="accordion-button border-bottom font-weight-bold collapsed" type="button"
                                                data-bs-toggle="collapse" data-bs-target="#collapseSeven" aria-expanded="false"
                                                aria-controls="collapseSeven">
                                                <?= get_label('customization_of_project_management_system', 'Is it possible to customize project management systems to fit specific project requirements?') ?>
                                                <i class="collapse-close fa fa-chevron-down position-absolute end-0 me-3 pt-1 text-xs"
                                                    aria-hidden="true"></i>
                                                <i class="collapse-open fa fa-chevron-up position-absolute end-0 me-3 pt-1 text-xs"
                                                    aria-hidden="true"></i>
                                            </button>
                                        </h5>
                                        <div id="collapseSeven" class="accordion-collapse collapse" aria-labelledby="headingSeven"
                                            data-bs-parent="#accordionRental">
                                            <div class="accordion-body text-dark opacity-8 text-sm">
                                                <?= get_label('customization_of_project_management_system_answer', 'Many project management systems offer customization options such as creating custom task types, defining project-specific workflows, adding custom fields, and integrating with other tools to adapt to the unique needs of different projects or industries.') ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="accordion-item mb-3">
                                        <!-- FAQ 6 -->
                                        <h5 class="accordion-header" id="headingEight">
                                            <button class="accordion-button border-bottom font-weight-bold collapsed" type="button"
                                                data-bs-toggle="collapse" data-bs-target="#collapseEight" aria-expanded="false"
                                                aria-controls="collapseEight">
                                                <?= get_label('security_of_project_management_system', 'How secure are project management systems for storing sensitive project data?') ?>
                                                <i class="collapse-close fa fa-chevron-down position-absolute end-0 me-3 pt-1 text-xs"
                                                    aria-hidden="true"></i>
                                                <i class="collapse-open fa fa-chevron-up position-absolute end-0 me-3 pt-1 text-xs"
                                                    aria-hidden="true"></i>
                                            </button>
                                        </h5>
                                        <div id="collapseEight" class="accordion-collapse collapse" aria-labelledby="headingEight"
                                            data-bs-parent="#accordionRental">
                                            <div class="accordion-body text-dark opacity-8 text-sm">
                                                <?= get_label('security_of_project_management_system_answer', 'Project management systems prioritize data security and typically employ measures such as encryption, user authentication, access control, and regular data backups to safeguard sensitive project information from unauthorized access, loss, or theft.') ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="accordion-item mb-3">
                                        <!-- FAQ 6 -->
                                        <h5 class="accordion-header" id="headingNine">
                                            <button class="accordion-button border-bottom font-weight-bold collapsed" type="button"
                                                data-bs-toggle="collapse" data-bs-target="#collapseNine" aria-expanded="false"
                                                aria-controls="collapseSeven">
                                                <?= get_label('integration_with_other_tools', 'Can project management systems integrate with other tools and applications?') ?>
                                                <i class="collapse-close fa fa-chevron-down position-absolute end-0 me-3 pt-1 text-xs"
                                                    aria-hidden="true"></i>
                                                <i class="collapse-open fa fa-chevron-up position-absolute end-0 me-3 pt-1 text-xs"
                                                    aria-hidden="true"></i>
                                            </button>
                                        </h5>
                                        <div id="collapseNine" class="accordion-collapse collapse" aria-labelledby="headingNine"
                                            data-bs-parent="#accordionRental">
                                            <div class="accordion-body text-dark opacity-8 text-sm">
                                                <?= get_label('integration_with_other_tools_answer', 'Yes, project management systems often offer integrations with popular productivity tools, communication platforms, file storage services, and software development tools to streamline workflows and enhance collaboration across teams.') ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="accordion-item mb-3">
                                        <!-- FAQ 6 -->
                                        <h5 class="accordion-header" id="headingTen">
                                            <button class="accordion-button border-bottom font-weight-bold collapsed" type="button"
                                                data-bs-toggle="collapse" data-bs-target="#collapseTen" aria-expanded="false"
                                                aria-controls="collapseTen">
                                                <?= get_label('choosing_right_project_management_system', 'How do I choose the right project management system for my team?') ?>
                                                <i class="collapse-close fa fa-chevron-down position-absolute end-0 me-3 pt-1 text-xs"
                                                    aria-hidden="true"></i>
                                                <i class="collapse-open fa fa-chevron-up position-absolute end-0 me-3 pt-1 text-xs"
                                                    aria-hidden="true"></i>
                                            </button>
                                        </h5>
                                        <div id="collapseTen" class="accordion-collapse collapse" aria-labelledby="headingTen"
                                            data-bs-parent="#accordionRental">
                                            <div class="accordion-body text-dark opacity-8 text-sm">
                                                <?= get_label('choosing_right_project_management_system_answer', 'When selecting a project management system, consider factors such as your team\'s size and requirements, the complexity of your projects, ease of use, scalability, customization options, pricing, customer support, and compatibility with existing tools and workflows. It\'s also helpful to try out different systems through free trials or demos to evaluate their suitability for your needs.') ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
                <script src="assets/lightbox/lightbox.min.js"></script>
@endsection
