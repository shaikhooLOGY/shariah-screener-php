<?php

function ui_card($title, $content, $class = '') {
    return "
    <div class='glass-card rounded-xl p-6 {$class}'>
        " . ($title ? "<h3 class='text-lg font-semibold mb-4'>{$title}</h3>" : '') . "
        <div>{$content}</div>
    </div>";
}

function ui_button($label, $type = 'primary', $class = '') {
    if (is_array($class)) {
        // Handle array format like ['href' => '/path']
        $href = $class['href'] ?? '';
        $extraClass = '';
        unset($class['href']);
        if (!empty($class)) {
            $extraClass = ' ' . implode(' ', $class);
        }
        $class = $extraClass;
    } else {
        $href = '';
    }

    $colors = [
        'primary' => 'bg-indigo-600 text-white hover:bg-indigo-700',
        'secondary' => 'bg-gray-200 text-gray-900 hover:bg-gray-300',
        'danger' => 'bg-red-600 text-white hover:bg-red-700',
        'success' => 'bg-green-600 text-white hover:bg-green-700',
        'link' => 'text-indigo-600 hover:text-indigo-800 underline',
        'soft' => 'bg-gray-100 text-gray-900 hover:bg-gray-200',
        'ghost' => 'text-gray-600 hover:text-gray-900 hover:bg-gray-100'
    ];

    $baseClasses = 'inline-flex items-center justify-center rounded-lg border border-transparent px-4 py-2 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2';

    if ($href) {
        return "<a href='{$href}' class='{$baseClasses} {$colors[$type]} {$class}'>{$label}</a>";
    } else {
        return "<button class='{$baseClasses} {$colors[$type]} {$class}'>{$label}</button>";
    }
}

function ui_badge($label, $type = 'default', $class = '') {
    $colors = [
        'default' => 'bg-gray-100 text-gray-800',
        'primary' => 'bg-indigo-100 text-indigo-800',
        'success' => 'bg-green-100 text-green-800',
        'warning' => 'bg-yellow-100 text-yellow-800',
        'danger' => 'bg-red-100 text-red-800',
        'info' => 'bg-blue-100 text-blue-800'
    ];

    return "<span class='inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {$colors[$type]} {$class}'>{$label}</span>";
}

function ui_chip($label, $type = 'default', $class = '') {
    $colors = [
        'default' => 'bg-gray-100 text-gray-800',
        'primary' => 'bg-indigo-100 text-indigo-800',
        'success' => 'bg-green-100 text-green-800',
        'warning' => 'bg-yellow-100 text-yellow-800',
        'danger' => 'bg-red-100 text-red-800'
    ];

    return "<span class='inline-flex items-center rounded-full px-3 py-1 text-sm font-medium {$colors[$type]} {$class}'>{$label}</span>";
}

function ui_table($headers, $rows, $class = '') {
    $html = "<div class='overflow-x-auto'><table class='min-w-full divide-y divide-gray-200 {$class}'>";

    if ($headers) {
        $html .= "<thead class='bg-gray-50'><tr>";
        foreach ($headers as $header) {
            $html .= "<th class='px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider'>{$header}</th>";
        }
        $html .= "</tr></thead>";
    }

    $html .= "<tbody class='bg-white divide-y divide-gray-200'>";
    foreach ($rows as $row) {
        $html .= "<tr>";
        foreach ($row as $cell) {
            $html .= "<td class='px-6 py-4 whitespace-nowrap text-sm text-gray-900'>{$cell}</td>";
        }
        $html .= "</tr>";
    }
    $html .= "</tbody></table></div>";

    return $html;
}

function ui_form_field($label, $input, $class = '') {
    return "
    <div class='{$class}'>
        <label class='block text-sm font-medium text-gray-700 mb-2'>{$label}</label>
        {$input}
    </div>";
}

function ui_select($name, $options, $selected = '', $class = '') {
    $html = "<select name='{$name}' class='block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500 {$class}'>";

    foreach ($options as $value => $label) {
        $isSelected = $selected === $value ? 'selected' : '';
        $html .= "<option value='{$value}' {$isSelected}>{$label}</option>";
    }

    $html .= "</select>";
    return $html;
}

function ui_input($name, $type = 'text', $value = '', $placeholder = '', $class = '') {
    return "<input type='{$type}' name='{$name}' value='{$value}' placeholder='{$placeholder}' class='block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500 {$class}'>";
}

function ui_textarea($name, $value = '', $placeholder = '', $rows = 4, $class = '') {
    return "<textarea name='{$name}' rows='{$rows}' placeholder='{$placeholder}' class='block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500 {$class}'>{$value}</textarea>";
}

function ui_modal($id, $title, $content, $footer = '') {
    return "
    <div x-show='modals.{$id}' class='fixed inset-0 z-50 overflow-y-auto' style='display: none;'>
        <div class='flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0'>
            <div class='fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75' x-on:click='modals.{$id} = false'></div>
            <div class='inline-block w-full max-w-lg p-6 my-8 overflow-hidden text-left align-middle transition-all transform bg-white shadow-xl rounded-2xl'>
                <div class='mt-3'>
                    <h3 class='text-lg font-medium text-gray-900'>{$title}</h3>
                    <div class='mt-4'>{$content}</div>
                </div>
                " . ($footer ? "<div class='mt-5 sm:mt-6'>{$footer}</div>" : '') . "
            </div>
        </div>
    </div>";
}

function ui_alert($tone, $message) {
    $colors = [
        'success' => 'bg-green-50 border-green-200 text-green-800',
        'danger' => 'bg-red-50 border-red-200 text-red-800',
        'warning' => 'bg-yellow-50 border-yellow-200 text-yellow-800',
        'info' => 'bg-blue-50 border-blue-200 text-blue-800'
    ];

    return "<div class='rounded-lg border p-4 {$colors[$tone]}'>{$message}</div>";
}

function ui_breadcrumbs($breadcrumbs) {
    $html = '<nav class="flex" aria-label="Breadcrumb"><ol class="flex items-center space-x-2">';

    foreach ($breadcrumbs as $index => $crumb) {
        if ($index > 0) {
            $html .= '<li><span class="text-gray-400">/</span></li>';
        }

        if (isset($crumb['href'])) {
            $html .= "<li><a href='{$crumb['href']}' class='text-gray-500 hover:text-gray-700'>{$crumb['label']}</a></li>";
        } else {
            $html .= "<li><span class='text-gray-900 font-medium'>{$crumb['label']}</span></li>";
        }
    }

    $html .= '</ol></nav>';
    return $html;
}

function ui_tabs($tabs, $active = '') {
    $html = '<div class="border-b border-gray-200"><nav class="-mb-px flex space-x-8">';

    foreach ($tabs as $tab) {
        $isActive = $active === $tab['id'];
        $activeClass = $isActive ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300';
        $html .= "<a href='{$tab['href']}' class='whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm {$activeClass}'>{$tab['label']}</a>";
    }

    $html .= '</nav></div>';
    return $html;
}

function ui_hero($title, $subtitle = '', $actions = '') {
    return "
    <div class='text-center py-12'>
        <h1 class='text-4xl font-bold text-gray-900 mb-4'>{$title}</h1>
        " . ($subtitle ? "<p class='text-xl text-gray-600 mb-8'>{$subtitle}</p>" : '') . "
        " . ($actions ? "<div class='flex justify-center gap-4'>{$actions}</div>" : '') . "
    </div>";
}

function ui_grid($items, $columns = 3, $class = '') {
    $gridClass = "grid gap-6 ";
    switch ($columns) {
        case 1: $gridClass .= "grid-cols-1"; break;
        case 2: $gridClass .= "grid-cols-1 md:grid-cols-2"; break;
        case 3: $gridClass .= "grid-cols-1 md:grid-cols-2 lg:grid-cols-3"; break;
        case 4: $gridClass .= "grid-cols-1 md:grid-cols-2 lg:grid-cols-4"; break;
    }

    return "<div class='{$gridClass} {$class}'>{$items}</div>";
}

function ui_loading($class = '') {
    return "<div class='animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600 {$class}'></div>";
}

function ui_empty_state($title, $message, $action = '') {
    return "
    <div class='text-center py-12'>
        <div class='mx-auto h-12 w-12 text-gray-400 mb-4'>
            <svg fill='none' viewBox='0 0 24 24' stroke='currentColor'>
                <path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4' />
            </svg>
        </div>
        <h3 class='text-lg font-medium text-gray-900 mb-2'>{$title}</h3>
        <p class='text-gray-500 mb-6'>{$message}</p>
        " . ($action ? $action : '') . "
    </div>";
}

function ui_kpi($title, $value, $status) {
    $statusClass = 'text-gray-500';
    $statusIcon = '•';

    if (str_starts_with($status, '+')) {
        $statusClass = 'text-green-600';
        $statusIcon = '✓';
    } elseif (str_starts_with($status, '-')) {
        $statusClass = 'text-red-600';
        $statusIcon = '✗';
    }

    return "<div class='rounded-2xl border border-gray-200 bg-white p-4 shadow-sm'>
        <div class='flex items-center justify-between'>
            <div>
                <p class='text-sm font-medium text-gray-500'>{$title}</p>
                <p class='text-2xl font-bold text-gray-900'>{$value}</p>
            </div>
            <div class='text-right'>
                <span class='text-xs {$statusClass}'>{$statusIcon} " . substr($status, 1) . "</span>
            </div>
        </div>
    </div>";
}
