<?php


namespace App\Traits;

use App\Helpers\QueryConfig;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

/**
 * Trait PaginationParams
 */
trait PaginationParams
{
    public final function getPaginationParams(Request $request): array
    {
        return [
            'PAGE' => $request->input('page', config('constants.PAGINATE.DEFAULT_PAGE')),
            'PER_PAGE' => $request->input('per_page', config('constants.PAGINATE.DEFAULT_PER_PAGE')),
            'ORDER_BY' => $request->input('order_by', config('constants.PAGINATE.DEFAULT_ORDER_BY')),
            'DIRECTION' => $request->input('direction', config('constants.PAGINATE.DEFAULT_DIRECTION')),
            'PAGINATION' => (bool)$request->input('pagination', config('constants.PAGINATE.DEFAULT_PAGINATION')),
            'KEYWORD' => $request->input('keyword', ''),
        ];
    }

    /**
     * @param mixed $items
     * @param QueryConfig $data
     * @param array $options
     * @return LengthAwarePaginator
     */
    public static function applyPagination(mixed $items, QueryConfig $data, array $options = []): LengthAwarePaginator
    {
        $items = $items instanceof Collection ? $items : Collection::make($items);
        $page = $data->getPage();
        $perPage = $data->getPerPage();
        $path = $options['path'] ?? null;
        $total = $items->count();
        $results = $total ? $items->forPage($page, $perPage) : new Collection;
        return new LengthAwarePaginator(
            $results,
            $total,
            $perPage,
            $page,
            [
                'path' => $path,
                'pageName' => 'page',
            ]
        );
    }
}
