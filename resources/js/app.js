import './bootstrap';

import './wbs-sortable';
import './category-sortable';

// Alpine.js は自前で import/start しない — Livewire 3 が同梱の Alpine を
// 全ページに自動注入・自動起動するため、ここで別インスタンスを起動すると
// 「Detected multiple instances of Alpine running」となり x-data/x-show/@click
// などのディレクティブが正しく動かなくなる（ハンバーガーメニュー等が無反応になる）。
// Alpine の拡張が必要な場合は `document.addEventListener('alpine:init', ...)`
// で Livewire 側のインスタンスに登録すること。
