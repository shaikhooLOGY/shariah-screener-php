<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Accept');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once __DIR__.'/../../app/Helpers/functions.php';

$q = trim($_GET['q'] ?? '');
if (!$q) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing query parameter "q"']);
    exit;
}

// Database connection
try {
    $pdo = db_pdo();
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed', 'message' => $e->getMessage()]);
    exit;
}

// Search logic: first try exact ticker match, then fuzzy name match
try {
    // Exact ticker match
    $stmt = $pdo->prepare("
        SELECT c.id, c.ticker, c.name
        FROM companies c
        WHERE UPPER(c.ticker) = UPPER(?)
        LIMIT 1
    ");
    $stmt->execute([$q]);
    $company = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$company) {
        // Fuzzy name match
        $stmt = $pdo->prepare("
            SELECT c.id, c.ticker, c.name
            FROM companies c
            WHERE UPPER(c.name) LIKE UPPER(?)
            LIMIT 1
        ");
        $stmt->execute(['%' . $q . '%']);
        $company = $stmt->fetch(PDO::FETCH_ASSOC);
    }

    if (!$company) {
        echo json_encode(['not_found' => true, 'query' => $q]);
        exit;
    }

    // Get financial data from filings (legacy approach)
    $stmt = $pdo->prepare("
        SELECT f.total_assets, f.total_debt, f.interest_income
        FROM filings f
        WHERE f.company_id = ?
        ORDER BY f.period DESC
        LIMIT 1
    ");
    $stmt->execute([$company['id']]);
    $filing = $stmt->fetch(PDO::FETCH_ASSOC);

    $fields = [];
    if ($filing) {
        $assets = (float)($filing['total_assets'] ?: 0);
        $debt = (float)($filing['total_debt'] ?: 0);
        $interest = (float)($filing['interest_income'] ?: 0);

        $fields = [
            ['k' => 'Debt/Assets %', 'v' => $assets > 0 ? number_format(($debt / $assets) * 100, 2) . '%' : 'N/A'],
            ['k' => 'Interest Income', 'v' => number_format($interest, 0)],
            ['k' => 'Total Assets', 'v' => number_format($assets, 0)],
            ['k' => 'Total Debt', 'v' => number_format($debt, 0)],
        ];
    }

    // Build response
    $response = [
        'ticker' => $company['ticker'],
        'company' => $company['name'],
        'updated' => 'Latest Filing',
        'status' => 'DATA_FOUND',
        'fields' => $fields
    ];

    echo json_encode($response);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Query failed', 'message' => $e->getMessage()]);
}
?>