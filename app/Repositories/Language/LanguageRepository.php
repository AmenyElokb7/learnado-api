<?php

namespace App\Repositories\Language;

use App\Helpers\QueryConfig;
use App\Models\Language;
use App\Traits\PaginationParams;
use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Ramsey\Collection\Collection;

class LanguageRepository
{
    use PaginationParams;

    /**
     * @param $data
     * @return Language
     */
    public final function createLanguage($data): Language
    {
        return Language::create($data);
    }

    /**
     * @param $languageId
     * @return void
     */
    public final function deleteLanguage($languageId): void
    {
        $language = Language::find($languageId);
        $language->delete();
    }

    /**
     * @param QueryConfig $queryConfig
     * @return LengthAwarePaginator|\Illuminate\Support\Collection
     */
    public static function indexLanguages(QueryConfig $queryConfig): LengthAwarePaginator|\Illuminate\Support\Collection
    {
        $query = Language::query();
        Language::applyFilters($queryConfig->getFilters(), $query);

        $languages = $query->orderBy($queryConfig->getOrderBy(), $queryConfig->getDirection())->get();
        if ($queryConfig->getPaginated()) {
            return self::applyPagination($languages, $queryConfig);
        }
        return $languages;
    }

    public static function indexLanguagesWithCourses(QueryConfig $queryConfig): LengthAwarePaginator|\Illuminate\Support\Collection
    {
        $query = Language::whereHas('courses', function ($query) {
            $query->where('is_active', true)->where('is_public', true)->where('is_offline', false);
        })->newQuery();
        Language::applyFilters($queryConfig->getFilters(), $query);

        $languages = $query->orderBy($queryConfig->getOrderBy(), $queryConfig->getDirection())->get();
        if ($queryConfig->getPaginated()) {
            return self::applyPagination($languages, $queryConfig);
        }
        return $languages;
    }
}
