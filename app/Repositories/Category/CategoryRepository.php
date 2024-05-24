<?php

namespace App\Repositories\Category;


use App\Helpers\QueryConfig;
use App\Models\Category;
use App\Repositories\Media\MediaRepository;
use App\Traits\PaginationParams;
use Exception;
use Illuminate\Http\UploadedFile;
use Illuminate\Pagination\LengthAwarePaginator;
use Ramsey\Collection\Collection;

class CategoryRepository
{
    use PaginationParams;


    /**
     * create a new category
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
     * delete a category
     * @param $categoryId
     * @return void
     */
    public final function deleteCategory($categoryId): void
    {
        $category = Category::find($categoryId);
        $category->delete();

    }

    /**
     * get all categories
     * @param QueryConfig $queryConfig
     * @return LengthAwarePaginator|Collection
     */

    public static function indexCategories(QueryConfig $queryConfig): LengthAwarePaginator|\Illuminate\Support\Collection
    {
        $query = Category::with('media')->withCount('courses')->newQuery();
        Category::applyFilters($queryConfig->getFilters(), $query);
        $categories = $query->orderBy($queryConfig->getOrderBy(), $queryConfig->getDirection())->get();
        if ($queryConfig->getPaginated()) {
            return self::applyPagination($categories, $queryConfig);
        }
        return $categories;
    }

    /** get categories which have at least one active course
     * @param QueryConfig $queryConfig
     * @return LengthAwarePaginator|\Illuminate\Support\Collection
     */
    public static function indexCategoriesWithCourses(QueryConfig $queryConfig): LengthAwarePaginator|\Illuminate\Support\Collection
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

    public static function indexCategoriesWithLearningPaths(QueryConfig $queryConfig): LengthAwarePaginator|\Illuminate\Support\Collection
    {

        $query = Category::with('media')->whereHas('learningPaths', function ($query) {
            $query->where('is_active', true);
        })->newQuery();
        Category::applyFilters($queryConfig->getFilters(), $query);
        $categories = $query->orderBy($queryConfig->getOrderBy(), $queryConfig->getDirection())->get();
        if ($queryConfig->getPaginated()) {
            return self::applyPagination($categories, $queryConfig);
        }
        return $categories;
    }

    /**
     * get a category by id
     * @param $categoryId
     * @return Category
     */

    public final function getCategory($categoryId): Category
    {
        return Category::find($categoryId)->load('media');
    }

    /** update a category
     * @param $data
     * @param $id
     * @return Category
     * @throws Exception
     */
    public final function updateCategory($data, $id): Category
    {
        $category = Category::findOrfail($id);
        if(!$category){
           throw new Exception('Category not found');
        }
        $mediaFile = $data['media'] ?? null;

        $category->update($data);

        $currentMedia = $category->media->first();
        if ($mediaFile instanceof UploadedFile) {
            $media =MediaRepository::attachOrUpdateMediaForModel($category, $mediaFile, $currentMedia->id);
            $category->media()->save($media);
        }
        return $category;

}

}
