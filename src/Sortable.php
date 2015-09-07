<?php

namespace Eloquent\Sortable;

use Illuminate\Database\Eloquent\Model;

interface Sortable {

    /**
     * Insert model before current
     *
     * @param Model $model
     * @return mixed
     */
    public function before(Model $model);

    /**
     * Insert model after current .
     *
     * @param Model $model
     * @return mixed
     */
    public function after(Model $model);
}