<?php
return [
    'skin' => $_ENV['UI_SKIN'] ?? 'GandFade', // 'GandFade' | 'AuroraGlass' | 'NeutralPro'
    'nav_position' => 'top', // fixed under header
    'density' => 'compact',  // compact paddings everywhere
    'skins' => [
        'GandFade' => [
            'name' => 'GandFade',
            'description' => 'Premium gradient fade with glossy elements',
            'css_vars' => [
                '--color-primary' => '#6366f1',
                '--color-primary-dark' => '#4f46e5',
                '--color-secondary' => '#8b5cf6',
                '--color-accent' => '#ec4899',
                '--gradient-primary' => 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
                '--gradient-secondary' => 'linear-gradient(135deg, #f093fb 0%, #f5576c 100%)',
                '--shadow-sm' => '0 1px 2px 0 rgb(0 0 0 / 0.05)',
                '--shadow-md' => '0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1)',
                '--shadow-lg' => '0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1)',
                '--shadow-xl' => '0 20px 25px -5px rgb(0 0 0 / 0.1), 0 8px 10px -6px rgb(0 0 0 / 0.1)',
                '--border-radius' => '0.75rem',
                '--border-radius-lg' => '1rem',
                '--border-radius-xl' => '1.5rem',
            ]
        ],
        'AuroraGlass' => [
            'name' => 'AuroraGlass',
            'description' => 'Glassmorphism with blurred elements',
            'css_vars' => [
                '--color-primary' => '#06b6d4',
                '--color-primary-dark' => '#0891b2',
                '--color-secondary' => '#3b82f6',
                '--color-accent' => '#8b5cf6',
                '--gradient-primary' => 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
                '--gradient-secondary' => 'linear-gradient(135deg, #f093fb 0%, #f5576c 100%)',
                '--shadow-sm' => '0 1px 3px 0 rgb(0 0 0 / 0.1), 0 1px 2px -1px rgb(0 0 0 / 0.1)',
                '--shadow-md' => '0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1)',
                '--shadow-lg' => '0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1)',
                '--shadow-xl' => '0 20px 25px -5px rgb(0 0 0 / 0.1), 0 8px 10px -6px rgb(0 0 0 / 0.1)',
                '--border-radius' => '1rem',
                '--border-radius-lg' => '1.5rem',
                '--border-radius-xl' => '2rem',
            ]
        ],
        'NeutralPro' => [
            'name' => 'NeutralPro',
            'description' => 'Clean enterprise design',
            'css_vars' => [
                '--color-primary' => '#374151',
                '--color-primary-dark' => '#1f2937',
                '--color-secondary' => '#6b7280',
                '--color-accent' => '#3b82f6',
                '--gradient-primary' => 'linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%)',
                '--gradient-secondary' => 'linear-gradient(135deg, #e2e8f0 0%, #cbd5e1 100%)',
                '--shadow-sm' => '0 1px 2px 0 rgb(0 0 0 / 0.05)',
                '--shadow-md' => '0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1)',
                '--shadow-lg' => '0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1)',
                '--shadow-xl' => '0 20px 25px -5px rgb(0 0 0 / 0.1), 0 8px 10px -6px rgb(0 0 0 / 0.1)',
                '--border-radius' => '0.375rem',
                '--border-radius-lg' => '0.5rem',
                '--border-radius-xl' => '0.75rem',
            ]
        ]
    ]
];