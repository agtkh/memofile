# MemoFile

シンプルなメモとファイルの管理アプリケーション。

## 概要

MemoFileは、日々のメモやちょっとしたファイルをブラウザ上で手軽に管理するためのWebアプリケーションである。PHPとJavaScriptで構築されており、データベースとしてPostgreSQLを使用する。

主な機能:
- テキストメモの作成、編集、削除
- ファイルのアップロード、ダウンロード、削除
- レスポンシブデザインによる、PC・スマートフォン・タブレットでの快適な操作

## スクリーンショット

*(アプリケーションのスクリーンショットをここに追加)*

## 必要なもの

- Webサーバー (Apache, Nginxなど)
- PHP 8.0 以上
  - `pdo_pgsql` 拡張モジュール
- PostgreSQL 12 以上

## セットアップ手順

### 1. リポジトリのクローン

```bash
git clone https://github.com/agtkh/memofile.git
cd memofile
```

### 2. データベースのセットアップ

PostgreSQLに接続し、データベースとユーザーを作成する。

```sql
CREATE DATABASE your_database_name;
CREATE USER your_username WITH PASSWORD 'your_password';
ALTER DATABASE your_database_name OWNER TO your_username;
```

次に、`init.sql` ファイルを使用してテーブルを作成する。

```bash
psql -U your_username -d your_database_name -f init.sql
```

### 3. データベース接続設定

`db_connect.php` ファイルを編集し、自身のデータベース接続情報を設定する。

```php
// db_connect.php

$host = 'localhost';
$port = '5432';
$dbname = 'your_database_name'; // 作成したDB名
$user = 'your_username';       // 作成したユーザー名
$password = 'your_password';   // 設定したパスワード
```

### 4. ファイルアップロードディレクトリのパーミッション設定

Webサーバーが `uploads/` ディレクトリに書き込みできるように、パーミッションを設定する。

```bash
chmod -R 755 uploads/
chown -R www-data:www-data uploads/
```
※ `www-data` の部分は、使用しているWebサーバーの実行ユーザーに合わせ��変更すること。

### 5. Webサーバーの設定

#### Apacheの設定例

Apacheのバーチャルホスト設定ファイル（例: `/etc/apache2/sites-available/memofile.conf`）に以下を追記する。

```apache
<VirtualHost *:80>
    ServerName memofile.local
    DocumentRoot /path/to/your/memofile

    <Directory /path/to/your/memofile>
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/error.log
    CustomLog ${APACHE_LOG_DIR}/access.log combined
</VirtualHost>
```

設定後、サイトを有効化し、Apacheを再起動する。

```bash
sudo a2ensite memofile.conf
sudo systemctl restart apache2
```

また、ローカル環境で `memofile.local` のようなホスト名を使用する場合は、`/etc/hosts` ファイルに以下を追記する必要がある。

```
127.0.0.1   memofile.local
```

## 使い方

設定したURL（例: `http://memofile.local`）にブラウザでアクセスする。

- **新しいメモ**: 右上の「新しいメモ」ボタンをクリックすると、作成・編集フォームが表示される。
- **ファイルアップロード**: 「ファイルをアップロード」ボタンからファイルを選択してアップロードする。
- **一覧の操作**: 各メモやファイルには、編集、コピー、ダウンロード、削除などの操作ボタンがある。

## ライセンス

このプロジェクトは [MIT License](LICENSE) のもとで公開されている。
