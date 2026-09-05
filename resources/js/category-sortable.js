import Sortable from 'sortablejs';

// 公開ページ左サイドバー「カテゴリ」の管理者モードでのドラッグ&ドロップ並び替え。
// 管理者モードOFF時はハンドルが描画されないため何もしない。
function initCategorySortable() {
    const list = document.getElementById('sidebar-category-list');
    if (!list || list.dataset.adminMode !== '1') return;

    const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
    const note = document.getElementById('category-sort-note');

    function flash(msg, ok) {
        if (!note) return;
        note.textContent = msg;
        note.classList.remove('hidden');
        note.classList.toggle('text-red-500', ok === false);
        if (ok) setTimeout(() => note.classList.add('hidden'), 1500);
    }

    function save() {
        const ids = Array.from(list.children)
            .filter((li) => li.dataset.id)
            .map((li) => parseInt(li.dataset.id, 10));

        flash('保存中…');
        fetch('/categories/reorder', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrf,
                Accept: 'application/json',
            },
            body: JSON.stringify({ ids }),
        })
            .then((r) => {
                if (r.ok) flash('並び順を保存しました', true);
                else flash('保存に失敗しました。再読み込みしてください。', false);
            })
            .catch(() => flash('通信エラー。再読み込みしてください。', false));
    }

    new Sortable(list, {
        handle: '.category-drag-handle',
        animation: 150,
        forceFallback: true, // ネイティブ HTML5 drag を使わずポインタイベントで（挙動が安定）
        fallbackOnBody: true,
        fallbackTolerance: 4,
        onEnd: save,
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initCategorySortable);
} else {
    initCategorySortable();
}
