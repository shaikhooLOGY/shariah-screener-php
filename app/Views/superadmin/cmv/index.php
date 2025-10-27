<?php
$this->layout('layouts/main', ['title' => 'CMV Management']);
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Compliance Master Versions (CMV)</h1>
        <p class="mt-2 text-gray-600">Manage draft, published, and archived compliance master versions</p>
    </div>

    <!-- Current Status -->
    <div class="bg-white shadow rounded-lg p-6 mb-8">
        <h2 class="text-lg font-semibold mb-4">Current Status</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="bg-blue-50 p-4 rounded-lg">
                <div class="text-sm text-blue-600 font-medium">Published CMV</div>
                <div class="text-2xl font-bold text-blue-900">
                    <?php echo htmlspecialchars($current['cmv_id_published'] ? 'ID: ' . $current['cmv_id_published'] : 'None'); ?>
                </div>
            </div>
            <div class="bg-yellow-50 p-4 rounded-lg">
                <div class="text-sm text-yellow-600 font-medium">Pending Approval</div>
                <div class="text-2xl font-bold text-yellow-900">
                    <?php echo htmlspecialchars($current['cmv_id_pending'] ? 'ID: ' . $current['cmv_id_pending'] : 'None'); ?>
                </div>
            </div>
            <div class="bg-green-50 p-4 rounded-lg">
                <div class="text-sm text-green-600 font-medium">Total CMVs</div>
                <div class="text-2xl font-bold text-green-900">
                    <?php echo count($grouped['draft']) + count($grouped['published']) + count($grouped['archived']) + count($grouped['rolled_back']); ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Run New CMV -->
    <div class="bg-white shadow rounded-lg p-6 mb-8">
        <h2 class="text-lg font-semibold mb-4">Run New CMV</h2>
        <form id="run-cmv-form" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Scope</label>
                    <select name="scope" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="all">All Companies</option>
                        <option value="ticker">Specific Ticker</option>
                        <option value="sector">Specific Sector</option>
                    </select>
                </div>
                <div id="symbol-field" class="hidden">
                    <label class="block text-sm font-medium text-gray-700">Symbol/Sector</label>
                    <input type="text" name="symbol" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>
                <div class="flex items-end">
                    <label class="flex items-center">
                        <input type="checkbox" name="dry" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <span class="ml-2 text-sm text-gray-700">Dry Run</span>
                    </label>
                </div>
            </div>
            <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                Run CMV
            </button>
        </form>
    </div>

    <!-- CMV Lists -->
    <?php foreach (['draft' => 'Draft', 'published' => 'Published', 'archived' => 'Archived', 'rolled_back' => 'Rolled Back'] as $status => $label): ?>
        <?php if (!empty($grouped[$status])): ?>
            <div class="bg-white shadow rounded-lg p-6 mb-8">
                <h2 class="text-lg font-semibold mb-4"><?php echo htmlspecialchars($label); ?> CMVs</h2>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Label</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Period</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($grouped[$status] as $cmv): ?>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        <?php echo htmlspecialchars($cmv['id']); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <?php echo htmlspecialchars($cmv['label']); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <?php echo htmlspecialchars($cmv['period']); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <?php echo htmlspecialchars(date('Y-m-d', strtotime($cmv['created_at']))); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <button onclick="viewDiff(<?php echo $cmv['id']; ?>)" class="text-indigo-600 hover:text-indigo-900 mr-4">View Diff</button>
                                        <?php if ($status === 'draft'): ?>
                                            <button onclick="publishCmv(<?php echo $cmv['id']; ?>)" class="text-green-600 hover:text-green-900 mr-4">Publish</button>
                                        <?php elseif ($status === 'published'): ?>
                                            <button onclick="rollbackCmv(<?php echo $cmv['id']; ?>)" class="text-red-600 hover:text-red-900">Rollback</button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>
    <?php endforeach; ?>
</div>

<!-- Diff Modal -->
<div id="diff-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden" id="my-modal">
    <div class="relative top-20 mx-auto p-5 border w-11/12 max-w-4xl shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <h3 class="text-lg font-medium text-gray-900 mb-4">CMV Diff</h3>
            <div id="diff-content" class="text-sm text-gray-700">
                <!-- Diff content will be loaded here -->
            </div>
            <div class="flex justify-end mt-4">
                <button onclick="closeDiffModal()" class="px-4 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
function viewDiff(cmvId) {
    fetch(`/dashboard/superadmin/cmv/${cmvId}/diff`)
        .then(response => response.json())
        .then(data => {
            document.getElementById('diff-content').innerHTML = `
                <pre class="bg-gray-100 p-4 rounded">${JSON.stringify(data, null, 2)}</pre>
            `;
            document.getElementById('diff-modal').classList.remove('hidden');
        })
        .catch(error => alert('Error loading diff: ' + error));
}

function closeDiffModal() {
    document.getElementById('diff-modal').classList.add('hidden');
}

function publishCmv(cmvId) {
    if (confirm('Are you sure you want to publish this CMV? This will require approval.')) {
        fetch(`/dashboard/superadmin/cmv/${cmvId}/publish`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': window.APP.csrf
            }
        })
        .then(response => response.json())
        .then(data => {
            alert('Publish request submitted for approval.');
            location.reload();
        })
        .catch(error => alert('Error: ' + error));
    }
}

function rollbackCmv(cmvId) {
    if (confirm('Are you sure you want to rollback this CMV? This will require approval.')) {
        fetch(`/dashboard/superadmin/cmv/${cmvId}/rollback`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': window.APP.csrf
            }
        })
        .then(response => response.json())
        .then(data => {
            alert('Rollback request submitted for approval.');
            location.reload();
        })
        .catch(error => alert('Error: ' + error));
    }
}

// Form handling
document.getElementById('run-cmv-form').addEventListener('submit', function(e) {
    e.preventDefault();

    const formData = new FormData(e.target);
    const data = {
        scope: formData.get('scope'),
        symbol: formData.get('symbol'),
        dry: formData.get('dry') === 'on'
    };

    fetch('/dashboard/superadmin/cmv/run', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-Token': window.APP.csrf
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(result => {
        if (result.dry_run) {
            alert(`Dry run: Would process ${result.stats.companies_found} companies`);
        } else {
            alert(`CMV created with ID: ${result.cmv_id}`);
            location.reload();
        }
    })
    .catch(error => alert('Error: ' + error));
});

// Show/hide symbol field based on scope
document.querySelector('select[name="scope"]').addEventListener('change', function(e) {
    const symbolField = document.getElementById('symbol-field');
    if (e.target.value === 'ticker' || e.target.value.value === 'sector') {
        symbolField.classList.remove('hidden');
    } else {
        symbolField.classList.add('hidden');
    }
});
</script>