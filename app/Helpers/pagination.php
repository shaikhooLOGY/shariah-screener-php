<?php

function paginate_query($pdo, $query, $params = [], $page = 1, $perPage = 25) {
    $page = max(1, (int)$page);
    $perPage = max(1, min(100, (int)$perPage));
    $offset = ($page - 1) * $perPage;

    // Get total count
    $countQuery = preg_replace('/SELECT\s+.*?\s+FROM/i', 'SELECT COUNT(*) FROM', $query);
    $countQuery = preg_replace('/\s+ORDER\s+BY\s+.*$/i', '', $countQuery);
    $countQuery = preg_replace('/\s+LIMIT\s+.*$/i', '', $countQuery);

    $countStmt = $pdo->prepare($countQuery);
    $countStmt->execute($params);
    $total = (int)$countStmt->fetchColumn();

    // Get paginated results
    $paginatedQuery = $query . " LIMIT :limit OFFSET :offset";
    $stmt = $pdo->prepare($paginatedQuery);
    $stmt->execute(array_merge($params, [
        ':limit' => $perPage,
        ':offset' => $offset
    ]));

    $items = $stmt->fetchAll();

    return [
        'items' => $items,
        'pagination' => [
            'page' => $page,
            'per_page' => $perPage,
            'total' => $total,
            'total_pages' => ceil($total / $perPage),
            'has_next' => $page < ceil($total / $perPage),
            'has_prev' => $page > 1
        ]
    ];
}

function pagination_links($pagination, $baseUrl = '') {
    $html = '<div class="flex items-center justify-between">';

    // Previous button
    if ($pagination['has_prev']) {
        $prevUrl = $baseUrl . '?page=' . ($pagination['page'] - 1) . '&per=' . $pagination['per_page'];
        $html .= '<a href="' . htmlspecialchars($prevUrl) . '" class="px-3 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-l-md hover:bg-gray-50">Previous</a>';
    } else {
        $html .= '<span class="px-3 py-2 text-sm font-medium text-gray-300 bg-gray-100 border border-gray-300 rounded-l-md cursor-not-allowed">Previous</span>';
    }

    // Page info
    $html .= '<span class="px-3 py-2 text-sm text-gray-700 bg-white border-t border-b border-gray-300">';
    $html .= 'Page ' . $pagination['page'] . ' of ' . $pagination['total_pages'] . ' (' . $pagination['total'] . ' total)';
    $html .= '</span>';

    // Next button
    if ($pagination['has_next']) {
        $nextUrl = $baseUrl . '?page=' . ($pagination['page'] + 1) . '&per=' . $pagination['per_page'];
        $html .= '<a href="' . htmlspecialchars($nextUrl) . '" class="px-3 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-r-md hover:bg-gray-50">Next</a>';
    } else {
        $html .= '<span class="px-3 py-2 text-sm font-medium text-gray-300 bg-gray-100 border border-gray-300 rounded-r-md cursor-not-allowed">Next</span>';
    }

    $html .= '</div>';
    return $html;
}