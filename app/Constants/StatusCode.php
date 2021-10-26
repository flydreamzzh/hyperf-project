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
namespace App\Constants;

use App\Core\Helpers\ArrayHelper;
use Hyperf\Utils\Traits\StaticInstance;

class StatusCode
{
    use StaticInstance;

    /** @var int 成功码 */
    const SUCCESS = 0;

    /** @var int 异常码 */
    const ERR_EXCEPTION = -1;

    /** @var int 未知错误 */
    const ERR_OTHER_EXCEPTION = 10001;

    /**
     * 异常吗文本信息.
     * @var array
     */
    private $message = [];

    /**
     * @var array 错误信息存放路径别名集合
     */
    private $_errorAlias = [
        'app/Constants/errors/*\.php',
    ];

    /**
     * Initializes.
     */
    public function __construct()
    {
        foreach ($this->_errorAlias as $relatePath) {
            $aliasPath = BASE_PATH . '/' . $relatePath;
            foreach (glob($aliasPath) as $errorPath) {
                $this->message = ArrayHelper::merge($this->message, require $errorPath);
            }
        }
    }

    /**
     * 返回错误代码对应的文本信息.
     * @param int $code
     * @return mixed
     */
    public function getMessage($code)
    {
        return isset($this->message[$code]) ? $this->message[$code] : $this->message[self::ERR_OTHER_EXCEPTION];
    }
}
