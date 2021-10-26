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
namespace App\Core\Traits;

/**
 * Trait IdeHelperTrait.
 *
 * @method static \Hyperf\Database\Model\Model|static make(array $attributes = [])
 * @method static $this|\Hyperf\Database\Model\Model create(array $attributes = [])
 * @method static $this|\Hyperf\Database\Model\Model forceCreate(array $attributes)
 * @method static \Hyperf\Database\Model\Model|static findOrNew($id, $columns = ['*'])
 * @method static \Hyperf\Database\Query\Builder where($column, $operator = null, $value = null, $boolean = 'and')
 * @method static \Hyperf\Database\Query\Builder whereIn($column, $values, $boolean = 'and', $not = false)
 * @method static \Hyperf\Database\Model\Collection|\Hyperf\Database\Model\Model|static|static[] findOrFail($id, $columns = ['*'])
 * @method static \Hyperf\Database\Model\Collection|static[] findMany($ids, $columns = ['*'])
 * @method static \Hyperf\Database\Model\Model|static first($columns = ['*'])
 * @method static \Hyperf\Database\Model\Model|static firstOrNew(array $attributes, array $values = [])
 * @method static \Hyperf\Database\Model\Model|static firstOrCreate(array $attributes, array $values = [])
 * @method static \Hyperf\Database\Model\Model|mixed|static firstOr($columns = ['*'], \Closure $callback = null)
 * @method static \Hyperf\Database\Model\Model|static updateOrCreate(array $attributes, array $values = [])
 * @method static \Hyperf\Database\Model\Model[]|static[] getModels($columns = ['*'])
 * @method static array eagerLoadRelations(array $models)
 * @method static \Hyperf\Database\Model\Relations\Relation getRelation($name)
 * @method static \Hyperf\Database\Model\Relations\Relation pluck($column, $key = null)
 * @method static string value($column)
 * @method static $this latest($column = null)
 */
trait IdeHelperTrait
{
}
