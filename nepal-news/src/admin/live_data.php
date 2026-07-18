<?php
/**
 * Live Data Admin Panel
 * Manage API cache, weather alerts, government notices
 */

admin_check();

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'install_tables':
            require SRC_DIR . '/lib/live_data_tables.php';
            install_live_data_tables();
            flash_set('success', 'Live data tables created successfully.');
            redirect('admin/live_data');
            break;
            
        case 'clear_cache':
            $db = db();
            $db->query("DELETE FROM api_cache WHERE expires_at < NOW()");
            flash_set('success', 'Cache cleared successfully.');
            redirect('admin/live_data');
            break;
            
        case 'save_notice':
            $title = trim($_POST['title'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $source = trim($_POST['source'] ?? '');
            $notice_date = $_POST['notice_date'] ?? date('Y-m-d');
            $url = trim($_POST['url'] ?? '');
            
            $db = db();
            $stmt = $db->prepare("
                INSERT INTO government_notices (title, description, source, notice_date, url)
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->bind_param('sssss', $title, $description, $source, $notice_date, $url);
            $stmt->execute();
            
            flash_set('success', 'Notice added successfully.');
            redirect('admin/live_data?tab=notices');
            break;
            
        case 'save_alert':
            $title = trim($_POST['title'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $alert_type = trim($_POST['alert_type'] ?? 'Weather');
            $severity = trim($_POST['severity'] ?? 'moderate');
            $source = trim($_POST['source'] ?? 'Admin');
            
            $db = db();
            $stmt = $db->prepare("
                INSERT INTO weather_alerts (title, description, alert_type, severity, source, start_time)
                VALUES (?, ?, ?, ?, ?, NOW())
            ");
            $stmt->bind_param('sssss', $title, $description, $alert_type, $severity, $source);
            $stmt->execute();
            
            flash_set('success', 'Alert published successfully.');
            redirect('admin/live_data?tab=alerts');
            break;
    }
}

admin_html_start('Live Data');
admin_sidebar('live_data');
?>
<div class="admin-content">
<?php admin_topbar('Live Data'); ?>
<div class="p-6">
<?php admin_flash(); ?>

<!-- Tab Navigation -->
<div class="flex flex-wrap gap-2 mb-6">
    <a href="?tab=dashboard" class="px-4 py-2 rounded-lg text-sm font-medium transition-all <?= ($_GET['tab'] ?? 'dashboard') === 'dashboard' ? 'bg-blue-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-50 border border-gray-200' ?>">
        Dashboard
    </a>
    <a href="?tab=alerts" class="px-4 py-2 rounded-lg text-sm font-medium transition-all <?= ($_GET['tab'] ?? '') === 'alerts' ? 'bg-blue-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-50 border border-gray-200' ?>">
        Weather Alerts
    </a>
    <a href="?tab=notices" class="px-4 py-2 rounded-lg text-sm font-medium transition-all <?= ($_GET['tab'] ?? '') === 'notices' ? 'bg-blue-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-50 border border-gray-200' ?>">
        Government Notices
    </a>
    <a href="?tab=cache" class="px-4 py-2 rounded-lg text-sm font-medium transition-all <?= ($_GET['tab'] ?? '') === 'cache' ? 'bg-blue-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-50 border border-gray-200' ?>">
        API Cache
    </a>
</div>

<?php $tab = $_GET['tab'] ?? 'dashboard'; ?>

<?php if ($tab === 'dashboard'): ?>
<!-- Dashboard -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
    <div class="stat-card">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500">Cached Items</p>
                <?php
                $db = db();
                $result = $db->query("SELECT COUNT(*) as cnt FROM api_cache WHERE expires_at > NOW()");
                $row = $result->fetch_assoc();
                ?>
                <p class="text-2xl font-bold"><?= $row['cnt'] ?></p>
            </div>
            <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                <i data-lucide="database" class="w-6 h-6 text-blue-600"></i>
            </div>
        </div>
    </div>
    <div class="stat-card">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500">Active Alerts</p>
                <?php
                $result = $db->query("SELECT COUNT(*) as cnt FROM weather_alerts WHERE is_active = 1");
                $row = $result->fetch_assoc();
                ?>
                <p class="text-2xl font-bold"><?= $row['cnt'] ?></p>
            </div>
            <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center">
                <i data-lucide="alert-triangle" class="w-6 h-6 text-red-600"></i>
            </div>
        </div>
    </div>
    <div class="stat-card">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500">Notices</p>
                <?php
                $result = $db->query("SELECT COUNT(*) as cnt FROM government_notices");
                $row = $result->fetch_assoc();
                ?>
                <p class="text-2xl font-bold"><?= $row['cnt'] ?></p>
            </div>
            <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                <i data-lucide="file-text" class="w-6 h-6 text-green-600"></i>
            </div>
        </div>
    </div>
</div>

<!-- Install & Clear Cache -->
<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <div class="stat-card">
        <h3 class="font-bold text-lg mb-4">Database Setup</h3>
        <p class="text-gray-600 mb-4">Create tables for live data storage:</p>
        <form method="POST">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="install_tables">
            <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                <i data-lucide="database" class="w-4 h-4 inline mr-1"></i> Install Tables
            </button>
        </form>
    </div>
    
    <div class="stat-card">
        <h3 class="font-bold text-lg mb-4">Cache Management</h3>
        <p class="text-gray-600 mb-4">Clear expired cache entries:</p>
        <form method="POST">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="clear_cache">
            <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
                <i data-lucide="trash-2" class="w-4 h-4 inline mr-1"></i> Clear Expired Cache
            </button>
        </form>
    </div>
</div>

<!-- API Endpoints Info -->
<div class="stat-card mt-4">
    <h3 class="font-bold text-lg mb-4">Available API Endpoints</h3>
    <div class="overflow-x-auto">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Endpoint</th>
                    <th>Description</th>
                    <th>Method</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><code class="bg-gray-100 px-2 py-1 rounded">/api/live/earthquakes</code></td>
                    <td>Recent earthquakes in Nepal (USGS)</td>
                    <td>GET</td>
                </tr>
                <tr>
                    <td><code class="bg-gray-100 px-2 py-1 rounded">/api/live/weather</code></td>
                    <td>Current weather for Kathmandu</td>
                    <td>GET</td>
                </tr>
                <tr>
                    <td><code class="bg-gray-100 px-2 py-1 rounded">/api/live/air-quality</code></td>
                    <td>Air quality index</td>
                    <td>GET</td>
                </tr>
                <tr>
                    <td><code class="bg-gray-100 px-2 py-1 rounded">/api/live/alerts</code></td>
                    <td>Weather and disaster alerts</td>
                    <td>GET</td>
                </tr>
                <tr>
                    <td><code class="bg-gray-100 px-2 py-1 rounded">/api/live/notices</code></td>
                    <td>Government notices</td>
                    <td>GET</td>
                </tr>
                <tr>
                    <td><code class="bg-gray-100 px-2 py-1 rounded">/api/live/dashboard</code></td>
                    <td>All live data combined</td>
                    <td>GET</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<?php elseif ($tab === 'alerts'): ?>
<!-- Weather Alerts -->
<div class="stat-card mb-6">
    <h3 class="font-bold text-lg mb-4">Publish New Alert</h3>
    <form method="POST" class="p-4 bg-gray-50 rounded-lg">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="save_alert">
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="form-label">Alert Type</label>
                <select name="alert_type" class="form-control">
                    <option value="Weather">Weather</option>
                    <option value="Earthquake">Earthquake</option>
                    <option value="Flood">Flood</option>
                    <option value="Landslide">Landslide</option>
                    <option value="Other">Other</option>
                </select>
            </div>
            <div>
                <label class="form-label">Severity</label>
                <select name="severity" class="form-control">
                    <option value="info">Info</option>
                    <option value="moderate">Moderate</option>
                    <option value="severe">Severe</option>
                </select>
            </div>
            <div>
                <label class="form-label">Source</label>
                <input type="text" name="source" class="form-control" value="Admin">
            </div>
        </div>
        
        <div class="mt-4">
            <label class="form-label">Title</label>
            <input type="text" name="title" class="form-control" required placeholder="Alert title...">
        </div>
        
        <div class="mt-4">
            <label class="form-label">Description</label>
            <textarea name="description" class="form-control" rows="3" required placeholder="Alert description..."></textarea>
        </div>
        
        <button type="submit" class="mt-4 px-6 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
            <i data-lucide="alert-triangle" class="w-4 h-4 inline mr-1"></i> Publish Alert
        </button>
    </form>
</div>

<!-- Active Alerts -->
<div class="stat-card">
    <h3 class="font-bold text-lg mb-4">Active Alerts</h3>
    <?php
    $result = $db->query("SELECT * FROM weather_alerts WHERE is_active = 1 ORDER BY created_at DESC");
    ?>
    <?php if ($result->num_rows > 0): ?>
    <div class="overflow-x-auto">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Type</th>
                    <th>Title</th>
                    <th>Severity</th>
                    <th>Created</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= h($row['alert_type']) ?></td>
                    <td><?= h($row['title']) ?></td>
                    <td>
                        <span class="px-2 py-1 rounded text-xs <?= $row['severity'] === 'severe' ? 'bg-red-100 text-red-800' : ($row['severity'] === 'moderate' ? 'bg-yellow-100 text-yellow-800' : 'bg-blue-100 text-blue-800') ?>">
                            <?= h($row['severity']) ?>
                        </span>
                    </td>
                    <td><?= date('Y-m-d H:i', strtotime($row['created_at'])) ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
    <?php else: ?>
    <p class="text-gray-500">No active alerts.</p>
    <?php endif; ?>
</div>

<?php elseif ($tab === 'notices'): ?>
<!-- Add Notice -->
<div class="stat-card mb-6">
    <h3 class="font-bold text-lg mb-4">Add Government Notice</h3>
    <form method="POST" class="p-4 bg-gray-50 rounded-lg">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="save_notice">
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="form-label">Title</label>
                <input type="text" name="title" class="form-control" required placeholder="Notice title...">
            </div>
            <div>
                <label class="form-label">Source</label>
                <input type="text" name="source" class="form-control" required placeholder="CBS Nepal, DHM, etc.">
            </div>
            <div>
                <label class="form-label">Date</label>
                <input type="date" name="notice_date" class="form-control" value="<?= date('Y-m-d') ?>">
            </div>
            <div>
                <label class="form-label">URL (optional)</label>
                <input type="url" name="url" class="form-control" placeholder="https://...">
            </div>
        </div>
        
        <div class="mt-4">
            <label class="form-label">Description</label>
            <textarea name="description" class="form-control" rows="3" required placeholder="Notice description..."></textarea>
        </div>
        
        <button type="submit" class="mt-4 px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
            <i data-lucide="plus" class="w-4 h-4 inline mr-1"></i> Add Notice
        </button>
    </form>
</div>

<!-- Existing Notices -->
<div class="stat-card">
    <h3 class="font-bold text-lg mb-4">All Notices</h3>
    <?php
    $result = $db->query("SELECT * FROM government_notices ORDER BY notice_date DESC");
    ?>
    <?php if ($result->num_rows > 0): ?>
    <div class="overflow-x-auto">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Source</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= h($row['title']) ?></td>
                    <td><?= h($row['source']) ?></td>
                    <td><?= $row['notice_date'] ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
    <?php else: ?>
    <p class="text-gray-500">No notices yet.</p>
    <?php endif; ?>
</div>

<?php elseif ($tab === 'cache'): ?>
<!-- API Cache -->
<div class="stat-card">
    <h3 class="font-bold text-lg mb-4">API Cache</h3>
    <?php
    $result = $db->query("SELECT * FROM api_cache ORDER BY expires_at DESC LIMIT 50");
    ?>
    <?php if ($result->num_rows > 0): ?>
    <div class="overflow-x-auto">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Cache Key</th>
                    <th>Expires At</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><code class="bg-gray-100 px-2 py-1 rounded"><?= h($row['cache_key']) ?></code></td>
                    <td><?= $row['expires_at'] ?></td>
                    <td>
                        <span class="text-sm <?= strtotime($row['expires_at']) > time() ? 'text-green-600' : 'text-red-600' ?>">
                            <?= strtotime($row['expires_at']) > time() ? 'Active' : 'Expired' ?>
                        </span>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
    <?php else: ?>
    <p class="text-gray-500">No cached items.</p>
    <?php endif; ?>
</div>
<?php endif; ?>

</div>
</div>

<?php admin_html_end(); ?>
