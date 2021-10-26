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
namespace App\Core;

use App\Core\Components\Identity;
use App\Core\Traits\HyStaticInstance;
use App\Core\Traits\ResultTrait;
use Hyperf\Utils\Context;

/**
 * BaseService
 * 服务基类.
 */
class BaseService
{
    use HyStaticInstance;
    use ResultTrait;

    /**
     * @return null|Interfaces\IdentityInterface
     */
    protected function getIdentity(): ?Interfaces\IdentityInterface
    {
        return Context::get(Identity::class)->getIdentity();
    }

    /**
     * @return null|int|string
     */
    protected function getIdentityId()
    {
        $identity = $this->getIdentity();
        return $identity ? $identity->getId() : null;
    }
}
