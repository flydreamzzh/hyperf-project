<?php


namespace App\Core\Components;

use Hyperf\DbConnection\Db;

/**
 * 添加外联表别名
 * Class Builder
 * @package App\Core\Components
 */
class Builder extends \Hyperf\Database\Query\Builder
{
    /**
     * @param string|array $name
     * @return $this
     */
    public function alias($name)
    {
        $alias = "$this->from as $name";
        $this->from($alias);
        return $this;
    }

    /**
     * 兼容数组别名，和子查询语句
     * @param string|array $table
     * @return \Hyperf\Database\Query\Builder
     */
    public function from($table)
    {
        if (is_array($table)) {
            $tables = [];
            foreach ($table as $alias => $item) {
                if ($item instanceof \Hyperf\Database\Query\Builder || $item instanceof \Hyperf\Database\Model\Builder) {
                    $aliasTable = $this->getTablePrefix() . $alias;
                    $tables[] = is_string($alias) ? "({$item->toSql()}) as {$aliasTable}" : "({$item->toSql()})";
                    $this->mergeBindings($item->getQuery());
                } else if (is_string($item)) {
                    $aliasTable = $this->getTablePrefix() . $alias;
                    $tables[] = !is_string($alias) ? (string)$item . "as $aliasTable" : $item;
                }
            }
            $table = implode(', ', $tables);
            $table = Db::raw($table);
        }
        return parent::from($table);
    }

    /**
     * @param $column
     * @param null $operator
     * @param null $value
     * @param string $boolean
     * @return $this
     */
    public function andFilterWhere($column, $operator = null, $value = null, $boolean = 'and')
    {
        $args = [];
        if (is_array($column)) {
            if (is_array(current($column))) {
                $filterColumns = array_filter($column, function ($item) {
                    if (is_array($item)) {
                        if ($this->isFilter($item)) {
                            return true;
                        }
                    }
                    return false;
                });
                if ($filterColumns) {
                    $args = [$filterColumns, $operator, $value, $boolean];
                }
            } else {
                if (is_numeric(array_key_first($column))) {
                    if ($this->isFilter($column)) {
                        $args = func_get_args();
                    }
                } else {
                    $filterColumns = array_filter($column, function ($item) {
                        if (!$this->isEmpty($item)) {
                            return true;
                        }
                        return false;
                    });
                    if ($filterColumns) {
                        $args = [$filterColumns, $operator, $value, $boolean];
                    }
                }
            }
        } else {
            $len = count(func_get_args());
            if (($len == 2 && !$this->isEmpty($operator)) || ($len >= 3 && !!$this->isEmpty($value))) {
                $args = func_get_args();
            }
        }
        $args && $this->where(...$args);
        return $this;
    }

    /**
     * @param $column
     * @param null $operator
     * @param null $value
     */
    public function orFilterWhere($column, $operator = null, $value = null)
    {
        [$value, $operator] = $this->prepareValueAndOperator($value, $operator, func_num_args() === 2);

        $this->andFilterWhere($column, $operator, $value, 'or');
    }

    /**
     * Add an "order by" clause to the query.
     *
     * @param string|array $column
     * @param string $direction
     * @return $this
     */
    public function orderBy($column, $direction = 'asc')
    {
        if (is_array($column)) {
            foreach ($column as $item => $direct) {
                if (in_array($direct, [SORT_ASC, SORT_DESC])) {
                    $direct == SORT_ASC && $direct = 'asc';
                    $direct == SORT_DESC && $direct = 'desc';
                }
                parent::orderBy($item, $direct);
            }
        } else {
            if (in_array($direction, [SORT_ASC, SORT_DESC])) {
                $direction == SORT_ASC && $direction = 'asc';
                $direction == SORT_DESC && $direction = 'desc';
            }
            parent::orderBy($column, $direction);
        }
        return $this;
    }

    /**
     * @param array $columns
     * @return mixed
     */
    public function one($columns = ['*'])
    {
        return $this->take(1)->get($columns)->first();
    }

    /**
     * @param array $columns
     * @return \Hyperf\Utils\Collection
     */
    public function all($columns = ['*'])
    {
        return $this->get($columns);
    }

    /**
     * @param string|array $table
     * @param \Closure|string $first
     * @param null|string $operator
     * @param null|string $second
     * @param string $type
     * @param bool $where
     * @return \Hyperf\Database\Query\Builder
     */
    public function join($table, $first, $operator = null, $second = null, $type = 'inner', $where = false)
    {
        return parent::join($this->getTableAlias($table), $first, $operator, $second, $type, $where); // TODO: Change the autogenerated stub
    }

    /**
     * @param string|array $table
     * @param \Closure|string $first
     * @param null $operator
     * @param null $second
     * @return \Hyperf\Database\Query\Builder
     */
    public function leftJoin($table, $first, $operator = null, $second = null)
    {
        return parent::leftJoin($this->getTableAlias($table), $first, $operator, $second); // TODO: Change the autogenerated stub
    }

    /**
     * @param string|array $table
     * @param \Closure|string $first
     * @param null $operator
     * @param null $second
     * @return \Hyperf\Database\Query\Builder
     */
    public function rightJoin($table, $first, $operator = null, $second = null)
    {
        return parent::rightJoin($this->getTableAlias($table), $first, $operator, $second); // TODO: Change the autogenerated stub
    }

    /**
     * @param string $table
     * @param null $first
     * @param null $operator
     * @param null $second
     * @return \Hyperf\Database\Query\Builder
     */
    public function crossJoin($table, $first = null, $operator = null, $second = null)
    {
        return parent::crossJoin($this->getTableAlias($table), $first, $operator, $second); // TODO: Change the autogenerated stub
    }

    /**
     * @param $table
     * @return mixed|string
     */
    private function getTableAlias($table)
    {
        if (is_array($table)) {
            $alias = array_key_first($table);
            $table = current($table);
            $table = is_string($alias) ? "$table as $alias" : $table;
        }
        return $table;
    }

    /**
     * 对函数的字段格式化
     * @param array|string $columns
     * @return \Hyperf\Database\Query\Builder
     */
    public function select($columns = ['*'])
    {
        if (is_array($columns)) {
            $filterColumns = [];
            foreach ($columns as $key => $column) {
                if (preg_match('/(?<=\()[^\)]+/', $column, $arr)) {//函数
                    $columnRaw = $this->columnize($column);
                    if (is_string($key) && !is_numeric($key)) {
                        $column = "$columnRaw as $key";
                    }
                    $this->selectRaw($column);
                } else {
                    if (is_string($key) && !is_numeric($key)) {
                        $column = "$column as $key";
                    }
                    $filterColumns[] = $column;
                }
            }
            return parent::addSelect($filterColumns); // TODO: Change the autogenerated stub
        } else {
            if (preg_match('/(?<=\()[^\)]+/', $columns, $arr)) {
                $columnRaw = $this->columnize($columns);
                $this->selectRaw($columnRaw);
                return $this;
            }
            return parent::addSelect($columns);
        }
    }

    /**
     * 格式化带函数的字段
     * @param $column
     * @return mixed
     */
    protected function columnize($column)
    {
        preg_match_all('/(?<=\()[^\)]+/', $column, $columns);
        $columns = current($columns);
        $data = [];
        foreach ($columns as $item) {
            $data[] = $this->grammar->columnize([$item]);
        }
        return str_replace($columns, $data, $column);
    }

    /**
     * 获取表前缀
     * @return array|bool|false|mixed|string|void
     */
    public function getTablePrefix()
    {
        return $this->grammar->getTablePrefix();
    }

    /**
     * 过滤查询条件
     * @param array $array
     * @return bool
     */
    protected function isFilter(array $array)
    {
        $len = count($array);
        if (($len == 2 && !$this->isEmpty($array[1])) || ($len >= 3 && !$this->isEmpty($array[2]))) {
            return true;
        }
        return false;
    }

    /**
     * @param $value
     * @return bool
     */
    protected function isEmpty($value)
    {
        return $value === '' || $value === [] || $value === null || is_string($value) && trim($value) === '';
    }

}