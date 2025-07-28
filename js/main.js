$(function() {
    const memoForm = $('#memo-form');
    const memoId = $('#memo-id');
    const memoContent = $('#memo-content');
    const memoModal = new bootstrap.Modal($('#memo-modal')[0]);
    const fileModal = new bootstrap.Modal($('#file-modal')[0]);
    const memosContainer = $('#memos-container');
    const filesContainer = $('#files-container');

    // --- データ更新関数 ---
    function refreshData() {
        $.ajax({
            url: 'api.php?action=get_all',
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    renderMemos(response.memos);
                    renderFiles(response.files);
                }
            }
        });
    }

    function renderMemos(memos) {
        memosContainer.empty();
        if (memos.length === 0) {
            memosContainer.html('<p class="text-muted">メモはありません。</p>');
            return;
        }
        memos.forEach(memo => {
            const truncatedContent = memo.content.length > 100 ? memo.content.substring(0, 100) + '...' : memo.content;
            const memoDate = new Date(memo.updated_at).toLocaleString('ja-JP');
            const memoElement = `
                <div class="col">
                    <div class="card h-100 memo-item" data-id="${memo.id}">
                        <div class="card-body">
                            <pre class="content card-text">${escapeHtml(truncatedContent)}</pre>
                            <div class="full-content d-none">${escapeHtml(memo.content)}</div>
                        </div>
                        <div class="card-footer bg-transparent border-top-0">
                            <div class="d-flex justify-content-between align-items-center">
                                <small class="text-muted">更新: ${memoDate}</small>
                                <div class="actions">
                                    <button class="btn btn-sm btn-outline-secondary edit-btn" title="編集"><i class="bi bi-pencil"></i></button>
                                    <button class="btn btn-sm btn-outline-info copy-btn" title="コピー"><i class="bi bi-clipboard"></i></button>
                                    <a href="memo.php?id=${memo.id}" class="btn btn-sm btn-outline-primary" title="詳細"><i class="bi bi-arrows-fullscreen"></i></a>
                                    <button class="btn btn-sm btn-outline-danger delete-memo-btn" title="削除"><i class="bi bi-trash"></i></button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>`;
            memosContainer.append(memoElement);
        });
    }

    function renderFiles(files) {
        filesContainer.empty();
        if (files.length === 0) {
            filesContainer.html('<tr><td colspan="4" class="text-muted text-center">ファイルはありません。</td></tr>');
            return;
        }
        files.forEach(file => {
            const fileDate = new Date(file.created_at).toLocaleString('ja-JP');
            const fileSize = formatBytes(file.file_size);
            const fileElement = `
                <tr data-id="${file.id}">
                    <td>${escapeHtml(file.original_filename)}</td>
                    <td>${fileSize}</td>
                    <td>${fileDate}</td>
                    <td class="actions">
                        <a href="download.php?id=${file.id}" class="btn btn-sm btn-success download-btn" title="ダウンロード"><i class="bi bi-download"></i></a>
                        <button class="btn btn-sm btn-danger delete-file-btn" title="削除"><i class="bi bi-trash"></i></button>
                    </td>
                </tr>`;
            filesContainer.append(fileElement);
        });
    }

    // --- AJAX処理 ---
    function handleAjax(formData, successCallback) {
        $.ajax({
            url: 'api.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    alert(response.message || '処理が完了しました。');
                    refreshData();
                    if (successCallback) successCallback();
                } else {
                    alert('エラー: ' + response.message);
                }
            },
            error: function() {
                alert('サーバーとの通信に失敗しました。');
            }
        });
    }

    // --- イベントハンドラ ---
    memoForm.on('submit', function(e) {
        e.preventDefault();
        const action = memoId.val() ? 'update_memo' : 'add_memo';
        const formData = new FormData(this);
        formData.append('action', action);
        handleAjax(formData, () => memoModal.hide());
    });

    $('#file-form').on('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        formData.append('action', 'upload_file');
        handleAjax(formData, () => fileModal.hide());
    });

    $('[data-bs-target="#memo-modal"]').on('click', function() {
        memoForm[0].reset();
        memoId.val('');
        $('#memo-modal-label').text('新しいメモ');
    });

    $('#memos-container').on('click', '.edit-btn', function() {
        const item = $(this).closest('.memo-item');
        memoId.val(item.data('id'));
        memoContent.val(item.find('.full-content').text());
        $('#memo-modal-label').text('メモの編集');
        memoModal.show();
    });

    $('#memos-container').on('click', '.delete-memo-btn', function() {
        if (!confirm('このメモを削除しますか？')) return;
        const id = $(this).closest('.memo-item').data('id');
        const formData = new FormData();
        formData.append('action', 'delete_memo');
        formData.append('id', id);
        handleAjax(formData);
    });

    $('#files-container').on('click', '.delete-file-btn', function() {
        if (!confirm('このファイルを削除しますか？')) return;
        const id = $(this).closest('tr').data('id');
        const formData = new FormData();
        formData.append('action', 'delete_file');
        formData.append('id', id);
        handleAjax(formData);
    });

    $('#memos-container').on('click', '.copy-btn', function() {
        const content = $(this).closest('.memo-item').find('.full-content').text();
        navigator.clipboard.writeText(content).then(() => {
            alert('コピーしました！');
        });
    });

    $('#clear-btn').on('click', function() {
        memoId.val('');
        memoContent.val('');
    });

    // --- ユーティリティ関数 ---
    function escapeHtml(str) {
        return $('<div>').text(str).html();
    }

    function formatBytes(bytes, decimals = 2) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const dm = decimals < 0 ? 0 : decimals;
        const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i];
    }

    // 初期データ読み込み
    // refreshData(); // 初期表示はPHPで行うため不要
});
