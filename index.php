<?php
require_once 'db_connect.php';

require_once 'functions.php';
// --- データ取得 ---
$memos = $pdo->query("SELECT * FROM memos ORDER BY updated_at DESC")->fetchAll(PDO::FETCH_ASSOC);
$files = $pdo->query("SELECT * FROM files ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MemoFile</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header class="bg-dark text-white text-center p-3 mb-4">
        <h1><i class="bi bi-journal-richtext"></i> MemoFile</h1>
    </header>

    <main class="container">
        <div class="d-flex justify-content-end mb-3">
            <button type="button" class="btn btn-primary me-2" data-bs-toggle="modal" data-bs-target="#memo-modal">
                <i class="bi bi-plus-lg"></i> 新しいメモ
            </button>
            <button type="button" class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#file-modal">
                <i class="bi bi-cloud-arrow-up"></i> ファイルをアップロード
            </button>
        </div>

        <div class="row g-4">
            <!-- データ一覧 -->
            <div class="col-12">
                <!-- メモ一覧 -->
                <div id="memos-list" class="mb-4">
                    <h2 class="h4 border-bottom pb-2 mb-3">メモ一覧</h2>
                    <div id="memos-container" class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
                        <?php foreach ($memos as $memo): ?>
                        <div class="col">
                            <div class="card h-100 memo-item" data-id="<?= $memo['id'] ?>">
                                <div class="card-body">
                                    <pre class="content card-text"><?= htmlspecialchars(truncateMemo($memo['content']), ENT_QUOTES, 'UTF-8') ?></pre>
                                    <div class="full-content d-none"><?= htmlspecialchars($memo['content'], ENT_QUOTES, 'UTF-8') ?></div>
                                </div>
                                <div class="card-footer bg-transparent border-top-0">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <small class="text-muted">更新: <?= date('Y/m/d H:i', strtotime($memo['updated_at'])) ?></small>
                                        <div class="actions">
                                            <button class="btn btn-sm btn-outline-secondary edit-btn" title="編集"><i class="bi bi-pencil"></i></button>
                                            <button class="btn btn-sm btn-outline-info copy-btn" title="コピー"><i class="bi bi-clipboard"></i></button>
                                            <a href="memo.php?id=<?= $memo['id'] ?>" class="btn btn-sm btn-outline-primary" title="詳細"><i class="bi bi-arrows-fullscreen"></i></a>
                                            <button class="btn btn-sm btn-outline-danger delete-memo-btn" title="��除"><i class="bi bi-trash"></i></button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- ファイル一覧 -->
                <div id="files-list">
                    <h2 class="h4 border-bottom pb-2 mb-3">ファイル一覧</h2>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>ファイル名</th>
                                    <th>サイズ</th>
                                    <th>アップロード日時</th>
                                    <th>操作</th>
                                </tr>
                            </thead>
                            <tbody id="files-container">
                            <?php foreach ($files as $file): ?>
                                <tr data-id="<?= $file['id'] ?>">
                                    <td><?= htmlspecialchars($file['original_filename'], ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><?= formatBytes($file['file_size']) ?></td>
                                    <td><?= date('Y/m/d H:i', strtotime($file['created_at'])) ?></td>
                                    <td class="actions">
                                        <a href="download.php?id=<?= $file['id'] ?>" class="btn btn-sm btn-success download-btn" title="ダウンロード"><i class="bi bi-download"></i></a>
                                        <button class="btn btn-sm btn-danger delete-file-btn" title="削除"><i class="bi bi-trash"></i></button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- メモ用モーダル -->
    <div class="modal fade" id="memo-modal" tabindex="-1" aria-labelledby="memo-modal-label" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form id="memo-form">
                    <div class="modal-header">
                        <h5 class="modal-title" id="memo-modal-label">メモの作成・編集</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="id" id="memo-id">
                        <div class="mb-3">
                            <textarea name="content" id="memo-content" class="form-control" placeholder="ここにメモを入力..." rows="15" required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" id="clear-btn" class="btn btn-secondary">クリア</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">キャンセル</button>
                        <button type="submit" class="btn btn-primary">保存</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- ファイルアップロード用モーダル -->
    <div class="modal fade" id="file-modal" tabindex="-1" aria-labelledby="file-modal-label" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="file-form" enctype="multipart/form-data">
                    <div class="modal-header">
                        <h5 class="modal-title" id="file-modal-label">ファイルアップロード</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <input type="file" name="file" id="file-input" class="form-control" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">キャンセル</button>
                        <button type="submit" class="btn btn-primary">アップロード</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <footer class="text-center text-muted py-4 mt-4 bg-light">
        <p>&copy; <?= date('Y') ?> MemoFile</p>
    </footer>

    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/main.js"></script>
</body>
</html>
