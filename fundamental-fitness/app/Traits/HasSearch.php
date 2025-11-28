<?php

namespace App\Traits;

trait HasSearch
{
    /**
     * Apply search filtering to a query.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string|null $search
     * @param array $columns
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function applySearch($query, $search, array $columns = ['name'])
    {
        if ($search) {
            $query->whereAny($columns, 'like', '%' . $search . '%');
        }

        return $query;
    }
}
