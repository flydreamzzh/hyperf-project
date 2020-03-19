<?php


namespace App\Core;

use Hyperf\DbConnection\Model\Model;
use App\Core\Traits\IdeHelperTrait;

/**
 * 模型基础类
 * Class BaseModel
 * @package App\base
 */
class BaseModel extends Model
{
    use IdeHelperTrait;

    /**
     * query()别名
     * @return \Hyperf\Database\Model\Builder
     */
    public static function find()
    {
        return self::query();
    }

    /**
     * 获取单条数据模型对象
     * @param $column
     * @param null $operator
     * @param null $value
     * @param string $boolean
     * @return \Hyperf\Database\Model\Builder|\Hyperf\Database\Model\Model|object|static|null
     */
    public static function findOne($column, $operator = null, $value = null, $boolean = 'and')
    {
        return self::query()->where(...func_get_args())->first();
    }

    /**
     * 获取多条数据模型对象
     * @param $column
     * @param null $operator
     * @param null $value
     * @param string $boolean
     * @return \Hyperf\Database\Model\Collection|static[]
     */
    public static function findAll($column, $operator = null, $value = null, $boolean = 'and')
    {
        return self::query()->where(...func_get_args())->get();
    }

}