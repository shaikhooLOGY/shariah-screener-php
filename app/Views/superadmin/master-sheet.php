<?php
require_once __DIR__.'/../partials/ui.php';
$title = 'Superadmin · Master Sheet';
$flash = $flash ?? [];
$ratios = $ratios ?? [];
$filters = $filters ?? [];
$filterOptions = $filterOptions ?? [];
$pagination = $pagination ?? [];
$activityStats = $activityStats ?? [];
$csrf = $csrf ?? '';
ob_start();
?>

<div class="flex gap-6">
    <!-- Main Content -->
    <div class="flex-1">
        <!-- Header -->
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Master Sheet</h1>
                <p class="text-sm text-gray-600 dark:text-gray-400">Manage all financial ratios with full audit trail</p>
            </div>
            <div class="flex gap-3">
                <button class="btn-primary" onclick="showBulkImportModal()">Bulk Import</button>
                <button class="btn-secondary" onclick="showAutoLoadModal()">Auto Load</button>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4 mb-6">
            <form method="GET" class="grid grid-cols-1 md:grid-cols-5 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Company</label>
                    <input type="text" name="company" value="<?php echo htmlspecialchars($filters['company']); ?>"
                           class="w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-sm"
                           placeholder="Ticker or name">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Period</label>
                    <select name="period" class="w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-sm">
                        <option value="">All Periods</option>
                        <?php foreach ($filterOptions['periods'] ?? [] as $period): ?>
                            <option value="<?php echo htmlspecialchars($period); ?>" <?php echo $filters['period'] === $period ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($period); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Source</label>
                    <select name="source" class="w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-sm">
                        <option value="">All Sources</option>
                        <?php foreach ($filterOptions['sources'] ?? [] as $source): ?>
                            <option value="<?php echo htmlspecialchars($source); ?>" <?php echo $filters['source'] === $source ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($source); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Key</label>
                    <select name="key" class="w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-sm">
                        <option value="">All Keys</option>
                        <?php foreach ($filterOptions['keys'] ?? [] as $key): ?>
                            <option value="<?php echo htmlspecialchars($key); ?>" <?php echo $filters['key'] === $key ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($key); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="flex items-end gap-2">
                    <button type="submit" class="btn-primary">Filter</button>
                    <a href="/dashboard/superadmin/master-sheet" class="btn-secondary">Clear</a>
                </div>
            </form>
        </div>

        <!-- Table -->
        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Company</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Period</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Key</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Value</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Source</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Confidence</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Last Updated</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">By</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        <?php foreach ($ratios as $ratio): ?>
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                                    <?php echo htmlspecialchars($ratio['ticker']); ?>
                                    <div class="text-xs text-gray-500 dark:text-gray-400"><?php echo htmlspecialchars($ratio['name']); ?></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white"><?php echo htmlspecialchars($ratio['period']); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white"><?php echo htmlspecialchars($ratio['key']); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white"><?php echo number_format((float)$ratio['value'], 2); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                        <?php
                                        $sourceColors = [
                                            'engine' => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
                                            'manual' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
                                            'suggestion' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
                                            'import' => 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200'
                                        ];
                                        echo $sourceColors[$ratio['source']] ?? 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200';
                                        ?>">
                                        <?php echo htmlspecialchars($ratio['source']); ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                        <?php
                                        $confidenceColors = [
                                            'low' => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
                                            'med' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
                                            'high' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200'
                                        ];
                                        echo $confidenceColors[$ratio['confidence']] ?? 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200';
                                        ?>">
                                        <?php echo htmlspecialchars($ratio['confidence']); ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                    <?php echo date('M j, H:i', strtotime($ratio['updated_at'])); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                    <?php echo htmlspecialchars($ratio['by_user_name'] ?? 'System'); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <div class="flex gap-2">
                                        <button onclick="editRatio(<?php echo $ratio['id']; ?>, '<?php echo htmlspecialchars($ratio['value']); ?>', '<?php echo htmlspecialchars($ratio['confidence']); ?>')"
                                                class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300">Edit</button>
                                        <button onclick="deleteRatio(<?php echo $ratio['id']; ?>)"
                                                class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300">Delete</button>
                                        <button onclick="viewHistory(<?php echo $ratio['id']; ?>)"
                                                class="text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-300">History</button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <?php if ($pagination['totalPages'] > 1): ?>
                <div class="bg-white dark:bg-gray-800 px-4 py-3 border-t border-gray-200 dark:border-gray-700 sm:px-6">
                    <div class="flex items-center justify-between">
                        <div class="text-sm text-gray-700 dark:text-gray-300">
                            Showing <?php echo (($pagination['page'] - 1) * $pagination['perPage']) + 1; ?> to
                            <?php echo min($pagination['page'] * $pagination['perPage'], $pagination['total']); ?> of
                            <?php echo $pagination['total']; ?> results
                        </div>
                        <div class="flex gap-2">
                            <?php if ($pagination['page'] > 1): ?>
                                <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $pagination['page'] - 1])); ?>"
                                   class="btn-secondary">Previous</a>
                            <?php endif; ?>
                            <?php if ($pagination['page'] < $pagination['totalPages']): ?>
                                <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $pagination['page'] + 1])); ?>"
                                   class="btn-secondary">Next</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Activity Rail -->
    <div class="w-80 flex-shrink-0">
        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Recent Activity (24h)</h3>
            <div class="space-y-3">
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600 dark:text-gray-400">Edits</span>
                    <span class="text-sm font-medium text-gray-900 dark:text-white"><?php echo $activityStats['edits_24h'] ?? 0; ?></span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600 dark:text-gray-400">Deletes</span>
                    <span class="text-sm font-medium text-gray-900 dark:text-white"><?php echo $activityStats['deletes_24h'] ?? 0; ?></span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600 dark:text-gray-400">Imports</span>
                    <span class="text-sm font-medium text-gray-900 dark:text-white"><?php echo $activityStats['imports_24h'] ?? 0; ?></span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600 dark:text-gray-400">Auto-loads</span>
                    <span class="text-sm font-medium text-gray-900 dark:text-white"><?php echo $activityStats['engines_24h'] ?? 0; ?></span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Edit Ratio Modal -->
<div id="editRatioModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white dark:bg-gray-800">
        <div class="mt-3">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Edit Ratio</h3>
            <form method="POST" action="">
                <input type="hidden" name="csrf" value="<?php echo htmlspecialchars($csrf); ?>">
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Value</label>
                    <input type="number" step="0.01" name="value" id="editValue" required
                           class="w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-sm">
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Confidence</label>
                    <select name="confidence" id="editConfidence" class="w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-sm">
                        <option value="low">Low</option>
                        <option value="med" selected>Medium</option>
                        <option value="high">High</option>
                    </select>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Change Note</label>
                    <textarea name="note" rows="3" class="w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-sm"></textarea>
                </div>
                <div class="flex justify-end gap-3">
                    <button type="button" onclick="closeEditModal()" class="btn-secondary">Cancel</button>
                    <button type="submit" class="btn-primary">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Ratio Modal -->
<div id="deleteRatioModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white dark:bg-gray-800">
        <div class="mt-3">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Delete Ratio</h3>
            <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">This will mark the ratio for deletion. It can be restored later.</p>
            <form method="POST" action="">
                <input type="hidden" name="csrf" value="<?php echo htmlspecialchars($csrf); ?>">
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Deletion Note</label>
                    <textarea name="note" rows="3" class="w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-sm"></textarea>
                </div>
                <div class="flex justify-end gap-3">
                    <button type="button" onclick="closeDeleteModal()" class="btn-secondary">Cancel</button>
                    <button type="submit" class="btn-danger">Delete</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function editRatio(id, currentValue, currentConfidence) {
    document.getElementById('editValue').value = currentValue;
    document.getElementById('editConfidence').value = currentConfidence;
    document.querySelector('#editRatioModal form').action = `/dashboard/superadmin/master-sheet/edit/${id}`;
    document.getElementById('editRatioModal').classList.remove('hidden');
}

function closeEditModal() {
    document.getElementById('editRatioModal').classList.add('hidden');
}

function deleteRatio(id) {
    document.querySelector('#deleteRatioModal form').action = `/dashboard/superadmin/master-sheet/delete/${id}`;
    document.getElementById('deleteRatioModal').classList.remove('hidden');
}

function closeDeleteModal() {
    document.getElementById('deleteRatioModal').classList.add('hidden');
}

function viewHistory(id) {
    // TODO: Implement history view
    alert('History view coming soon');
}

function showBulkImportModal() {
    // TODO: Implement bulk import
    alert('Bulk import coming soon');
}

function showAutoLoadModal() {
    // TODO: Implement auto load
    alert('Auto load coming soon');
}
</script>

<?php
$content = ob_get_clean();
include __DIR__.'/../layout.php';
?>