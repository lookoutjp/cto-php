@php
    $frozenCols ??= 3;
@endphp
<style>
    /* 表の領域を画面内に収まる高さのスクロール領域にし、見出し行と左端 N 列をその中で
       固定表示にする（呼び出し元で $frozenCols 列指定、既定3列）。
       Filament の表ラッパー（.fi-ta-content）は横スクロール用に overflow-x:auto を
       持つが、CSS の仕様上これがあると overflow-y も自動的に auto 扱いになり、
       ページ全体のスクロールに対して sticky を効かせることができない
       （固定の基準が「ページ」ではなく「このラッパー自身のスクロール」になるため）。
       そのため、ラッパー自体に高さの上限を与えて実際に内側でスクロールさせる。 */
    #fi-sticky-scroll {
        max-height: min(70vh, 40rem);
    }

    #fi-sticky-table thead th {
        position: sticky;
        top: 0;
        z-index: 20;
        background-color: rgb(255 255 255);
    }

    #fi-sticky-table [data-frozen-col] {
        position: sticky;
        z-index: 10;
        background-color: rgb(255 255 255);
    }

    #fi-sticky-table thead th[data-frozen-col] {
        z-index: 30; /* 見出し行 × 固定列が重なる左上のセルを最前面に */
    }

    #fi-sticky-table [data-frozen-col-last] {
        box-shadow: 1px 0 0 0 rgb(0 0 0 / 8%);
    }

    @media (prefers-color-scheme: dark) {
        #fi-sticky-table thead th,
        #fi-sticky-table [data-frozen-col] {
            background-color: rgb(17 24 39);
        }

        #fi-sticky-table [data-frozen-col-last] {
            box-shadow: 1px 0 0 0 rgb(255 255 255 / 8%);
        }
    }
</style>
<script>
    (() => {
        const FROZEN_COLS = {{ (int) $frozenCols }};

        function apply() {
            const table = document.querySelector('table.fi-ta-table');
            if (!table) {
                return;
            }
            table.id = 'fi-sticky-table';

            const scrollBox = table.closest('.fi-ta-content');
            if (scrollBox) {
                scrollBox.id = 'fi-sticky-scroll';
            }

            const headerCells = table.querySelectorAll('thead tr:first-child > *');
            if (headerCells.length < FROZEN_COLS) {
                return;
            }

            let left = 0;
            const lefts = [];
            for (let i = 0; i < FROZEN_COLS; i++) {
                lefts.push(left);
                left += headerCells[i].getBoundingClientRect().width;
            }

            table.querySelectorAll('tr').forEach((row) => {
                const cells = row.children;
                for (let i = 0; i < FROZEN_COLS && i < cells.length; i++) {
                    cells[i].setAttribute('data-frozen-col', '');
                    cells[i].style.left = lefts[i] + 'px';
                }
                if (cells.length >= FROZEN_COLS) {
                    cells[FROZEN_COLS - 1].setAttribute('data-frozen-col-last', '');
                }
            });
        }

        let scheduled = false;
        function schedule() {
            if (scheduled) {
                return;
            }
            scheduled = true;
            // 非表示タブだと止まる requestAnimationFrame ではなく setTimeout でまとめて実行する。
            setTimeout(() => {
                scheduled = false;
                apply();
            }, 0);
        }

        document.addEventListener('DOMContentLoaded', schedule);
        document.addEventListener('livewire:navigated', schedule);
        window.addEventListener('resize', schedule);
        if (document.readyState !== 'loading') {
            schedule();
        }

        // 検索・並び替え・ページ送り（Livewire がテーブルの行を作り直す）のたびに再計算する。
        // 自前で付けた style/属性の変更では発火しないよう childList のみ監視する。
        document.addEventListener('DOMContentLoaded', () => {
            new MutationObserver(schedule).observe(document.body, {
                childList: true,
                subtree: true,
            });
        });
    })();
</script>
