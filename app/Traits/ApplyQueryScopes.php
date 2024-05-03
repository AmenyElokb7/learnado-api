<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

trait ApplyQueryScopes
{
    /**
     * @var Model
     */
    protected Model $query;

    /**
     * @param array $filters
     * @param $query
     */
    public static function applyFilters(array $filters, $query): void
    {
        $self = new self();
        foreach ($filters as $filter => $value) {
            if (self::isValidFilter($filter)) {
                $methodName = 'scopeBy' . Str::studly($filter);
                if (method_exists($self, $methodName)) {

                    $self->{$methodName}($query, $value);
                }
            }
        }
    }

    /**
     * @param $filter
     * @return bool
     */
    private static function isValidFilter($filter): bool
    {
        return method_exists(self::class, 'scopeBy' . Str::studly($filter));
    }
}

