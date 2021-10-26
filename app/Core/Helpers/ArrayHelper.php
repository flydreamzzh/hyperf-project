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
namespace App\Core\Helpers;

use Hyperf\Utils\Arr;

class ArrayHelper extends Arr
{
    /**
     * Merges two or more arrays into one recursively.
     * If each array has an element with the same string key value, the latter
     * will overwrite the former (different from array_merge_recursive).
     * Recursive merging will be conducted if both arrays have an element of array
     * type and are having the same key.
     * For integer-keyed elements, the elements from the latter array will
     * be appended to the former array.
     * You can use [[UnsetArrayValue]] object to unset value from previous array or
     * [[ReplaceArrayValue]] to force replace former value instead of recursive merging.
     * @param array $a array to be merged to
     * @param array $b array to be merged from. You can specify additional
     *                 arrays via third argument, fourth argument etc.
     * @return array the merged array (the original arrays are not changed.)
     */
    public static function merge(array $a, array $b, bool $unique = true): array
    {
        $args = func_get_args();
        $res = array_shift($args);
        while (! empty($args)) {
            foreach (array_shift($args) as $k => $v) {
                if (is_int($k)) {
                    if (array_key_exists($k, $res)) {
                        $res[] = $v;
                    } else {
                        $res[$k] = $v;
                    }
                } elseif (is_array($v) && isset($res[$k]) && is_array($res[$k])) {
                    $res[$k] = static::merge($res[$k], $v);
                } else {
                    $res[$k] = $v;
                }
            }
        }

        return $res;
    }

    /**
     * Retrieves the value of an array element or object property with the given key or property name.
     * If the key does not exist in the array, the default value will be returned instead.
     * Not used when getting value from an object.
     *
     * The key may be specified in a dot format to retrieve the value of a sub-array or the property
     * of an embedded object. In particular, if the key is `x.y.z`, then the returned value would
     * be `$array['x']['y']['z']` or `$array->x->y->z` (if `$array` is an object). If `$array['x']`
     * or `$array->x` is neither an array nor an object, the default value will be returned.
     * Note that if the array already has an element `x.y.z`, then its value will be returned
     * instead of going through the sub-arrays. So it is better to be done specifying an array of key names
     * like `['x', 'y', 'z']`.
     *
     * Below are some usage examples,
     *
     * ```php
     * // working with array
     * $username = \yii\helpers\ArrayHelper::getValue($_POST, 'username');
     * // working with object
     * $username = \yii\helpers\ArrayHelper::getValue($user, 'username');
     * // working with anonymous function
     * $fullName = \yii\helpers\ArrayHelper::getValue($user, function ($user, $defaultValue) {
     *     return $user->firstName . ' ' . $user->lastName;
     * });
     * // using dot format to retrieve the property of embedded object
     * $street = \yii\helpers\ArrayHelper::getValue($users, 'address.street');
     * // using an array of keys to retrieve the value
     * $value = \yii\helpers\ArrayHelper::getValue($versions, ['1.0', 'date']);
     * ```
     *
     * @param array|object $array array or object to extract value from
     * @param array|\Closure|string $key key name of the array element, an array of keys or property name of the object,
     *                                   or an anonymous function returning the value. The anonymous function signature should be:
     *                                   `function($array, $defaultValue)`.
     *                                   The possibility to pass an array of keys is available since version 2.0.4.
     * @param mixed $default the default value to be returned if the specified array key does not exist. Not used when
     *                       getting value from an object.
     * @return mixed the value of the element if found, default value otherwise
     */
    public static function getValue($array, $key, $default = null)
    {
        if ($key instanceof \Closure) {
            return $key($array, $default);
        }

        if (is_array($key)) {
            $lastKey = array_pop($key);
            foreach ($key as $keyPart) {
                $array = static::getValue($array, $keyPart);
            }
            $key = $lastKey;
        }

        if (is_array($array) && (isset($array[$key]) || array_key_exists($key, $array))) {
            return $array[$key];
        }

        if (($pos = strrpos($key, '.')) !== false) {
            $array = static::getValue($array, substr($key, 0, $pos), $default);
            $key = substr($key, $pos + 1);
        }

        if (is_object($array)) {
            // this is expected to fail if the property does not exist, or __get() is not implemented
            // it is not reliably possible to check whether a property is accessible beforehand
            return $array->{$key};
        }
        if (is_array($array)) {
            return (isset($array[$key]) || array_key_exists($key, $array)) ? $array[$key] : $default;
        }

        return $default;
    }

    /**
     * Returns the values of a specified column in an array.
     * The input array should be multidimensional or an array of objects.
     *
     * For example,
     *
     * ```php
     * $array = [
     *     ['id' => '123', 'data' => 'abc'],
     *     ['id' => '345', 'data' => 'def'],
     * ];
     * $result = ArrayHelper::getColumn($array, 'id');
     * // the result is: ['123', '345']
     *
     * // using anonymous function
     * $result = ArrayHelper::getColumn($array, function ($element) {
     *     return $element['id'];
     * });
     * ```
     *
     * @param array $array
     * @param \Closure|int|string $name
     * @param bool $keepKeys whether to maintain the array keys. If false, the resulting array
     *                       will be re-indexed with integers.
     * @return array the list of column values
     */
    public static function getColumn(array $array, $name, $keepKeys = true): array
    {
        $result = [];
        if ($keepKeys) {
            foreach ($array as $k => $element) {
                $result[$k] = static::getValue($element, $name);
            }
        } else {
            foreach ($array as $element) {
                $result[] = static::getValue($element, $name);
            }
        }

        return $result;
    }

    /**
     * ------------------------------------------
     * 把返回的数据集转换成Tree.
     * @param array $list 要转换的数据集
     * @param string $pk 主键
     * @param string $pid parent标记字段
     * @param string $child
     * @param int $root
     * @return array
     *               ------------------------------------------
     */
    public static function ListToTree(array $list, $pk = 'id', $pid = 'pid', $child = '_child', $root = 0): array
    {
        // 创建Tree
        $tree = [];
        if (is_array($list)) {
            // 创建基于主键的数组引用
            $refer = [];
            foreach ($list as $key => $data) {
                $refer[$data[$pk]] = &$list[$key];
            }
            foreach ($list as $key => $data) {
                // 判断是否存在parent
                $parentId = $data[$pid];
                if ($root == $parentId) {
                    $tree[] = &$list[$key];
                } else {
                    if (isset($refer[$parentId])) {
                        $parent = &$refer[$parentId];
                        $parent[$child][] = &$list[$key];
                    }
                }
            }
        }
        return $tree;
    }

    /**
     * ---------------------------------------------------
     * 将treeToList的树还原成列表.
     * @param array $tree 原来的树
     * @param string $child 孩子节点的键
     * @param string $order 排序显示的键，一般是主键 升序排列
     * @param array $list 过渡用的中间数组，
     * @return array 返回排过序的列表数组
     *               ---------------------------------------------------
     */
    public static function treeToList($tree, $child = '_child', $order = 'id', &$list = [])
    {
        if (is_array($tree)) {
            $refer = [];
            foreach ($tree as $key => $value) {
                $reffer = $value;
                if (isset($reffer[$child])) {
                    unset($reffer[$child]);
                    static::treeToList($value[$child], $child, $order, $list);
                }
                $list[] = $reffer;
            }
            $list = static::listSortBy($list, $order, $sortby = 'asc');
        }
        return $list;
    }

    /**
     * --------------------------------------------------
     * 对查询结果集进行排序.
     * @param array $list 查询结果
     * @param string $field 排序的字段名
     * @param string $sortby 排序类型 asc正向排序 desc逆向排序 nat自然排序
     * @return array|bool
     *                    --------------------------------------------------
     */
    public static function listSortBy($list, $field, $sortby = 'asc')
    {
        if (is_array($list)) {
            $refer = $resultSet = [];
            foreach ($list as $i => $data) {
                $refer[$i] = &$data[$field];
            }

            switch ($sortby) {
                case 'asc': // 正向排序
                    asort($refer);
                    break;
                case 'desc': // 逆向排序
                    arsort($refer);
                    break;
                case 'nat': // 自然排序
                    natcasesort($refer);
                    break;
            }
            foreach ($refer as $key => $val) {
                $resultSet[] = &$list[$key];
            }

            return $resultSet;
        }
        return false;
    }

    /**
     * ---------------------------------------
     * 递归方式将tree结构转化为 表单中select可使用的格式.
     * @param array $tree 树型结构的数组
     * @param string $title 将格式化的字段
     * @param int $level 当前循环的层次,从0开始
     * @return array
     *               ---------------------------------------
     */
    public static function formatTree(array $tree, $title = 'title', $level = 0): array
    {
        static $list;
        /* 按层级格式的字符串 */
        $tmp_str = str_repeat('　　', $level) . '└';
        $level == 0 && $tmp_str = '';

        foreach ($tree as $key => $value) {
            $value[$title] = $tmp_str . $value[$title];
            $arr = $value;
            if (isset($arr['_child'])) {
                unset($arr['_child']);
            }

            $list[] = $arr;
            if (array_key_exists('_child', $value)) {
                static::formatTree($value['_child'], $title, $level + 1);
            }
        }
        return $list ?: [];
    }

    /**
     * 将数组转换成字符串.
     * @param array|string $arr
     * @param bool $hasKey
     * @param string $split
     * @return string
     */
    public static function arrayToString($arr, $hasKey = false, $split = '<br>')
    {
        if (is_array($arr)) {
            $string = '';
            $i = 0;
            foreach ($arr as $key => $data) {
                if ($hasKey) {
                    $i > 0 && $string .= $split;
                    $string .= $key . '：' . self::arrayToString($data, $hasKey);
                } else {
                    $i > 0 && $string .= $split;
                    $string .= self::arrayToString($data, $hasKey);
                }
                ++$i;
            }
            return $string;
        }
        return $arr;
    }

    /**
     * PHP stdClass Object转array.
     * @param $array
     * @return array
     */
    public static function objectToArray($array): array
    {
        if (is_object($array)) {
            $array = (array) $array;
        }
        if (is_array($array)) {
            foreach ($array as $key => $value) {
                $array[$key] = self::objectToArray($value);
            }
        }
        return $array;
    }

    /**
     * [
     *      [
     *          'name' => 'ip',
     *          'value' => '192.168.1.2'
     *      ],
     *      ````````````
     * ].
     *
     * 转换成：
     * [
     *      'ip' => '192.168.1.2'
     * ]
     * @param $arr array 将form表单格式化成数组
     * @return array
     */
    public static function serializeArray(array $arr): array
    {
        $data = [];
        foreach ($arr as $item) {
            $data[$item['name']] = $item['value'];
        }
        return $data;
    }
}
