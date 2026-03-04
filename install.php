<?php
/**
 * One-click installer for English Writing Coach
 * Recommended mode: SQLite (auto setup for most shared-host deployments)
 */

declare(strict_types=1);

session_start();

$baseDir = __DIR__;
$envFile = $baseDir . '/.env';
$envExample = $baseDir . '/.env.example';
$schemaFile = $baseDir . '/database/schema.sql';
$seedFile = $baseDir . '/database/seed.sql';
$installLock = $baseDir . '/storage/.installed';

function h(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function generateCsrfToken(): string
{
    if (empty($_SESSION['_installer_csrf'])) {
        $_SESSION['_installer_csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['_installer_csrf'];
}

function verifyInstallerCsrf(string $token): bool
{
    return hash_equals($_SESSION['_installer_csrf'] ?? '', $token);
}

function writeEnv(array $data, string $target, string $example): void
{
    $template = file_exists($example)
        ? file_get_contents($example)
        : "APP_NAME=English Writing Coach\nAPP_ENV=production\nAPP_URL=http://localhost\nDB_DRIVER=sqlite\nDB_DATABASE=database/app.db\nDB_HOST=127.0.0.1\nDB_PORT=3306\nDB_NAME=english_writing\nDB_USER=root\nDB_PASS=\n";

    $lines = preg_split('/\R/', $template) ?: [];
    $parsed = [];
    foreach ($lines as $line) {
        if (str_contains($line, '=')) {
            [$k, $v] = explode('=', $line, 2);
            $parsed[trim($k)] = trim($v);
        }
    }

    $merged = array_merge($parsed, $data);

    $output = [];
    foreach ($merged as $k => $v) {
        $output[] = $k . '=' . $v;
    }
    file_put_contents($target, implode(PHP_EOL, $output) . PHP_EOL);
}

function runSqliteInstall(string $dbPath, string $schema, string $seed): void
{
    $dir = dirname($dbPath);
    if (!is_dir($dir) && !mkdir($dir, 0775, true) && !is_dir($dir)) {
        throw new RuntimeException('Không thể tạo thư mục database: ' . $dir);
    }

    $pdo = new PDO('sqlite:' . $dbPath);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $schemaSql = file_get_contents($schema);
    $seedSql = file_get_contents($seed);

    if ($schemaSql === false || $seedSql === false) {
        throw new RuntimeException('Không đọc được file schema/seed.');
    }

    $pdo->exec($schemaSql);
    $pdo->exec($seedSql);
}

$requirements = [
    'PHP >= 8.1' => version_compare(PHP_VERSION, '8.1.0', '>='),
    'PDO extension' => extension_loaded('pdo'),
    'PDO SQLite extension' => extension_loaded('pdo_sqlite'),
    'Quyền ghi thư mục dự án' => is_writable($baseDir),
    'Quyền ghi thư mục storage' => is_dir($baseDir . '/storage') ? is_writable($baseDir . '/storage') : is_writable($baseDir),
    'File database/schema.sql tồn tại' => file_exists($schemaFile),
    'File database/seed.sql tồn tại' => file_exists($seedFile),
];

$allGood = !in_array(false, $requirements, true);
$errors = [];
$successMessage = '';

if (file_exists($installLock)) {
    $successMessage = 'Hệ thống đã được cài đặt trước đó. Nếu muốn cài lại, hãy xóa file storage/.installed và database/app.db (nếu dùng SQLite).';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !file_exists($installLock)) {
    try {
        if (!verifyInstallerCsrf($_POST['_csrf'] ?? '')) {
            throw new RuntimeException('CSRF token không hợp lệ.');
        }

        if (!$allGood) {
            throw new RuntimeException('Máy chủ chưa đạt đủ điều kiện cài đặt.');
        }

        $driver = $_POST['db_driver'] ?? 'sqlite';
        $appUrl = trim($_POST['app_url'] ?? '');
        if ($appUrl === '') {
            $appUrl = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http')
                . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost');
        }

        if ($driver !== 'sqlite') {
            throw new RuntimeException('Bản installer one-click hiện tối ưu cho SQLite trên shared host. Vui lòng chọn SQLite để tự động cài.');
        }

        $sqliteRel = trim($_POST['sqlite_path'] ?? 'database/app.db');
        $sqliteRel = ltrim($sqliteRel, '/');
        $sqliteAbs = $baseDir . '/' . $sqliteRel;

        if (file_exists($sqliteAbs)) {
            @unlink($sqliteAbs);
        }

        runSqliteInstall($sqliteAbs, $schemaFile, $seedFile);

        writeEnv([
            'APP_NAME' => 'English Writing Coach',
            'APP_ENV' => 'production',
            'APP_URL' => $appUrl,
            'DB_DRIVER' => 'sqlite',
            'DB_DATABASE' => $sqliteRel,
            'DB_HOST' => '127.0.0.1',
            'DB_PORT' => '3306',
            'DB_NAME' => 'english_writing',
            'DB_USER' => 'root',
            'DB_PASS' => '',
        ], $envFile, $envExample);

        if (!is_dir($baseDir . '/storage')) {
            mkdir($baseDir . '/storage', 0775, true);
        }
        file_put_contents($installLock, 'installed_at=' . date('c') . PHP_EOL);

        $successMessage = 'Cài đặt thành công! Bạn có thể đăng nhập tại /login với tài khoản mẫu: admin@example.com/admin123, teacher@example.com/teacher123, student@example.com/student123';
    } catch (Throwable $e) {
        $errors[] = $e->getMessage();
    }
}
?>
<!doctype html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Installer - English Writing Coach</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h2 class="mb-3">English Writing Coach - One-click Installer</h2>
                    <p class="text-muted mb-4">Upload toàn bộ source lên host, mở <code>/install.php</code> và bấm cài đặt.</p>

                    <?php if ($successMessage !== ''): ?>
                        <div class="alert alert-success"><?= h($successMessage) ?></div>
                    <?php endif; ?>

                    <?php foreach ($errors as $error): ?>
                        <div class="alert alert-danger mb-2"><?= h($error) ?></div>
                    <?php endforeach; ?>

                    <h5>Kiểm tra môi trường</h5>
                    <ul class="list-group mb-4">
                        <?php foreach ($requirements as $label => $ok): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <span><?= h($label) ?></span>
                                <?php if ($ok): ?>
                                    <span class="badge bg-success">OK</span>
                                <?php else: ?>
                                    <span class="badge bg-danger">FAIL</span>
                                <?php endif; ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>

                    <?php if (!file_exists($installLock)): ?>
                    <form method="post">
                        <input type="hidden" name="_csrf" value="<?= h(generateCsrfToken()) ?>">

                        <div class="mb-3">
                            <label class="form-label">APP_URL</label>
                            <input name="app_url" class="form-control" value="<?= h(((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost')) ?>">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Database driver</label>
                            <select name="db_driver" class="form-select">
                                <option value="sqlite" selected>SQLite (khuyên dùng, tự động 100%)</option>
                                <option value="mysql">MySQL (chưa one-click hoàn chỉnh trong bản này)</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">SQLite path (relative)</label>
                            <input name="sqlite_path" class="form-control" value="database/app.db">
                        </div>

                        <button class="btn btn-primary" <?= $allGood ? '' : 'disabled' ?>>Cài đặt ngay</button>
                    </form>
                    <?php else: ?>
                        <div class="alert alert-warning">Installer đã khóa. Xóa <code>storage/.installed</code> nếu muốn chạy lại.</div>
                    <?php endif; ?>

                    <hr>
                    <p class="mb-0"><strong>Khuyến nghị bảo mật:</strong> Sau khi cài xong, hãy xóa hoặc đổi tên file <code>install.php</code>.</p>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
