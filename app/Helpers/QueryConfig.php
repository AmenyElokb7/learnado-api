<?php

namespace App\Helpers;
class QueryConfig
{
    /**
     * Pagination constants
     */
    const SORT_ASC = 'ASC';
    const SORT_DESC = 'DESC';
    const DEFAULT_PAGE = 10;
    const PAGE = 1;
    /**
     * Pagination
     * @var bool $paginated
     */
    private bool $paginated = true;
    /**
     * paginate per page
     * @var int $perPage
     */
    private int $perPage = self::DEFAULT_PAGE;
    /**
     * paginate page number
     * @var int $page
     */
    private int $page = self::PAGE;
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

    public final function setDirection(mixed $DIRECTION): static
    {
        $this->direction = $DIRECTION;
        return $this;
    }

    public final function getPaginated(): bool
    {
        return $this->paginated;
    }

    public final function setPaginated(mixed $PAGINATION): static
    {
        $this->paginated = $PAGINATION;
        return $this;
    }

    public final function isPaginated(): bool
    {
        return $this->paginated;
    }
}
