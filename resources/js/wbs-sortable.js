import Sortable from 'sortablejs';

// WBS ツリーのドラッグ&ドロップ並び替え。/wbs（会員向け）でのみ動く。
function initWbsSortable() {
    const root = document.getElementById('wbs-root');
    if (!root) return;

    const note = document.getElementById('wbs-save-note');
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content;

    function collect() {
        const nodes = [];
        document.querySelectorAll('ul.wbs-sortable').forEach((ul) => {
            const parentId = parseInt(ul.dataset.parentId, 10);
            Array.from(ul.children).forEach((li, idx) => {
                if (!li.dataset.id) return;
                nodes.push({ id: parseInt(li.dataset.id, 10), parent_id: parentId, junban: idx });
            });
        });
        return nodes;
    }

    function flash(msg, ok) {
        if (!note) return;
        note.textContent = msg;
        note.classList.remove('hidden');
        note.classList.toggle('text-red-500', ok === false);
        if (ok) setTimeout(() => note.classList.add('hidden'), 1500);
    }

    function save() {
        flash('保存中…');
        fetch('/wbs/reorder', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrf,
                Accept: 'application/json',
            },
            body: JSON.stringify({ nodes: collect() }),
        })
            .then((r) => {
                if (r.ok) flash('並び順を保存しました', true);
                else if (r.status === 422) r.json().then((d) => flash(d.message || '保存できませんでした', false));
                else flash('保存に失敗しました。再読み込みしてください。', false);
            })
            .catch(() => flash('通信エラー。再読み込みしてください。', false));
    }

    document.querySelectorAll('ul.wbs-sortable').forEach((ul) => {
        new Sortable(ul, {
            group: 'wbs',
            handle: '.wbs-handle',
            animation: 150,
            forceFallback: true, // ネイティブ HTML5 drag を使わずポインタイベントで（挙動が安定）
            fallbackOnBody: true,
            fallbackTolerance: 4,
            invertSwap: true,
            onEnd: save,
        });
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initWbsSortable);
} else {
    initWbsSortable();
}
