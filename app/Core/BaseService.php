<?php


namespace App\Core;

use App\Core\Components\Identity;
use App\Core\Traits\HyStaticInstance;
use App\Core\Traits\ResultTrait;
use Hyperf\Utils\Context;

/**
 * BaseService
 * 服务基类
 * @package App\Core\Services
 */
class BaseService
{
    use HyStaticInstance;
    use ResultTrait;

    /**
     * @return Interfaces\IdentityInterface|null
     */
    protected function getIdentity()
    {
        return Context::get(Identity::class)->getIdentity();
    }

    /**
     * @return int|string|null
     */
    protected function getIdentityId()
    {
        $identity = $this->getIdentity();
        $id = $identity ? $identity->getId() : null;
        return $id;
    }
}