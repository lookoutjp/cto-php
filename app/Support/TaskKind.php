<?php

namespace App\Support;

use App\Models\ChangeRequest;
use App\Models\Problem;
use App\Models\Product;
use App\Models\Risk;
use App\Models\RoutineWorkList;
use App\Models\Todo;
use Illuminate\Database\Eloquent\Model;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * 会員向けタスク系画面（todo / problem / risk / product / routinework）のメタ情報。
 * ルート {kind} からモデル・表示名・使うフィールドを引く。
 *
 * features: 一覧/フォーム/詳細で出す任意フィールドの集合
 *   date（期限系）, team, situation, criteria（完了基準）, approver, content,
 *   stage, responsible（責任者）
 */
class TaskKind
{
    private const MAP = [
        'todo' => [
            'label' => 'TODO', 'model' => Todo::class, 'function' => 'todofunction',
            'date_label' => '期限',
            'features' => ['date', 'team', 'situation', 'criteria', 'approver', 'content', 'today'],
        ],
        'problem' => [
            'label' => '課題', 'model' => Problem::class, 'function' => 'problemfunction',
            'date_label' => '期限',
            'features' => ['date', 'team', 'situation', 'criteria', 'approver', 'content', 'today'],
        ],
        'risk' => [
            'label' => 'リスク', 'model' => Risk::class, 'function' => 'riskfunction',
            'date_label' => '期限',
            'features' => ['date', 'team', 'situation', 'criteria', 'approver', 'content', 'today'],
        ],
        'product' => [
            'label' => '成果物', 'model' => Product::class, 'function' => 'productfunction',
            'date_label' => null,
            'features' => ['content', 'stage', 'responsible'],
        ],
        'routinework' => [
            'label' => '定例作業', 'model' => RoutineWorkList::class, 'function' => 'routineworkfunction',
            'date_label' => '実施日',
            'features' => ['date', 'team', 'situation', 'criteria', 'content', 'today'],
        ],
        'change' => [
            'label' => '変更管理', 'model' => ChangeRequest::class, 'function' => 'changefunction',
            'date_label' => '期限',
            'features' => ['date', 'team', 'approver', 'content', 'stage', 'today', 'changedetail'],
        ],
    ];

    public function __construct(
        public readonly string $slug,
        public readonly string $label,
        /** @var class-string<Model> */
        public readonly string $model,
        public readonly string $function,
        public readonly ?string $dateLabel,
        /** @var list<string> */
        public readonly array $features,
    ) {}

    public static function fromSlug(string $slug): self
    {
        $def = self::MAP[$slug] ?? throw new NotFoundHttpException("unknown task kind: {$slug}");

        return new self($slug, $def['label'], $def['model'], $def['function'], $def['date_label'], $def['features']);
    }

    /** @return list<string> */
    public static function slugs(): array
    {
        return array_keys(self::MAP);
    }

    /** @return array<int, self> */
    public static function all(): array
    {
        return array_map([self::class, 'fromSlug'], self::slugs());
    }

    public function has(string $feature): bool
    {
        return in_array($feature, $this->features, true);
    }

    public function dateColumn(): ?string
    {
        return $this->model::taskDateColumn();
    }

    public function statusKind(): string
    {
        return $this->model::$taskKind;
    }

    public function newModel()
    {
        return new $this->model;
    }

    public function query()
    {
        return $this->model::query();
    }
}
