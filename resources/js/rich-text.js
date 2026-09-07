// 会員向けの素朴な Blade フォーム（掲示板・メッセージ）で使うリッチテキストエディタ。
// Trix（Basecamp）。<x-rich-text> コンポーネントが <trix-editor> を吐く。
// 出力 HTML はサーバ側 App\Support\RichText::clean() で必ずサニタイズする。
import 'trix';
import 'trix/dist/trix.css';
import '../css/rich-text.css';

// 添付ファイル機能は使わない（アップロード先が無い）。
// ファイルのドロップ/貼り付けを拒否し、ツールバーの添付ボタンも隠す（CSS 側）。
document.addEventListener('trix-file-accept', (event) => event.preventDefault());
