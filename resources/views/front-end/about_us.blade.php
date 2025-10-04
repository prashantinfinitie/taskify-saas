@extends('front-end.layout')
@section('title')
    {{ get_label('about_us', 'About Us') }}

@endsection
@section('content')
    <link rel="stylesheet" href="assets/lightbox/lightbox.min.css">

    <section class="section" id="about_us">
        <div class="bg-gradient-primary position-relative border-radius-xl w-100">

            <img src="/assets/front-end/img/gallery/waves-white.svg" alt="pattern-lines"
                class="position-absolute start-0 top-md-0 w-100 opacity-7">
            <div class="container pb-lg-9 pb-7 pt-7 postion-relative z-index-2">
                <div class="row">
                    <div class="col-md-8 mx-auto text-center">
                        <span class="badge bg-gradient-dark mb-2">{{ get_label('about_us', 'About Us') }}</span>
                        <h3 class="text-white">
                            <?= get_label('about_us', 'About Us') ?>
                            {{ $general_settings['company_title'] }}
                        </h3>
                        <p class="text-center text-white fs-0 fs-md-1 mt-3 mb-3">
                            <?= get_label('about_us_desc1', 'We are passionate about empowering teams to achieve peak productivity.') ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>
        <div class="mt-lg-n8 mt-n7">
            <div class="container">

                <section class="card py-6">
                    <div class="">

                        <div class="row">

                            <div class="col-lg-6 mx-auto text-center pb-4">
                                <h5 class="text-gradient text-primary font-weight-bolder">
                                    {{ !empty($frontend_general_settings['header_subtitle']) ? $frontend_general_settings['header_subtitle'] : get_label(
        'project_management_and_task_management_system',
        "Project Management and Task Management System"
    ) }}
                                </h5>
                                <h2>{{ !empty($frontend_general_settings['header_title']) ? $frontend_general_settings['header_title'] : get_label("enhance_team_collaboration_and_productivity", "Enhance Team Collaboration and Productivity")}}
                                </h2>
                                <p class="text-lg">
                                    {{ !empty($frontend_general_settings['header_description']) ? $frontend_general_settings['header_description'] : get_label("streamlined_collaboration_for_productivity", "Streamlined collaboration for productivity")}}
                                </p>

                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="p-3 text-center">
                                    <div
                                        class="icon icon-shape bg-gradient-dark shadow mx-auto  align-items-center justify-content-center">
                                        <i class="fas fa-tasks text-white"></i>
                                    </div>
                                    <h5 class="mt-4 text-lg font-weight-bolder">
                                        {{ !empty($frontend_general_settings['info_about_us_card1_title']) ? $frontend_general_settings['info_about_us_card1_title'] : get_label("manage_projects_efficiently", "Manage Projects Efficiently")}}
                                    </h5>
                                    <p>{{ !empty($frontend_general_settings['info_about_us_card1_description']) ? $frontend_general_settings['info_about_us_card1_description'] : get_label("simplify_project_organization_for_focus", "Simplify project organization for focus.")}}

                                    </p>

                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="p-3 text-center">
                                    <div
                                        class="icon icon-shape bg-gradient-dark shadow mx-auto  align-items-center justify-content-center">
                                        <i class="fas fa-tasks text-white"></i>

                                    </div>

                                    <h5 class="mt-4 text-lg font-weight-bolder">
                                        {{ !empty($frontend_general_settings['info_about_us_card2_title']) ? $frontend_general_settings['info_about_us_card2_title'] : get_label("assign_and_monitor_tasks", "Assign and Monitor Tasks")}}
                                    </h5>
                                    <p>{{ !empty($frontend_general_settings['info_about_us_card2_description']) ? $frontend_general_settings['info_about_us_card2_description'] : get_label("assign_track_and_meet_deadlines", "Easily assign tasks to team members, set priorities, and track progress in real-time to ensure nothing falls through the cracks.")}}
                                    </p>

                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="p-3 text-center">
                                    <div
                                        class="icon icon-shape bg-gradient-dark shadow mx-auto  align-items-center justify-content-center">
                                        <i class="fas fa-users text-white"></i>
                                    </div>
                                    <h5 class="mt-4 text-lg font-weight-bolder">{{
        !empty($frontend_general_settings['info_about_us_card3_title']) ?
        $frontend_general_settings['info_about_us_card3_title'] :
        get_label("enhance_collaboration", "Enhance Collaboration")}}</h5>
                                    <p>{{!empty($frontend_general_settings['info_about_us_card3_description']) ?
        $frontend_general_settings['info_about_us_card3_description'] :
        get_label("seamless_collaboration_for_success", "Connect your team with real-time communication, shared tasks, and seamless file sharing to boost teamwork and productivity.")}}</p>

                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="pt-3">

                    <div class="container">
                        <div class="row">
                            <div class="col-md-5 col-12 my-auto">
                                <h5 class="text-gradient text-primary font-weight-bolder">
                                    {{ !empty($frontend_general_settings['project_management_subtitle']) ? $frontend_general_settings['project_management_subtitle'] : get_label('project_management', 'Project Management') }}
                                </h5>
                                <h2> <?=  !empty($frontend_general_settings['project_management_title']) ? $frontend_general_settings['project_management_title'] : get_label('simple_and_intuitive_project_management', 'Simple and Intuitive Project Management') ?>
                                </h2>
                                <p class="small text-black-50 text-sm">
                                    <?= !empty($frontend_general_settings['project_management_description']) ? $frontend_general_settings['project_management_description'] : get_label('simpleIntuitiveDesc', 'No more learning curves: Our user-friendly interface makes it easy for anyone to get started, regardless of technical expertise. Visualize your projects with intuitive dashboards and customizable views.') ?>
                                </p>

                                <div class="d-flex justify-content-lg-start align-items-center py-2">
                                    <div
                                        class="icon icon-shape icon-xxs rounded-circle bg-gradient-dark d-flex align-items-center justify-content-center text-center">
                                        <i class="fas fa-check opacity-10 mt-2" aria-hidden="true"></i>
                                    </div>
                                    <div>

                                        <span
                                            class="ps-2">{{ !empty($frontend_general_settings['project_management_feature1']) ? $frontend_general_settings['project_management_feature1'] : get_label("user_friendly_no_learning_curve", "User-friendly, no learning curve.")}}</span>
                                    </div>
                                </div>
                                <div class="d-flex justify-content-lg-start align-items-center py-2">
                                    <div
                                        class="icon icon-shape icon-xxs rounded-circle bg-gradient-dark d-flex align-items-center justify-content-center text-center">
                                        <i class="fas fa-check opacity-10 mt-2" aria-hidden="true"></i>
                                    </div>
                                    <div>

                                        <span
                                            class="ps-2">{{ !empty($frontend_general_settings['project_management_feature2']) ? $frontend_general_settings['project_management_feature2'] : get_label("non_tech_users_start_easily", "Non-tech users start easily.")}}</span>
                                    </div>
                                </div>
                                <div class="d-flex justify-content-lg-start align-items-center py-2">
                                    <div
                                        class="icon icon-shape icon-xxs rounded-circle bg-gradient-dark d-flex align-items-center justify-content-center text-center">
                                        <i class="fas fa-check opacity-10 mt-2" aria-hidden="true"></i>
                                    </div>
                                    <div>

                                        <span
                                            class="ps-2">{{ !empty($frontend_general_settings['project_management_feature3']) ? $frontend_general_settings['project_management_feature3'] : get_label("intuitive_project_dashboards", "Intuitive project dashboards.")}}</span>
                                    </div>
                                </div>
                                <div class="d-flex justify-content-lg-start align-items-center py-2">
                                    <div
                                        class="icon icon-shape icon-xxs rounded-circle bg-gradient-dark d-flex align-items-center justify-content-center text-center">
                                        <i class="fas fa-check opacity-10 mt-2" aria-hidden="true"></i>
                                    </div>
                                    <div>
                                        <span
                                            class="ps-2">{{ !empty($frontend_general_settings['project_management_feature4']) ? $frontend_general_settings['project_management_feature4'] : get_label("customizable_project_views", "Customizable project views.")}}</span>
                                    </div>
                                </div>

                            </div>

                            <div class="col-md-7 col-12 my-auto ms-auto">
                                <div class="image-box-85">

                                <img class="w-100 border-radius-lg image-box-85"
                                    src="{{ !empty($frontend_general_settings['project_management_image']) ? asset($frontend_general_settings['project_management_image']) : asset('assets/front-end/img/gallery/Statistics-pana.png') }}"
                                    alt="Project Management">
                                </div>    
                            </div>

                        </div>
                    </div>
                </section>

                <section class="">
                    <div class="container">
                        <div class="row">
                            <div class="col-md-7 col-12 my-auto me-auto px-4 order-lg-1 order-2">
                                <div class="image-box-85">
                                    <img class="w-100 border-radius-lg image-box-85"
                                        src="{{ !empty($frontend_general_settings['task_management_image']) ? asset($frontend_general_settings['task_management_image']) : asset('assets/front-end/img/gallery/Visual data-pana.png') }}"
                                        alt="Task Management">
                                </div>
                            </div>

                            <div class="col-md-5 col-12 my-auto order-lg-2 order-1">
                                <h5 class="text-gradient text-primary font-weight-bolder">
                                    {{ !empty($frontend_general_settings['task_management_subtitle']) ? $frontend_general_settings['task_management_subtitle'] : get_label('task_management', 'Task Management') }}
                                </h5>
                                <h2><?= !empty($frontend_general_settings['task_management_title']) ? $frontend_general_settings['task_management_title'] : get_label('effective_task_organization_with_workspaces_and_statuses', 'Effective Task Organization with Workspaces and Statuses') ?>
                                </h2>
                                <p class="text-sm small text-black-50">
                                    <?= !empty($frontend_general_settings['task_management_description']) ? $frontend_general_settings['task_management_description'] : get_label('effectiveTaskDesc', 'Organize chaos: Break down complex projects into manageable tasks and subtasks using our flexible workspace system. Keep track of progress with customizable task statuses (e.g., "To Do," "In Progress," "Completed") and prioritize effectively by highlighting critical tasks.') ?>
                                </p>

                                <div class="d-flex justify-content-lg-start align-items-center py-2">
                                    <div
                                        class="icon icon-shape icon-xxs rounded-circle bg-gradient-dark d-flex align-items-center justify-content-center text-center">
                                        <i class="fas fa-check opacity-10 mt-2" aria-hidden="true"></i>
                                    </div>
                                    <div>
                                        <span
                                            class="ps-2">{{ !empty($frontend_general_settings['task_management_feature1']) ? $frontend_general_settings['task_management_feature1'] : get_label("subdivide_tasks_for_organization", "Subdivide tasks for organization.")}}</span>

                                    </div>
                                </div>
                                <div class="d-flex justify-content-lg-start align-items-center py-2">
                                    <div
                                        class="icon icon-shape icon-xxs rounded-circle bg-gradient-dark d-flex align-items-center justify-content-center text-center">
                                        <i class="fas fa-check opacity-10 mt-2" aria-hidden="true"></i>
                                    </div>
                                    <div>
                                        <span
                                            class="ps-2">{{ !empty($frontend_general_settings['task_management_feature2']) ? $frontend_general_settings['task_management_feature2'] : get_label("customizable_progress_tracking", "Customizable progress tracking.")}}</span>

                                    </div>
                                </div>
                                <div class="d-flex justify-content-lg-start align-items-center py-2">
                                    <div
                                        class="icon icon-shape icon-xxs rounded-circle bg-gradient-dark d-flex align-items-center justify-content-center text-center">
                                        <i class="fas fa-check opacity-10 mt-2" aria-hidden="true"></i>
                                    </div>
                                    <div>
                                        <span
                                            class="ps-2">{{ !empty($frontend_general_settings['task_management_feature3']) ? $frontend_general_settings['task_management_feature3'] : get_label("flexible_project_workspace", "Flexible project workspace.")}}</span>

                                    </div>
                                </div>
                                <div class="d-flex justify-content-lg-start align-items-center py-2">
                                    <div
                                        class="icon icon-shape icon-xxs rounded-circle bg-gradient-dark d-flex align-items-center justify-content-center text-center">
                                        <i class="fas fa-check opacity-10 mt-2" aria-hidden="true"></i>
                                    </div>

                                    <div>
                                        <span
                                            class="ps-2">{{ !empty($frontend_general_settings['task_management_feature4']) ? $frontend_general_settings['task_management_feature4'] : get_label("highlight_critical_tasks", "Highlight critical tasks.")}}</span>

                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                </section>

                <section class="">
                    <div class="container">

                        <div class="row">

                            <div class="col-md-6 col-12 my-auto">
                                <h5 class="text-gradient text-primary font-weight-bolder">
                                    {{ !empty($frontend_general_settings['team_collaboration_subtitle']) ? $frontend_general_settings['team_collaboration_subtitle'] : get_label('team_collaboration', 'Team Collaboration') }}
                                </h5>
                                <h2 class="">
                                    <?= !empty($frontend_general_settings['team_collaboration_title']) ? $frontend_general_settings['team_collaboration_title'] : get_label('improved_team_collaboration_and_communication', 'Improved Team Collaboration and Communication') ?>

                                </h2>
                                <p class="text-sm small text-black-50">
                                    <?= !empty($frontend_general_settings['team_collaboration_description']) ? $frontend_general_settings['team_collaboration_description'] : get_label('improvedTeamDesc', 'Break down silos: Foster seamless collaboration with built-in communication tools like comments, mentions, and discussions. Stay on the same page with real-time task updates and activity feeds, ensuring everyone is informed. Centralize all project-related information, documents, and files in one easily accessible location.') ?>
                                </p>

                                <div class="d-flex justify-content-lg-start align-items-center py-2">
                                    <div
                                        class="icon icon-shape icon-xxs rounded-circle bg-gradient-dark d-flex align-items-center justify-content-center text-center">
                                        <i class="fas fa-check opacity-10 mt-2" aria-hidden="true"></i>
                                    </div>
                                    <div>
                                        <span
                                            class="ps-2">{{ !empty($frontend_general_settings['team_collaboration_feature1']) ? $frontend_general_settings['team_collaboration_feature1'] : get_label("seamless_collaboration_tools", "Seamless collaboration tools.")}}</span>

                                    </div>
                                </div>
                                <div class="d-flex justify-content-lg-start align-items-center py-2">
                                    <div
                                        class="icon icon-shape icon-xxs rounded-circle bg-gradient-dark d-flex align-items-center justify-content-center text-center">
                                        <i class="fas fa-check opacity-10 mt-2" aria-hidden="true"></i>
                                    </div>
                                    <div>
                                        <span
                                            class="ps-2">{{ !empty($frontend_general_settings['team_collaboration_feature2']) ? $frontend_general_settings['team_collaboration_feature2'] : get_label("real_time_task_updates", "Real-time task updates.")}}</span>

                                    </div>
                                </div>
                                <div class="d-flex justify-content-lg-start align-items-center py-2">
                                    <div
                                        class="icon icon-shape icon-xxs rounded-circle bg-gradient-dark d-flex align-items-center justify-content-center text-center">
                                        <i class="fas fa-check opacity-10 mt-2" aria-hidden="true"></i>
                                    </div>
                                    <div>
                                        <span
                                            class="ps-2">{{ !empty($frontend_general_settings['team_collaboration_feature3']) ? $frontend_general_settings['team_collaboration_feature3'] : get_label("centralized_project_data", "Centralize project data")}}.</span>

                                    </div>
                                </div>
                                <div class="d-flex justify-content-lg-start align-items-center py-2">
                                    <div
                                        class="icon icon-shape icon-xxs rounded-circle bg-gradient-dark d-flex align-items-center justify-content-center text-center">
                                        <i class="fas fa-check opacity-10 mt-2" aria-hidden="true"></i>
                                    </div>
                                    <div>
                                        <span
                                            class="ps-2">{{ !empty($frontend_general_settings['team_collaboration_feature4']) ? $frontend_general_settings['team_collaboration_feature4'] : get_label("foster_teamwork_break_silos", "Foster teamwork, break silos")}}</span>

                                    </div>
                                </div>

                            </div>
                            <div class="col-md-6 col-12 my-auto ms-auto">
                                <div class="image-box-85">

                                <img class="w-100 border-radius-lg image-box-85"
                                    src="{{ !empty($frontend_general_settings['team_collaboration_image']) ? asset($frontend_general_settings['team_collaboration_image']) : asset('assets/front-end/img/gallery/Connected world-bro.png') }}"
                                    alt="Team Collaboration">
                                </div>    
                            </div>
                        </div>
                    </div>
                </section>

                <section class="">
                    <div class="container">
                        <div class="row">
                            <div class="col-md-6 col-12 my-auto me-auto px-4 order-lg-1 order-2">
                                <div class="image-box-85">

                                <img class="w-100 border-radius-lg image-box-85"
                                    src="{{ !empty($frontend_general_settings['increased_productivity_image']) ? asset($frontend_general_settings['increased_productivity_image']) : asset('assets/front-end/img/gallery/Design stats-rafiki.png') }}"
                                    alt="Increased Productivity">
                                </div>    
                            </div>
                            <div class="col-md-6 col-12 my-auto order-lg-2 order-1">
                                <h5 class="text-gradient text-primary font-weight-bolder">
                                    {{  !empty($frontend_general_settings['increased_productivity_subtitle']) ? $frontend_general_settings['increased_productivity_subtitle'] : get_label('increased_productivity', 'Increased Productivity') }}
                                </h5>
                                <h2> <?= !empty($frontend_general_settings['increased_productivity_title']) ? $frontend_general_settings['increased_productivity_title'] : get_label('increased_productivity_and_efficiency', 'Increased Productivity and Efficiency') ?>
                                </h2>
                                <p class="text-sm small text-black-50">
                                    <?= !empty($frontend_general_settings['increased_productivity_description']) ? $frontend_general_settings['increased_productivity_description'] : get_label('increasedProductivityDesc', 'Automate repetitive tasks to free up valuable time. Minimize distractions and streamline your workflow with centralized task management. Meet deadlines with confidence with built-in time tracking, milestone management, and progress reporting.') ?>
                                </p>

                                <div class="d-flex justify-content-lg-start align-items-center py-2">
                                    <div
                                        class="icon icon-shape icon-xxs rounded-circle bg-gradient-dark d-flex align-items-center justify-content-center text-center">
                                        <i class="fas fa-check opacity-10 mt-2" aria-hidden="true"></i>
                                    </div>
                                    <div>
                                        <span
                                            class="ps-2">{{ !empty($frontend_general_settings['increased_productivity_feature1']) ? $frontend_general_settings['increased_productivity_feature1'] : get_label("centralized_focused_workflow", "Centralized, focused workflow")}}</span>

                                    </div>
                                </div>
                                <div class="d-flex justify-content-lg-start align-items-center py-2">
                                    <div
                                        class="icon icon-shape icon-xxs rounded-circle bg-gradient-dark d-flex align-items-center justify-content-center text-center">
                                        <i class="fas fa-check opacity-10 mt-2" aria-hidden="true"></i>
                                    </div>
                                    <div>
                                        <span
                                            class="ps-2">{{ !empty($frontend_general_settings['increased_productivity_feature2']) ? $frontend_general_settings['increased_productivity_feature2'] : get_label("confident_deadline_tracking", "Confident deadline tracking")}}</span>

                                    </div>
                                </div>
                                <div class="d-flex justify-content-lg-start align-items-center py-2">
                                    <div
                                        class="icon icon-shape icon-xxs rounded-circle bg-gradient-dark d-flex align-items-center justify-content-center text-center">
                                        <i class="fas fa-check opacity-10 mt-2" aria-hidden="true"></i>
                                    </div>
                                    <div>
                                        <span
                                            class="ps-2">{{ !empty($frontend_general_settings['increased_productivity_feature3']) ? $frontend_general_settings['increased_productivity_feature3'] : get_label("progress_ensures_timely_completion", "Progress ensures timely completion.")}}</span>

                                    </div>
                                </div>

                                <div class="d-flex justify-content-lg-start align-items-center py-2">
                                    <div
                                        class="icon icon-shape icon-xxs rounded-circle bg-gradient-dark d-flex align-items-center justify-content-center text-center">
                                        <i class="fas fa-check opacity-10 mt-2" aria-hidden="true"></i>

                                    </div>

                                    <div>
                                        <span
                                            class="ps-2">{{ !empty($frontend_general_settings['increased_productivity_feature4']) ? $frontend_general_settings['increased_productivity_feature4'] : get_label("efficient_streamlined_processes", "Efficient streamlined processes")}}</span>

                                    </div>
                                </div>

                            </div>
                        </div>

                    </div>
                </section>
            </div>
    </section>

    <section>
        <div class="container text-center mb-3">
            <div class="col-8 mx-auto text-center">
                <h5 class="text-gradient text-primary font-weight-bolder">
                    {{ !empty($frontend_general_settings['system_overview']) ? $frontend_general_settings['system_overview'] : get_label("system_overview", "System Overview")}}
                </h5>
                <h2 class="">
                    {{ !empty($frontend_general_settings['discover_our_system']) ? $frontend_general_settings['discover_our_system'] : get_label("discover_our_system", "Discover Our System")}}
                </h2>
            </div>
            <div id="myCarousel" class="carousel slide image-box" data-bs-ride="carousel">
                <div class="carousel-inner">
                    {{-- @dd($general_settings) --}}
                    <div class="carousel-item active">
                        <img src="{{ !empty($frontend_general_settings['carousel_image1_file']) ? asset($frontend_general_settings['carousel_image1_file']) : '/assets/front-end/img/gallery/ts1.png' }}"
                            class="d-block w-100 img-fluid" alt="Image 1">
                    </div>
                    <div class="carousel-item">
                        <img src="{{ !empty($frontend_general_settings['carousel_image2_file']) ? asset($frontend_general_settings['carousel_image2_file']) : '/assets/front-end/img/gallery/ts2.png' }}"
                            class="d-block w-100 img-fluid" alt="Image 2">
                    </div>
                    <div class="carousel-item">
                        <img src="{{ !empty($frontend_general_settings['carousel_image3_file']) ? asset($frontend_general_settings['carousel_image3_file']) : '/assets/front-end/img/gallery/ts3.png' }}"
                            class="d-block w-100 img-fluid" alt="Image 3">
                    </div>
                    <div class="carousel-item">
                        <img src="{{ !empty($frontend_general_settings['carousel_image4_file']) ? asset($frontend_general_settings['carousel_image4_file']) : '/assets/front-end/img/gallery/ts4.png'}}"
                            class="d-block w-100 img-fluid" alt="Image 4">
                    </div>
                </div>
                <button class="carousel-control-prev" type="button" data-bs-target="#myCarousel" data-bs-slide="prev">
                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                </button>
                <button class="carousel-control-next" type="button" data-bs-target="#myCarousel" data-bs-slide="next">
                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                </button>
            </div>
        </div>
    </section>

    <!-- Call To Action 1 - Bootstrap Brain Component -->
    <section id="contact-cta-section" class="contact-cta-section bg-gray-900">
        <div class="container-fluid ">
            <div class="row justify-content-center">
                <div class="col-lg-8 col-md-10">
                    <div class="cta-wrapper d-flex justify-content-between align-items-center">
                        <div class="details-wrapper">
                            <h2>{{ get_label('aboutUsCTA', 'Ready to experience the ' . $general_settings['company_title'] . 'difference? Sign up and see how we can help your team achieve more!') }}
                            </h2>
                        </div>
                    </div>
                </div>

                <div class="col-md-2 col-lg-2">
                    <a href="{{ route('login') }}"
                        class="btn btn-primary btn-lg mt-4 ms-1 "><?= get_label('get_started', 'Get Started') ?></a>
                </div>
            </div>
        </div>
    </section>

    <script src="assets/lightbox/lightbox.min.js"></script>

@endsection