<?php
// ========== データベース接続設定 ==========
$host = 'localhost';
$port = '5432';
$dbname = 'your_database_name'; // あなたのDB名
$user = 'your_username';       // あなたのユーザー名
$password = 'your_password';   // あなたのパスワード
// ==========================================

$dsn = "pgsql:host=$host;port=$port;dbname=$dbname";

try {
    $pdo = new PDO($dsn, $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("データベース接続に失敗しました: " . $e->getMessage());
}