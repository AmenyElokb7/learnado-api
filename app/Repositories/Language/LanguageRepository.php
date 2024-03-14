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
     * @return LengthAwarePaginator|Builder|Collection
     */
    public final function indexLanguages(QueryConfig $queryConfig): LengthAwarePaginator|Builder|Collection
    {
        $query = Language::query();
        $languages = $query->orderBy($queryConfig->getOrderBy(), $queryConfig->getDirection());
        if ($queryConfig->getPaginated()) {
            return self::applyPagination($languages, $queryConfig);
        }
        return $languages;
    }
}
