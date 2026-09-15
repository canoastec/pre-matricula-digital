<?php

namespace iEducar\Packages\PreMatricula\Models\Builders;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as QueryBuilder;

class PreRegistrationBuilder extends Builder
{
    public const DATE = 1;

    public const POSITION = 2;

    public const SCHOOL = 3;

    public const NAME = 4;

    public const DATE_OF_BIRTH = 5;

    public function __construct(QueryBuilder $query)
    {
        $query->select('preregistrations.*', 'preregistration_position.position');
        $query->join('preregistration_position', 'preregistrations.id', '=', 'preregistration_position.preregistration_id');

        parent::__construct($query);

        $this->orderByPriorityByMonth();
    }

    /**
     * Ordena por mês (mais recente), pontuação e data de criação.
     */
    public function orderByPriorityByMonth(): static
    {
        return $this
            ->orderByRaw("DATE_TRUNC('month', preregistrations.created_at) DESC")
            ->orderByDesc('preregistrations.priority')
            ->orderByDesc('preregistrations.created_at')
            ->orderByDesc('preregistrations.id');
    }

    public function sort(int $type): static
    {
        $this->reorder();

        return match ($type) {
            self::DATE => $this->orderBy('created_at'),
            self::POSITION => $this->orderBy('position'),
            self::SCHOOL => $this->orderBySchoolName(),
            self::NAME => $this->orderByStudentName(),
            self::DATE_OF_BIRTH => $this->orderByStudentDateOfBirth(),
            default => $this->orderByPriorityByMonth(),
        };
    }

    /**
     * TODO: mover para uma trait que possa ser reaproveitada
     *
     * @param  string  $relation
     * @param  callable|null  $callback
     * @return PreRegistrationBuilder
     */
    public function merge($relation, $callback = null)
    {
        $model = $this->getModel();

        $rel = $model->{$relation}();

        $related = $rel->getRelated();
        $foreignKey = $rel->getForeignKeyName();
        $ownerKey = $rel->getOwnerKeyName();
        $table = $related->getTable();

        /** @var PreRegistrationBuilder $query */
        $query = $this->newQuery()->join(
            $table,
            "{$table}.{$ownerKey}",
            '=',
            "{$model->getTable()}.{$foreignKey}"
        );

        if ($callback) {
            $callback($query);
        }

        return $query;
    }

    /**
     * Ordena pelo nome do(a) aluno(a).
     *
     * @return PreRegistrationBuilder
     */
    public function orderByStudentName()
    {
        return $this->merge('student', function (Builder $query) {
            $query->orderByRaw('slug');
        });
    }

    /**
     * Ordena pela data de nascimento do(a) aluno(a).
     *
     * @return PreRegistrationBuilder
     */
    public function orderByStudentDateOfBirth()
    {
        return $this->merge('student', function (Builder $query) {
            $query->orderBy('date_of_birth');
        });
    }

    /**
     * Ordena pela data de nascimento do(a) aluno(a).
     *
     * @return PreRegistrationBuilder
     */
    public function orderBySchoolName()
    {
        return $this->merge('school', function (Builder $query) {
            $order = 'unaccent(name)';

            if ($query->getConnection()->getDriverName() === 'sqlite') {
                $order = 'name'; // @codeCoverageIgnore
            }

            // TODO remover uso da função `unaccent`
            $query->orderByRaw($order);
        });
    }

    public function match(array $data): static
    {
        return $this->whereHas('student', function ($query) use ($data) {
            $query->where(function ($query) use ($data) {
                if (isset($data['cpf'])) {
                    $query->orWhere('cpf', $data['cpf']);
                }

                if (isset($data['rg'])) {
                    $query->orWhere('rg', $data['rg']);
                }

                if (isset($data['birth_certificate'])) {
                    $query->orWhere('birth_certificate', $data['birth_certificate']);
                }

                $query->orWhere(function ($query) use ($data) {
                    $query->where('slug', $data['name']);
                    $query->where('date_of_birth', $data['date_of_birth']);
                });
            });
        });
    }
}
