<?php

/** custom taskhub config */

return [

    /*
    |--------------------------------------------------------------------------
    | Project status labels
    |--------------------------------------------------------------------------
    */

    'project_status_labels' => [
        'completed' => "success",
        "onhold" => "warning",
        "ongoing" => "info",
        "started" => "primary",
        "cancelled" => "danger"
    ],

    'task_status_labels' => [
        'completed' => "success",
        "onhold" => "warning",
        "started" => "primary",
        "cancelled" => "danger",
        "ongoing" => "info"
    ],

    'role_labels' => [
        'admin' => "info",
        "Super Admin" => "danger",
        "HR" => "primary",
        "member" => "warning",
        'default' => "dark"
    ],

    'priority_labels' => [
        'low' => "success",
        "high" => "danger",
        "medium" => "warning"
    ],

    'permissions' => [
        'Projects' =>  array('create_projects', 'manage_projects', 'edit_projects', 'delete_projects'),
        'Tasks' =>  array('create_tasks', 'manage_tasks', 'edit_tasks', 'delete_tasks'),
        'Statuses' =>  array('create_statuses', 'manage_statuses', 'edit_statuses', 'delete_statuses'),
        'Priorities' =>  array('create_priorities', 'manage_priorities', 'edit_priorities', 'delete_priorities'),
        'Tags' =>  array('create_tags', 'manage_tags', 'edit_tags', 'delete_tags'),
        'Users' =>  array('create_users', 'manage_users', 'edit_users', 'delete_users'),
        'Clients' =>  array('create_clients', 'manage_clients', 'edit_clients', 'delete_clients'),
        'Workspaces' =>  array('create_workspaces', 'manage_workspaces', 'edit_workspaces', 'delete_workspaces'),
        'Meetings' =>  array('create_meetings', 'manage_meetings', 'edit_meetings', 'delete_meetings'),
        'Contracts' =>  array('create_contracts', 'manage_contracts', 'edit_contracts', 'delete_contracts'),
        'Contract_types' =>  array('create_contract_types', 'manage_contract_types', 'edit_contract_types', 'delete_contract_types'),
        'Timesheet' =>  array('create_timesheet', 'manage_timesheet', 'delete_timesheet'),
        'Media' =>  array('create_media', 'manage_media', 'delete_media'),
        'Payslips' =>  array('create_payslips', 'manage_payslips', 'edit_payslips', 'delete_payslips'),
        'Allowances' =>  array('create_allowances', 'manage_allowances', 'edit_allowances', 'delete_allowances'),
        'Deductions' =>  array('create_deductions', 'manage_deductions', 'edit_deductions', 'delete_deductions'),
        'Payment methods' =>  array('create_payment_methods', 'manage_payment_methods', 'edit_payment_methods', 'delete_payment_methods'),
        'Activity Log' =>  array('manage_activity_log', 'delete_activity_log'),
        'Estimates Invoices' =>  array('create_estimates_invoices', 'manage_estimates_invoices', 'edit_estimates_invoices', 'delete_estimates_invoices'),
        'Payments' =>  array('create_payments', 'manage_payments', 'edit_payments', 'delete_payments'),
        'Taxes' =>  array('create_taxes', 'manage_taxes', 'edit_taxes', 'delete_taxes'),
        'Units' =>  array('create_units', 'manage_units', 'edit_units', 'delete_units'),
        'Items' =>  array('create_items', 'manage_items', 'edit_items', 'delete_items'),
        'Expenses' =>  array('create_expenses', 'manage_expenses', 'edit_expenses', 'delete_expenses'),
        'Expense types' =>  array('create_expense_types', 'manage_expense_types', 'edit_expense_types', 'delete_expense_types'),
        'Milestones' =>  array('create_milestones', 'manage_milestones', 'edit_milestones', 'delete_milestones'),
        'System Notifications' =>  array('manage_system_notifications', 'delete_system_notifications'),
        'Announcements' =>  array('create_announcements', 'manage_announcements', 'edit_announcements', 'delete_announcements'),
        'Leads' =>  array('create_leads', 'manage_leads', 'edit_leads', 'delete_leads'),
        'Emails and Email Template' => array('send_email', 'create_email_template', 'manage_email_template', 'delete_email_template'),
        'Candidates' => array('create_candidate',  'manage_candidate', 'edit_candidate', 'delete_candidate'),
        'Candidate Statuses' => array('create_candidate_status',   'manage_candidate_status', 'edit_candidate_status', 'delete_candidate_status'),
        'Interviews' => array('create_interview',  'manage_interview', 'edit_interview', 'delete_interview')
    ],

    //Modules
    'modules' => [
        'tasks' => ['icon' => 'bx bx-task', 'description' => 'Manage tasks and assignments efficiently'],
        'notes' => ['icon' => 'bx bx-note', 'description' => 'Take and organize notes for better productivity'],
        'meetings' => ['icon' => 'bx bx-calendar-event', 'description' => 'Schedule and organize meetings with team members'],
        'chat' => ['icon' => 'bx bx-chat', 'description' => 'Communicate with team members in real-time'],
        'todos' => ['icon' => 'bx bx-list-ul', 'description' => 'Create and manage to-do lists for tasks and projects'],
        'contracts' => ['icon' => 'bx bx-news', 'description' => 'Manage contracts and agreements with clients'],
        'payslips' => ['icon' => 'bx bx-box', 'description' => 'View and manage payslips for employees'],
        'finance' => ['icon' => 'bx bx-dollar', 'description' => 'Create and Manange Expenses Payments and Invoice Estimates'],
    ],

];
