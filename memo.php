<?php
require_once 'db_connect.php';

if (empty($_GET['id'])) {
    die('IDが指定されていません。');
}

$id = $_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM memos WHERE id = ?");
$stmt->execute([$id]);
$memo = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$memo) {
    http_response_code(404);
    die('メモが見つかりません。');
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>メモ詳細</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header class="bg-dark text-white text-center p-3 mb-4">
        <h1><a href="index.php" class="text-white text-decoration-none">MemoFile</a></h1>
    </header>

    <main class="container">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span>更新日時: <?= date('Y/m/d H:i', strtotime($memo['updated_at'])) ?></span>
                <a href="index.php" class="btn btn-secondary">一覧へ戻る</a>
            </div>
            <div class="card-body">
                <pre class="card-text"><?= htmlspecialchars($memo['content'], ENT_QUOTES, 'UTF-8') ?></pre>
            </div>
        </div>
    </main>

    <footer class="text-center text-muted py-4 mt-4 bg-light">
        <p>&copy; <?= date('Y') ?> MemoFile</p>
    </footer>
</body>
</html>
