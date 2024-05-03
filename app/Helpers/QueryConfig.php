<?php

namespace App\Helpers;

class QueryConfig
{
    /**
     * Pagination constants
     */
    const SORT_ASC = 'ASC';
    const SORT_DESC = 'DESC';

    /**
     * Pagination
     * @var bool $paginated
     */
    private bool $paginated = true;

    /**
     * paginate per page
     * @var int $perPage
     */
    private int $perPage;

    /**
     * paginate page number
     * @var int $page
     */
    private int $page;

    /**
     * Filters
     * @var array $filters
     */
    private array $filters = [];

    /**
     * order by column
     * @var string|null $orderBy
     */
    private ?string $orderBy;


    /**
     * Filters
     * @var array $selectColumns
     */
    private array $selectColumns = ['*'];

    /**
     * Filters
     * @var string $selectedRaw
     */
    private string $selectedRaw = '';

    /**
     * Order by direction
     * @var string $orderDirection
     */
    private string $direction = self::SORT_DESC;

    /**
     * SearchQueryConfig constructor
     */
    public function __construct()
    {
        $configValuePerPage = config('constants.DEFAULT_PER_PAGE');
        $this->perPage = is_null($configValuePerPage) ? 10 : (int)$configValuePerPage;

        $configValuePage = config('constants.DEFAULT_PAGE');
        $this->page = is_null($configValuePage) ? 1 : (int)$configValuePage;


    }


    public final function getPage(): int
    {
        return $this->page;
    }

    public final function getPerPage(): int
    {
        return $this->perPage;
    }

    public final function setPerPage(int $perPage): static
    {
        $this->perPage = $perPage;

        return $this;
    }

    public final function getOrderBy(): string
    {
        return $this->orderBy;
    }

    public final function setOrderBy(string $orderBy): static
    {
        $this->orderBy = $orderBy;
        return $this;
    }


    public final function getFilters(): array
    {
        return $this->filters;
    }

    public final function setFilters(array $filters): static
    {
        $this->filters = $filters;
        return $this;

    }

    public final function getDirection(): string
    {
        return $this->direction;
    }

    public final function setDirection(mixed $direction): static
    {
        $this->direction = $direction;
        return $this;
    }

    public final function getPaginated(): bool
    {
        return $this->paginated;
    }

    public final function setPaginated(mixed $pagination): static
    {
        $this->paginated = $pagination;
        return $this;
    }

    public final function isPaginated(): bool
    {
        return $this->paginated;
    }


}
