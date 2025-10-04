<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Projects Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 0;
        }

        /* Header Styles */
        .report-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding: 20px;
            border-bottom: 2px solid #ddd;
        }

        .company-info {
            display: flex;
            align-items: center;
        }

        .company-logo {
            width: 400px;
            height: auto;
            margin-right: 10px;
        }

        .company-details h1 {
            margin: 0;
            color: #333;
        }

        .company-details p {
            margin: 5px 0;
            color: #666;
        }

        .report-info {
            text-align: right;
        }

        .report-info h2 {
            margin: 0;
            color: #333;
        }

        .report-info p {
            margin: 5px 0;
            color: #666;
        }

        /* Content Styles */
        .report-content {
            padding: 20px;
        }

        .section {
            margin-bottom: 30px;
        }

        .section-title {
            font-size: 18px;
            font-weight: bold;
            color: #333;
            border-bottom: 1px solid #ddd;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }

        /* Table Styles */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        th,
        td {
            padding: 8px 12px;
            text-align: left;
            border: 1px solid #ddd;
        }

        th {
            background-color: #f4f4f4;
        }

        .avatar {
            display: inline-block;
            margin-right: 5px;
        }

        .avatar img {
            border-radius: 50%;
            width: 30px;
            height: 30px;
        }


        /* Utility Classes */
        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .mb-10 {
            margin-bottom: 10px;
        }

        .mt-20 {
            margin-top: 20px;
        }

        .font-bold {
            font-weight: bold;
        }

        .text-large {
            font-size: 16px;
        }

        .text-small {
            font-size: 12px;
        }

        .text-muted {
            color: #777;
        }

        /* Status Badge Styles */
        .badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 12px;
        }

        .bg-label-secondary {
            background-color: #ebeef0;
            color: #8592a3;
        }

        .bg-label-success {
            background-color: #e8fadf;
            color: #71dd37;
        }

        .bg-label-info {
            background-color: #d7f5fc;
            color: #03c3ec;
        }

        .bg-label-warning {
            background-color: #fff2d6;
            color: #ffab00;
        }

        .bg-label-danger {
            background-color: #ffe0db;
            color: #ff3e1d;
        }

        .bg-label-primary {
            background-color: #e7e7ff;
            color: #696cff;
        }

        .bg-label-dark {
            background-color: #dcdfe1 !important;
            color: #233446 !important;
        }

        /* Avatar Styles */
        .avatar-container {
            display: flex;
            flex-wrap: wrap;
            gap: 4px;
        }

        .avatar {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            object-fit: cover;
            border: 1px solid #e0e0e0;
        }

        .avatar-container * {
            text-decoration: none;
            border: none;
        }

        .bg-primary {
            background-color: #696cff;
            color: white;
        }

        .bg-secondary {
            background-color: #8592a3;
            color: white;
        }

        .bg-success {
            background-color: #71dd37;
            color: white;
        }

        .bg-danger {
            background-color: #ff3e1d;
            color: white;
        }

        .bg-warning {
            background-color: #ffab00;
            color: #000;
        }

        .bg-info {
            background-color: #03c3ec;
            color: white;
        }

        .bg-light {
            background-color: #fcfdfd;
            color: #000;
        }

        .bg-dark {
            background-color: #233446;
            color: white;
        }

        .bg-gray {
            background-color: #f5f5f9;
            color: #000;
        }

        .summary-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 16px;
        }

        .summary-table td {
            padding: 8px 12px;
            border: 1px solid #ddd;
        }

        .summary-label {
            font-size: 14px;
            color: #666;
        }

        .summary-value {
            font-size: 16px;
            font-weight: bold;
            color: #333;
        }

        .summary-item {
            width: 20%;
            /* Adjust to fit your needs */
            text-align: center;
        }

        /* Print Styles */
        @media print {
            body {
                font-size: 12px;
            }

            table {
                page-break-inside: auto;
            }

            tr {
                page-break-inside: avoid;
                page-break-after: auto;
            }
        }
    </style>
</head>

<body>
    <header>
        <div class="company-info">
            <img src="{{ asset($general_settings['full_logo']) }}" alt="{{ $general_settings['company_title'] }}"
                class="company-logo">
            <div class="company-details">
                <h1>{{ $general_settings['company_title'] }}</h1>
                <span>{!! $general_settings['company_address'] !!}</span>
                <p>{{ $general_settings['support_email'] }}</p>
            </div>
        </div>
        <div class="report-info text-right">
            <h2>Projects Report</h2>
            <p>Date: {{ date('F d, Y h:m:s') }}</p>
            @php $authUser = getAuthenticatedUser(); @endphp
            <p>Generated by: {{ ucfirst($authUser->first_name) }} {{ ucfirst($authUser->last_name) }}</p>
        </div>
    </header>
    <main>
        <div class="report-content">
            <table class="summary-table">
                <tr>
                    <td class="summary-item">
                        <div class="summary-label">Total Projects</div>
                        <div class="summary-value">{{ $summary->total_projects }}</div>
                    </td>
                    <td class="summary-item">
                        <div class="summary-label">Total Tasks</div>
                        <div class="summary-value">{{ $summary->total_tasks }}</div>
                    </td>
                    <td class="summary-item">
                        <div class="summary-label">Total Team Members</div>
                        <div class="summary-value">{{ $summary->total_team_members }}</div>
                    </td>
                    <td class="summary-item">
                        <div class="summary-label">Avg. Overdue Days/Project</div>
                        <div class="summary-value">{{ $summary->average_overdue_days_per_project }}</div>
                    </td>
                    <td class="summary-item">
                        <div class="summary-label">Overdue Projects (%)</div>
                        <div class="summary-value">{{ $summary->overdue_projects_percentage }}</div>
                    </td>
                    <td class="summary-item">
                        <div class="summary-label">Total Overdue Days</div>
                        <div class="summary-value">{{ $summary->total_overdue_days }}</div>
                    </td>
                </tr>
            </table>
            <section>
                <h2 class="section-title">Projects Details</h2>
                <table>
                    <thead>
                        <tr>
                            <th rowspan="2">ID</th>
                            <th rowspan="2">Title</th>
                            <th rowspan="2">Description</th>
                            <th colspan="2">Dates</th>
                            <th rowspan="2">Status</th>
                            <th rowspan="2">Priority</th>
                            <th rowspan="2">Budget Total</th>
                            <th colspan="3">Time</th>
                            <th colspan="4">Tasks</th>
                            <th colspan="2">Team</th>
                            <!-- Group Clients and Total Clients under the "Clients" heading -->
                            <th colspan="2">Clients</th>
                            <th rowspan="2">Tags</th>
                            <th rowspan="2">Favorite</th>
                            <th rowspan="2">Created At</th>
                            <th rowspan="2">Updated At</th>
                        </tr>
                        <tr>
                            <th>Start Date</th>
                            <th>End Date</th>
                            <th>Total Days</th>
                            <th>Days Elapsed</th>
                            <th>Days Remaining</th>
                            <th>Total</th>
                            <th>Due</th>
                            <th>Overdue</th>
                            <th>Overdue Days</th>
                            <th>Members</th>
                            <th>Users</th>
                            <!-- Separate the "Clients" and "Total Clients" columns under their group -->
                            <th>Clients</th>
                            <th>Total Clients</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($projects as $project)
                            <tr>
                                <td>{{ $project->id }}</td>
                                <td>{{ $project->title }}</td>
                                <td>{!! $project->description !!}</td>
                                <td>{{ $project->start_date }}</td>
                                <td>{{ $project->end_date }}</td>
                                <td>{!! $project->status !!}</td>
                                <td>{!! $project->priority !!}</td>
                                <td>{{ $project->budget->total }}</td>
                                <td>{{ $project->time->total_days }}</td>
                                <td>{{ $project->time->days_elapsed }}</td>
                                <td>{{ $project->time->days_remaining }}</td>
                                <td>{{ $project->tasks->total }}</td>
                                <td>{{ $project->tasks->due }}</td>
                                <td>{{ $project->tasks->overdue }}</td>
                                <td>{{ $project->tasks->overdue_days }}</td>
                                <td>{{ $project->team->total_members }}</td>
                                <td>
                                    <div class="avatar-container">
                                        {!! $project->users !!}
                                    </div>
                                </td>
                                <!-- Add Clients and Total Clients columns in the corresponding row -->
                                <td>{{ $project->clients }}</td>
                                <td>{{ $project->total_clients }}</td>
                                <td>{{ implode(', ', $project->tags) }}</td>
                                <td>{{ $project->is_favorite ? 'Yes' : 'No' }}</td>
                                <td>{{ $project->created_at }}</td>
                                <td>{{ $project->updated_at }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

            </section>
            <section class="mt-20">
                <h2 class="section-title">Additional Information</h2>
                <p class="text-muted">This report was generated automatically. For any questions or concerns, please
                    contact admin for support.</p>
            </section>
        </div>

    </main>
</body>

</html>
