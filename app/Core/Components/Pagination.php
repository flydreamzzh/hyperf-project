<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
namespace App\Core\Components;

use Hyperf\Utils\Context;
use Psr\Http\Message\ServerRequestInterface;

/**
 * 功能摘自YII2
 * Class Pagination.
 */
class Pagination
{
    /**
     * @var string name of the parameter storing the current page index
     * @see params
     */
    public $pageParam = 'page';

    /**
     * @var string name of the parameter storing the page size
     * @see params
     */
    public $pageSizeParam = 'pageSize';

    /**
     * @var array parameters (name => value) that should be used to obtain the current page number
     *            and to create new pagination URLs. If not set, all parameters from $_GET will be used instead.
     *
     * In order to add hash to all links use `array_merge($_GET, ['#' => 'my-hash'])`.
     *
     * The array element indexed by [[pageParam]] is considered to be the current page number (defaults to 0);
     * while the element indexed by [[pageSizeParam]] is treated as the page size (defaults to [[defaultPageSize]]).
     */
    public $params;

    /**
     * @var bool whether to check if [[page]] is within valid range.
     *           When this property is true, the value of [[page]] will always be between 0 and ([[pageCount]]-1).
     *           Because [[pageCount]] relies on the correct value of [[totalCount]] which may not be available
     *           in some cases (e.g. MongoDB), you may want to set this property to be false to disable the page
     *           number validation. By doing so, [[page]] will return the value indexed by [[pageParam]] in [[params]].
     */
    public $validatePage = true;

    /**
     * @var int total number of items
     */
    public $totalCount = 0;

    /**
     * @var int the default page size. This property will be returned by [[pageSize]] when page size
     *          cannot be determined by [[pageSizeParam]] from [[params]].
     */
    public $defaultPageSize = 20;

    /**
     * @var array|false the page size limits. The first array element stands for the minimal page size, and the second
     *                  the maximal page size. If this is false, it means [[pageSize]] should always return the value of [[defaultPageSize]].
     */
    public $pageSizeLimit = [1, 50];

    /**
     * @var int number of items on each page.
     *          If it is less than 1, it means the page size is infinite, and thus a single page contains all items.
     */
    private $_pageSize;

    private $_page;

    public function __construct($config = [])
    {
        if (! empty($config)) {
            foreach ($config as $name => $value) {
                $this->{$name} = $value;
            }
        }
    }

    /**
     * @return int number of pages
     */
    public function getPageCount(): int
    {
        $pageSize = $this->getPageSize();
        if ($pageSize < 1) {
            return $this->totalCount > 0 ? 1 : 0;
        }

        $totalCount = $this->totalCount < 0 ? 0 : (int) $this->totalCount;

        return (int) (($totalCount + $pageSize - 1) / $pageSize);
    }

    /**
     * Returns the zero-based current page number.
     * @param bool $recalculate whether to recalculate the current page based on the page size and item count
     * @return int the zero-based current page number
     */
    public function getPage($recalculate = false): int
    {
        if ($this->_page === null || $recalculate) {
            $page = (int) $this->getQueryParam($this->pageParam, 1) - 1;
            $this->setPage($page, true);
        }

        return $this->_page;
    }

    /**
     * Sets the current page number.
     * @param int $value the zero-based index of the current page
     * @param bool $validatePage whether to validate the page number. Note that in order
     *                           to validate the page number, both [[validatePage]] and this parameter must be true.
     */
    public function setPage(int $value, $validatePage = false)
    {
        if ($value === null) {
            $this->_page = null;
        } else {
            $value = (int) $value;
            if ($validatePage && $this->validatePage) {
                $pageCount = $this->getPageCount();
                if ($value >= $pageCount) {
                    $value = $pageCount - 1;
                }
            }
            if ($value < 0) {
                $value = 0;
            }
            $this->_page = $value;
        }
    }

    /**
     * Returns the number of items per page.
     * By default, this method will try to determine the page size by [[pageSizeParam]] in [[params]].
     * If the page size cannot be determined this way, [[defaultPageSize]] will be returned.
     * @return int the number of items per page. If it is less than 1, it means the page size is infinite,
     *             and thus a single page contains all items.
     * @see pageSizeLimit
     */
    public function getPageSize(): int
    {
        if ($this->_pageSize === null) {
            if (empty($this->pageSizeLimit)) {
                $pageSize = $this->defaultPageSize;
                $this->setPageSize($pageSize);
            } else {
                $pageSize = (int) $this->getQueryParam($this->pageSizeParam, $this->defaultPageSize);
                $this->setPageSize($pageSize, true);
            }
        }

        return $this->_pageSize;
    }

    /**
     * @param int $value the number of items per page
     * @param bool $validatePageSize whether to validate page size
     */
    public function setPageSize(int $value, $validatePageSize = false)
    {
        if ($value === null) {
            $this->_pageSize = null;
        } else {
            $value = (int) $value;
            if ($validatePageSize && isset($this->pageSizeLimit[0], $this->pageSizeLimit[1]) && count($this->pageSizeLimit) === 2) {
                if ($value < $this->pageSizeLimit[0]) {
                    $value = $this->pageSizeLimit[0];
                } elseif ($value > $this->pageSizeLimit[1]) {
                    $value = $this->pageSizeLimit[1];
                }
            }
            $this->_pageSize = $value;
        }
    }

    /**
     * @return int the offset of the data. This may be used to set the
     *             OFFSET value for a SQL statement for fetching the current page of data.
     */
    public function getOffset()
    {
        $pageSize = $this->getPageSize();

        return $pageSize < 1 ? 0 : $this->getPage() * $pageSize;
    }

    /**
     * @return int the limit of the data. This may be used to set the
     *             LIMIT value for a SQL statement for fetching the current page of data.
     *             Note that if the page size is infinite, a value -1 will be returned.
     */
    public function getLimit(): int
    {
        $pageSize = $this->getPageSize();

        return $pageSize < 1 ? -1 : $pageSize;
    }

    /**
     * Returns the value of the specified query parameter.
     * This method returns the named parameter value from [[params]]. Null is returned if the value does not exist.
     * @param string $name the parameter name
     * @param null $defaultValue the value to be returned when the specified parameter does not exist in [[params]]
     * @return string the parameter value
     */
    protected function getQueryParam(string $name, $defaultValue = null)
    {
        if (($params = $this->params) === null) {
            $request = Context::get(ServerRequestInterface::class);
            $params = $request ? $request->getQueryParams() : [];
        }

        return isset($params[$name]) && is_scalar($params[$name]) ? $params[$name] : $defaultValue;
    }
}
