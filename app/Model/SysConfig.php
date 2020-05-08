<?php

declare (strict_types=1);
namespace App\Model;



use App\Model\Base\ConfigModel;

/**
 * @property string $name 名称
 * @property string $label 标题
 * @property string $tip 提示
 * @property string $type 类型
 * @property float $maxSize 文件大小
 * @property int $multiple 是否多选，1是
 * @property int $required 是否必填，1是
 * @property int $system 是否系统配置，1是
 * @property int $sort 排序
 * @property string $options 选项
 * @property string $group 
 */
class SysConfig extends ConfigModel
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'sys_config';

}