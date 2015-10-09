<?php

namespace Eloquent\Sortable;

use Illuminate\Database\Eloquent\Model;

trait SortableTrait {

    /**
     * On creating increment position .
     *
     */
    public static function bootSortableTrait() {
        static::creating(
            function ($model) {
                $model->position = static::max('position') + 1;
            }
        );
    }

    /**
     * Get sorted by position .
     *
     * @param $query
     * @return mixed
     */
    public function scopeSorted($query) {
        return $query->orderBy(
            $this->getPositionColumn()
        );
    }

    /**
     * Insert model before current
     *
     * @param Model $model
     * @return mixed
     */
    public function before(Model $model) {
        if ($model->getTable() != $this->getTable())
            return false;

        $field = $this->getPositionColumn();

        if ($this->{$field}  > $model->{$field}) {

            $this->_transaction(function () use ($model, $field) {

                $this->where($field, '>=', $model->{$field})
                    ->where($field, '<', $this->{$field})
                    ->increment($field);

                $this->{$field}  = $model->{$field};
                $model->{$field} = $model->{$field} + 1;
                $this->save();
            });

        } elseif ($this->{$field} < $model->{$field})  {
            $this->_transaction(function () use ($model, $field) {

                $this->where($field, '<', $model->{$field})
                    ->where($field, '>', $this->{$field})
                    ->decrement($field);

                $this->{$field} = $model->{$field} - 1;
                $this->save();
            });
        }

        return $this;
    }

    /**
     * Insert model after current .
     *
     * @param Model $model
     * @return mixed
     */
    public function after(Model $model) {
        if ($model->getTable() != $this->getTable())
            return false;

        $field = $this->getPositionColumn();

        if ($this->{$field} > $model->{$field}) {

            $this->_transaction(function () use ($model, $field) {

                $this->where($field, '>', $model->{$field})
                    ->where($field, '<', $this->{$field})
                    ->increment($field);

                $this->{$field} = $model->{$field} + 1;
                $this->save();
            });

        } elseif ($this->{$field} < $model->{$field}) {
            $this->_transaction(function () use ($model, $field) {

                $this->where($field, '<=', $model->{$field})
                    ->where($field, '>', $this->{$field})
                    ->decrement($field);

                $this->{$field} = $model->{$field};
                $model->{$field} = $model->{$field} - 1;
                $this->save();
            });
        }

        $this->save();

        return $this;
    }

    /**
     * Decorate into transaction .
     *
     * @param callable $callback
     * @return mixed
     */
    protected function _transaction(\Closure $callback) {
        return $this->getConnection()->transaction($callback);
    }

    /**
     * Get position column .
     *
     * @param string $default
     * @return string
     */
    protected function getPositionColumn($default = 'position') {
        return isset(static::$sortableColumm) ? static::$sortableColumm : $default;
    }
}