<?php

namespace App\Repositories\Category;


use App\Helpers\QueryConfig;
use App\Models\Category;
use App\Traits\PaginationParams;
use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Ramsey\Collection\Collection;

class CategoryRepository
{
    use PaginationParams;


    /**
     * @param $data
     * @return Category
     */
    public final function createCategory($data): Category
    {
        return Category::create($data);
    }

    /**
     * @param $categoryId
     * @return void
     */
    public final function deleteCategory($categoryId): void
    {
        $category = Category::find($categoryId);
        $category->delete();

    }

    /**
     * @param QueryConfig $queryConfig
     * @return LengthAwarePaginator|Builder|Collection
     */

    public final function indexCategories(QueryConfig $queryConfig): LengthAwarePaginator|Builder|Collection
    {
        $query = Category::query();
        $categories = $query->orderBy($queryConfig->getOrderBy(), $queryConfig->getDirection());
        if ($queryConfig->getPaginated()) {
            return self::applyPagination($categories, $queryConfig);
        }
        return $categories;
    }


}
