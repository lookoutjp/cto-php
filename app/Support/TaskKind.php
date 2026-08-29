<?php

namespace App\Support;

use App\Models\Problem;
use App\Models\Risk;
use App\Models\Todo;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * 会員向けタスク系画面（todo / problem / risk）のメタ情報。
 * ルート {kind} からモデルや表示名を引く。
 */
class TaskKind
{
    private const MAP = [
        'todo' => [
            'label' => 'TODO',
            'model' => Todo::class,
            'function' => 'todofunction',
        ],
        'problem' => [
            'label' => '課題',
            'model' => Problem::class,
            'function' => 'problemfunction',
        ],
        'risk' => [
            'label' => 'リスク',
            'model' => Risk::class,
            'function' => 'riskfunction',
        ],
    ];

    public function __construct(
        public readonly string $slug,
        public readonly string $label,
        /** @var class-string<\Illuminate\Database\Eloquent\Model> */
        public readonly string $model,
        public readonly string $function,
    ) {}

    public static function fromSlug(string $slug): self
    {
        $def = self::MAP[$slug] ?? throw new NotFoundHttpException("unknown task kind: {$slug}");

        return new self($slug, $def['label'], $def['model'], $def['function']);
    }

    /** @return array<string, self> */
    public static function all(): array
    {
        return array_map(
            fn (string $slug) => self::fromSlug($slug),
            array_combine(array_keys(self::MAP), array_keys(self::MAP)),
        );
    }

    /** @return \Illuminate\Database\Eloquent\Model */
    public function newModel()
    {
        return new $this->model;
    }

    public function query()
    {
        return $this->model::query();
    }
}
