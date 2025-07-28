<?php
header('Content-Type: application/json');
require_once 'db_connect.php';

// ヘルパー関数をインクルード
require_once 'functions.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $action = $_GET['action'] ?? '';
    if ($action === 'get_all') {
        $memos = $pdo->query("SELECT * FROM memos ORDER BY updated_at DESC")->fetchAll(PDO::FETCH_ASSOC);
        $files = $pdo->query("SELECT * FROM files ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['success' => true, 'memos' => $memos, 'files' => $files]);
    } else {
        echo json_encode(['success' => false, 'message' => '無効なGETアクションです。']);
    }
    exit;
}

if ($method === 'POST') {
    $action = $_POST['action'] ?? '';
    $response = ['success' => false, 'message' => '無効なアクションです。'];

    switch ($action) {
        // メモ追加
        case 'add_memo':
            if (!empty($_POST['content'])) {
                $stmt = $pdo->prepare("INSERT INTO memos (content) VALUES (?)");
                $stmt->execute([$_POST['content']]);
                $response = ['success' => true, 'message' => 'メモを保存しました。'];
            } else {
                $response['message'] = '内容が空です。';
            }
            break;

        // メモ更新
        case 'update_memo':
            if (!empty($_POST['id']) && !empty($_POST['content'])) {
                $stmt = $pdo->prepare("UPDATE memos SET content = ? WHERE id = ?");
                $stmt->execute([$_POST['content'], $_POST['id']]);
                $response = ['success' => true, 'message' => 'メモを更新しました。'];
            } else {
                $response['message'] = 'IDまたは内容が不足しています。';
            }
            break;

        // メモ削除
        case 'delete_memo':
            if (!empty($_POST['id'])) {
                $stmt = $pdo->prepare("DELETE FROM memos WHERE id = ?");
                $stmt->execute([$_POST['id']]);
                $response = ['success' => true, 'message' => 'メモを削除しました。'];
            } else {
                $response['message'] = 'IDがありません。';
            }
            break;

        // ファイルアップロード
        case 'upload_file':
            if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = 'uploads/';
                $originalName = basename($_FILES['file']['name']);
                $fileSize = $_FILES['file']['size'];
                $mimeType = $_FILES['file']['type'];
                $storedName = uniqid('', true) . '_' . $originalName;
                $filePath = $uploadDir . $storedName;

                if (move_uploaded_file($_FILES['file']['tmp_name'], $filePath)) {
                    $stmt = $pdo->prepare(
                        "INSERT INTO files (original_filename, stored_filename, file_path, file_size, mime_type) VALUES (?, ?, ?, ?, ?)"
                    );
                    $stmt->execute([$originalName, $storedName, $filePath, $fileSize, $mimeType]);
                    $response = ['success' => true, 'message' => 'ファイルをアップロードしました。'];
                } else {
                    $response['message'] = 'ファイルの移動に失敗しました。';
                }
            } else {
                $response['message'] = 'ファイルアップロードエラーが発生しました。';
            }
            break;

        // ファイル削除
        case 'delete_file':
            if (!empty($_POST['id'])) {
                $stmt = $pdo->prepare("SELECT * FROM files WHERE id = ?");
                $stmt->execute([$_POST['id']]);
                $file = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($file) {
                    if (file_exists($file['file_path'])) {
                        unlink($file['file_path']);
                    }
                    $deleteStmt = $pdo->prepare("DELETE FROM files WHERE id = ?");
                    $deleteStmt->execute([$_POST['id']]);
                    $response = ['success' => true, 'message' => 'ファイルを削除しました。'];
                } else {
                    $response['message'] = 'ファイルが見つかりません。';
                }
            } else {
                $response['message'] = 'IDがありません。';
            }
            break;
    }

    echo json_encode($response);
}
