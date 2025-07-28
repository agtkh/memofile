<?php
require_once 'db_connect.php';

if (empty($_GET['id'])) {
    die('IDが指定されていません。');
}

$id = $_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM files WHERE id = ?");
$stmt->execute([$id]);
$file = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$file || !file_exists($file['file_path'])) {
    http_response_code(404);
    die('ファイルが見つからないか、アクセスできません。');
}

header('Content-Description: File Transfer');
header('Content-Type: ' . $file['mime_type']);
header('Content-Disposition: attachment; filename="' . basename($file['original_filename']) . '"');
header('Expires: 0');
header('Cache-Control: must-revalidate');
header('Pragma: public');
header('Content-Length: ' . $file['file_size']);
readfile($file['file_path']);
exit;