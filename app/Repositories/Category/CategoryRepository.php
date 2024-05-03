<?php

namespace App\Repositories\Category;


use App\Helpers\QueryConfig;
use App\Models\Category;
use App\Repositories\Media\MediaRepository;
use App\Traits\PaginationParams;
use Illuminate\Http\UploadedFile;
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
        $mediaFile = $data['media'] ?? null;
        unset($data['media']);
        $category = Category::create($data);
        if ($mediaFile instanceof UploadedFile) {

            $media = MediaRepository::attachOrUpdateMediaForModel($category, $mediaFile);
            $category->media()->save($media);
        }
        return $category;
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
     * @return LengthAwarePaginator|Collection
     */

    public final function indexCategories(QueryConfig $queryConfig): LengthAwarePaginator|\Illuminate\Support\Collection
    {
        $query = Category::with('media')->withCount('courses')->newQuery();
        Category::applyFilters($queryConfig->getFilters(), $query);
        $categories = $query->orderBy($queryConfig->getOrderBy(), $queryConfig->getDirection())->get();
        if ($queryConfig->getPaginated()) {
            return self::applyPagination($categories, $queryConfig);
        }
        dump($categories);
        return $categories;
    }
    // get categories which have at least number of courses 1
    public final function indexCategoriesWithCourses(QueryConfig $queryConfig): LengthAwarePaginator|\Illuminate\Support\Collection
    {

        $query = Category::with('media')->whereHas('courses', function ($query) {
            $query->where('is_active', true);
        })->newQuery();
        Category::applyFilters($queryConfig->getFilters(), $query);
        $categories = $query->orderBy($queryConfig->getOrderBy(), $queryConfig->getDirection())->get();
        if ($queryConfig->getPaginated()) {
            return self::applyPagination($categories, $queryConfig);
        }
        return $categories;
    }

    public final function getCategory($categoryId): Category
    {
        return Category::find($categoryId)->load('media');
    }

    public final function updateCategory($categoryId, $data): Category
    {
        $category = Category::find($categoryId);
        $mediaFile = $data['media'] ?? null;
        unset($data['media']);
        $category->update($data);
        if ($mediaFile instanceof UploadedFile) {
            $media = MediaRepository::attachOrUpdateMediaForModel($category, $mediaFile);
            $category->media()->save($media);
        }
        return $category;

}

}
