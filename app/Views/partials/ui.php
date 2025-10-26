<?php

if (!function_exists('ui_attr')) {
    function ui_attr(array $attrs): string {
        $out = [];
        foreach ($attrs as $key => $value) {
            if ($value === null) {
                continue;
            }
            if (is_bool($value)) {
                if ($value) {
                    $out[] = htmlspecialchars($key, ENT_QUOTES);
                }
                continue;
            }
            $out[] = sprintf('%s="%s"', htmlspecialchars($key, ENT_QUOTES), htmlspecialchars((string)$value, ENT_QUOTES));
        }
        return $out ? ' ' . implode(' ', $out) : '';
    }

    function ui_badge(string $text, string $tone = 'neutral'): string {
        $tones = [
            'neutral' => 'bg-surface-200 text-surface-700 dark:bg-surface-700 dark:text-surface-200',
            'success' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-200',
            'warn'    => 'bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-200',
            'danger'  => 'bg-rose-100 text-rose-700 dark:bg-rose-500/20 dark:text-rose-200',
            'info'    => 'bg-indigo-100 text-indigo-700 dark:bg-indigo-500/20 dark:text-indigo-200',
        ];
        $classes = $tones[$tone] ?? $tones['neutral'];
        return sprintf('<span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-medium %s">%s</span>', $classes, htmlspecialchars($text));
    }

    function ui_button(string $text, string $variant = 'primary', array $attrs = []): string {
        $base = 'inline-flex items-center justify-center gap-2 rounded-full px-4 py-2 text-sm font-semibold transition focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-indigo-500 disabled:opacity-60 disabled:cursor-not-allowed';
        $variants = [
            'primary' => 'bg-indigo-600 text-white hover:bg-indigo-500 dark:bg-indigo-500 dark:hover:bg-indigo-400',
            'soft'    => 'bg-indigo-500/10 text-indigo-600 hover:bg-indigo-500/15 dark:text-indigo-200 dark:bg-indigo-500/20 dark:hover:bg-indigo-500/30',
            'ghost'   => 'bg-transparent text-surface-600 hover:bg-surface-200 dark:text-surface-100 dark:hover:bg-surface-700',
            'link'    => 'bg-transparent text-indigo-600 hover:text-indigo-500 dark:text-indigo-300',
            'danger'  => 'bg-rose-600 text-white hover:bg-rose-500',
        ];
        $classes = $base . ' ' . ($variants[$variant] ?? $variants['primary']);
        $attrs['class'] = ($attrs['class'] ?? '') . ' ' . $classes;
        $tag = isset($attrs['href']) ? 'a' : 'button';
        return sprintf('<%1$s%2$s>%3$s</%1$s>', $tag, ui_attr($attrs), htmlspecialchars($text));
    }

    function ui_input(
        string $name,
        string $label,
        string $type = 'text',
        string $value = '',
        string $help = '',
        array $errors = [],
        array $attrs = []
    ): string {
        $id = $attrs['id'] ?? $name;
        $errorsForField = $errors[$name] ?? [];
        $invalid = !empty($errorsForField);
        $attrs = array_merge([
            'type' => $type,
            'name' => $name,
            'id'   => $id,
            'value'=> $value,
            'class'=> 'mt-1 block w-full rounded-xl border border-surface-300 bg-white px-4 py-2 text-sm text-surface-900 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-surface-700 dark:bg-surface-800 dark:text-surface-100'
        ], $attrs);
        if ($invalid) {
            $attrs['class'] .= ' border-rose-500 focus:border-rose-500 focus:ring-rose-500';
        }
        $html = '<label class="block text-sm font-semibold text-surface-700 dark:text-surface-200" for="'.htmlspecialchars($id).'">'.htmlspecialchars($label).'</label>';
        $html .= '<input'.ui_attr($attrs).' />';
        if ($help) {
            $html .= '<p class="mt-1 text-xs text-surface-500">'.$help.'</p>';
        }
        if ($invalid) {
            $html .= '<p class="mt-1 text-xs text-rose-500">'.htmlspecialchars(implode(', ', (array)$errorsForField)).'</p>';
        }
        return '<div class="space-y-1">'.$html.'</div>';
    }

    function ui_textarea(string $name, string $label, string $value = '', string $help = '', array $errors = [], array $attrs = []): string {
        $attrs['class'] = ($attrs['class'] ?? '') . ' mt-1 block w-full rounded-xl border border-surface-300 bg-white px-4 py-2 text-sm text-surface-900 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-surface-700 dark:bg-surface-800 dark:text-surface-100';
        $attrs['name'] = $name;
        $attrs['id'] = $attrs['id'] ?? $name;
        $invalid = !empty($errors[$name] ?? []);
        if ($invalid) {
            $attrs['class'] .= ' border-rose-500 focus:border-rose-500 focus:ring-rose-500';
        }
        $field = '<label class="block text-sm font-semibold text-surface-700 dark:text-surface-200" for="'.htmlspecialchars($attrs['id']).'">'.htmlspecialchars($label).'</label>';
        $field .= '<textarea'.ui_attr($attrs).'>'.htmlspecialchars($value).'</textarea>';
        if ($help) {
            $field .= '<p class="mt-1 text-xs text-surface-500">'.$help.'</p>';
        }
        if ($invalid) {
            $field .= '<p class="mt-1 text-xs text-rose-500">'.htmlspecialchars(implode(', ', (array)$errors[$name])).'</p>';
        }
        return '<div class="space-y-1">'.$field.'</div>';
    }

    function ui_select(string $name, string $label, array $options, string $selected = '', string $help = '', array $errors = [], array $attrs = []): string {
        $attrs['name'] = $name;
        $attrs['id'] = $attrs['id'] ?? $name;
        $attrs['class'] = ($attrs['class'] ?? '') . ' mt-1 block w-full rounded-xl border border-surface-300 bg-white px-4 py-2 text-sm text-surface-900 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-surface-700 dark:bg-surface-800 dark:text-surface-100';
        $invalid = !empty($errors[$name] ?? []);
        if ($invalid) {
            $attrs['class'] .= ' border-rose-500 focus:border-rose-500 focus:ring-rose-500';
        }
        $field = '<label class="block text-sm font-semibold text-surface-700 dark:text-surface-200" for="'.htmlspecialchars($attrs['id']).'">'.htmlspecialchars($label).'</label>';
        $field .= '<select'.ui_attr($attrs).'>';
        foreach ($options as $value => $text) {
            $field .= sprintf('<option value="%s"%s>%s</option>', htmlspecialchars((string)$value), $value == $selected ? ' selected' : '', htmlspecialchars((string)$text));
        }
        $field .= '</select>';
        if ($help) {
            $field .= '<p class="mt-1 text-xs text-surface-500">'.$help.'</p>';
        }
        if ($invalid) {
            $field .= '<p class="mt-1 text-xs text-rose-500">'.htmlspecialchars(implode(', ', (array)$errors[$name])).'</p>';
        }
        return '<div class="space-y-1">'.$field.'</div>';
    }

    function ui_toggle(string $name, string $label, bool $checked = false, string $help = '', array $attrs = []): string {
        $attrs['type'] = 'checkbox';
        $attrs['name'] = $name;
        $attrs['id'] = $attrs['id'] ?? $name;
        if ($checked) {
            $attrs['checked'] = true;
        }
        $attrs['class'] = ($attrs['class'] ?? '') . ' sr-only peer';
        $field = '<label class="flex items-center justify-between gap-4">';
        $field .= '<span class="text-sm font-semibold text-surface-700 dark:text-surface-200">'.htmlspecialchars($label).'</span>';
        $field .= '<span class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer items-center">';
        $field .= '<input'.ui_attr($attrs).'>';
        $field .= '<span class="absolute inset-y-0 left-0 h-full w-full rounded-full bg-surface-300 transition peer-checked:bg-indigo-500"></span>';
        $field .= '<span class="absolute left-1 top-1 h-4 w-4 rounded-full bg-white transition peer-checked:translate-x-5"></span>';
        $field .= '</span>';
        $field .= '</label>';
        if ($help) {
            $field .= '<p class="mt-1 text-xs text-surface-500">'.$help.'</p>';
        }
        return '<div class="space-y-1">'.$field.'</div>';
    }

    function ui_checkbox(string $name, string $label, bool $checked = false, array $attrs = []): string {
        $attrs['type'] = 'checkbox';
        $attrs['name'] = $name;
        $attrs['id'] = $attrs['id'] ?? $name;
        if ($checked) {
            $attrs['checked'] = true;
        }
        $attrs['class'] = ($attrs['class'] ?? '') . ' h-4 w-4 rounded border-surface-300 text-indigo-600 focus:ring-indigo-500';
        return '<label class="flex items-center gap-3 text-sm text-surface-700 dark:text-surface-200"><input'.ui_attr($attrs).'>'.htmlspecialchars($label).'</label>';
    }

    function ui_radio(string $name, string $label, string $value, string $current = '', array $attrs = []): string {
        $attrs['type'] = 'radio';
        $attrs['name'] = $name;
        $attrs['value'] = $value;
        $attrs['id'] = $attrs['id'] ?? $name.'_'.$value;
        if ($value === $current) {
            $attrs['checked'] = true;
        }
        $attrs['class'] = ($attrs['class'] ?? '') . ' h-4 w-4 border-surface-300 text-indigo-600 focus:ring-indigo-500';
        return '<label class="flex items-center gap-3 text-sm text-surface-700 dark:text-surface-200"><input'.ui_attr($attrs).'>'.htmlspecialchars($label).'</label>';
    }

    function ui_card(string $title, string $body, array $actions = [], array $attrs = []): string {
        $attrs['class'] = 'rounded-2xl bg-white p-6 shadow-sm ring-1 ring-surface-200 dark:bg-surface-900 dark:ring-surface-800 '.($attrs['class'] ?? '');
        $header = '<div class="flex items-start justify-between gap-4">';
        $header .= '<h2 class="text-lg font-semibold text-surface-900 dark:text-white">'.$title.'</h2>';
        if ($actions) {
            $header .= '<div class="flex flex-wrap items-center gap-2">'.implode('', $actions).'</div>';
        }
        $header .= '</div>';
        return '<section'.ui_attr($attrs).'><header class="mb-4">'.$header.'</header><div class="space-y-4">'.$body.'</div></section>';
    }

    function ui_kpi(string $label, string $value, ?string $delta = null): string {
        $deltaHtml = '';
        if ($delta !== null) {
            $tone = str_starts_with($delta, '-') ? 'text-rose-500' : 'text-emerald-500';
            $deltaHtml = '<span class="ml-auto text-xs font-semibold '.$tone.'">'.htmlspecialchars($delta).'</span>';
        }
        return sprintf(
            '<div class="rounded-2xl border border-surface-200 bg-white p-4 shadow-sm dark:border-surface-800 dark:bg-surface-900">
                <p class="text-xs font-semibold uppercase tracking-wide text-surface-500 dark:text-surface-400">%s%s</p>
                <p class="mt-2 text-2xl font-semibold text-surface-900 dark:text-white">%s</p>
            </div>',
            htmlspecialchars($label),
            $deltaHtml,
            htmlspecialchars($value)
        );
    }

    function ui_alert(string $tone, string $title, string $desc = ''): string {
        $tones = [
            'info' => 'border-indigo-300 bg-indigo-500/10 text-indigo-700 dark:border-indigo-500/40 dark:bg-indigo-500/15 dark:text-indigo-200',
            'success' => 'border-emerald-300 bg-emerald-500/10 text-emerald-700 dark:border-emerald-500/40 dark:bg-emerald-500/15 dark:text-emerald-200',
            'warn' => 'border-amber-300 bg-amber-500/10 text-amber-700 dark:border-amber-500/40 dark:bg-amber-500/15 dark:text-amber-200',
            'danger' => 'border-rose-300 bg-rose-500/10 text-rose-700 dark:border-rose-500/40 dark:bg-rose-500/15 dark:text-rose-200',
        ];
        $classes = $tones[$tone] ?? $tones['info'];
        return '<div class="rounded-2xl border px-4 py-3 '.$classes.'"><p class="text-sm font-semibold">'.$title.'</p>'.($desc ? '<p class="mt-1 text-sm/relaxed">'.$desc.'</p>' : '').'</div>';
    }

    function ui_empty(string $title, string $desc, ?string $cta = null): string {
        $ctaHtml = $cta ? '<div class="mt-4">'.$cta.'</div>' : '';
        return '<div class="flex flex-col items-center justify-center rounded-2xl border border-dashed border-surface-300 bg-white px-6 py-10 text-center dark:border-surface-700 dark:bg-surface-900"><p class="text-lg font-semibold text-surface-900 dark:text-white">'.$title.'</p><p class="mt-2 max-w-md text-sm text-surface-500">'.$desc.'</p>'.$ctaHtml.'</div>';
    }

    function ui_breadcrumbs(array $items): string {
        $parts = [];
        $lastIndex = array_key_last($items);
        foreach ($items as $index => $item) {
            $isLast = $index === $lastIndex;
            if ($isLast) {
                $parts[] = '<span aria-current="page" class="text-sm font-semibold text-indigo-600 dark:text-indigo-300">'.htmlspecialchars($item['label']).'</span>';
            } else {
                $parts[] = '<a class="text-sm text-surface-500 hover:text-indigo-600 dark:text-surface-400 dark:hover:text-indigo-300" href="'.htmlspecialchars($item['href']).'">'.htmlspecialchars($item['label']).'</a><svg class="mx-2 h-4 w-4 text-surface-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l3.999 3.999a1 1 0 010 1.414l-3.999 3.999a1 1 0 01-1.414 0z" clip-rule="evenodd"/></svg>';
            }
        }
        return '<nav class="flex items-center text-sm" aria-label="Breadcrumb">'.implode('', $parts).'</nav>';
    }

    function ui_tabs(array $items, string $active): string {
        $html = '<div class="flex gap-3 overflow-x-auto rounded-full bg-surface-200 p-1 dark:bg-surface-800/70">';
        foreach ($items as $item) {
            $isActive = $item['id'] === $active;
            $classes = $isActive ? 'bg-white text-indigo-600 dark:bg-surface-900 dark:text-indigo-300 shadow-sm' : 'text-surface-600 hover:text-indigo-600 dark:text-surface-300 dark:hover:text-indigo-300';
            $html .= sprintf('<a href="%s" class="inline-flex min-w-[120px] items-center justify-center rounded-full px-4 py-2 text-sm font-semibold transition %s">%s</a>',
                htmlspecialchars($item['href'] ?? '#'),
                $classes,
                htmlspecialchars($item['label'])
            );
        }
        $html .= '</div>';
        return $html;
    }

    function ui_modal(string $id, string $title, string $body, string $footer = ''): string {
        return <<<HTML
<div x-data="{ open: false }" x-on:open-modal.window="if(\$event.detail === '{$id}') open = true" x-on:keyup.escape.window="open=false">
  <div x-show="open" class="fixed inset-0 z-40 flex items-center justify-center bg-surface-900/60 p-4 backdrop-blur" x-transition.opacity>
    <div x-show="open" class="w-full max-w-lg rounded-2xl bg-white shadow-xl ring-1 ring-surface-200 dark:bg-surface-900 dark:ring-surface-800" x-transition.scale>
      <div class="flex items-center justify-between border-b border-surface-200 px-6 py-4 dark:border-surface-800">
        <h2 class="text-lg font-semibold text-surface-900 dark:text-white">{$title}</h2>
        <button type="button" class="rounded-full p-2 text-surface-500 hover:bg-surface-100 focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 dark:text-surface-300" x-on:click="open=false">
          <span class="sr-only">Close</span>
          ×
        </button>
      </div>
      <div class="px-6 py-4 text-sm text-surface-600 dark:text-surface-300">{$body}</div>
      <div class="flex justify-end gap-3 border-t border-surface-200 px-6 py-4 dark:border-surface-800">{$footer}</div>
    </div>
  </div>
</div>
HTML;
    }

    function ui_toast(string $tone, string $msg): string {
        $tones = [
            'info' => 'bg-indigo-600 text-white',
            'success' => 'bg-emerald-600 text-white',
            'warn' => 'bg-amber-500 text-white',
            'danger' => 'bg-rose-600 text-white',
        ];
        $classes = $tones[$tone] ?? $tones['info'];
        return '<div class="pointer-events-auto flex items-start gap-3 rounded-2xl px-4 py-3 text-sm shadow-lg '.$classes.'">'.$msg.'</div>';
    }

    function ui_table(array $columns, array $rows, array $options = []): string {
        $tableId = $options['id'] ?? 'table_'.uniqid();
        $html = '<div class="overflow-hidden rounded-2xl border border-surface-200 bg-white shadow-sm dark:border-surface-800 dark:bg-surface-900" x-data="tableEnhance(\''.$tableId.'\')" x-init="init()">';
        $html .= '<div class="overflow-x-auto">';
        $html .= '<table class="min-w-full text-left text-sm text-surface-600 dark:text-surface-200" data-table="'.$tableId.'">';
        $html .= '<thead class="sticky top-0 bg-surface-100 text-xs font-semibold uppercase tracking-wide text-surface-500 dark:bg-surface-800 dark:text-surface-300">';
        $html .= '<tr>';
        foreach ($columns as $column) {
            $sortable = !empty($column['sortable']);
            $html .= '<th scope="col" class="px-4 py-3'.($sortable ? ' cursor-pointer select-none' : '').'"'.($sortable ? ' data-sort="'.$column['key'].'"' : '').'>';
            $html .= htmlspecialchars($column['label']);
            if ($sortable) {
                $html .= ' <span class="sort-indicator" aria-hidden="true">↕</span>';
            }
            $html .= '</th>';
        }
        $html .= '</tr></thead><tbody class="divide-y divide-surface-200 dark:divide-surface-800">';
        foreach ($rows as $row) {
            $html .= '<tr class="hover:bg-surface-50 dark:hover:bg-surface-800/60">';
            foreach ($columns as $column) {
                $key = $column['key'];
                $html .= '<td class="px-4 py-3">'.($row[$key] ?? '').'</td>';
            }
            $html .= '</tr>';
        }
        $html .= '</tbody></table></div>';
        $html .= '<div class="flex items-center justify-between border-t border-surface-200 px-4 py-3 text-xs text-surface-500 dark:border-surface-800 dark:text-surface-300">';
        $html .= '<div class="space-x-2"><button type="button" class="rounded-full border border-surface-200 px-3 py-1 hover:bg-surface-100 dark:border-surface-700 dark:hover:bg-surface-800" x-on:click="prev()">Prev</button>';
        $html .= '<button type="button" class="rounded-full border border-surface-200 px-3 py-1 hover:bg-surface-100 dark:border-surface-700 dark:hover:bg-surface-800" x-on:click="next()">Next</button></div>';
        $html .= '<span x-text="summary"></span>';
        $html .= '</div></div>';
        return $html;
    }
}
