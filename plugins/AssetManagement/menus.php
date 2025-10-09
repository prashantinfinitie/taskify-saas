<?php

return [
    [
        'id'       => 'Assets Management',
        'label'    => get_label('assets', 'Assets'),
        'class'    => 'menu-item' . (request()->is('master-panel/assets/*') ? ' active open' : ''),
        'category' => get_label('utilities', 'Utilities'),
        'show' =>  auth()->user()->hasRole('admin') || auth()->user()->can('manage_assets')  ? 1 : 0,
        'badge' => '<span class="badge rounded-pill bg-label-info text-uppercase ms-2">' . get_label('plugin', 'Plugin') . '</span>',
        'icon'     => 'bx bx-desktop',
        'submenus' => [
            [
                'id'    => 'asset',
                'label' => get_label('assets', 'Assets'),
                'url'   => route('assets.index'),
                'class' => 'menu-item' . (request()->is('master-panel/assets/index') ? ' active' : ''),
                'show'  => isUser() ? 1 : 0,
            ],
            [
                'id'    => 'asset_category',
                'label' => get_label('assets_category', 'Assets Category'),
                'url'   => route('assets.category.index'),
                'class' => 'menu-item' . (request()->is('master-panel/assets/category/index') ? ' active' : ''),
                'show'  => isAdminOrHasAllDataAccess() || auth()->user()->can('manage_asset_categories') ? 1 : 0,
            ],
        ],
    ],
];



