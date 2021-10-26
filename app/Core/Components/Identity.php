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

use App\Core\Interfaces\IdentityInterface;

/**
 * Class Identity.
 */
class Identity
{
    public $identityClass = 'App\Model\User';

    private $_identity;

    /**
     * @param string $token
     * @param null $type
     * @return null|IdentityInterface
     */
    public function loginByAccessToken(string $token, $type = null): ?IdentityInterface
    {
        /* @var $class IdentityInterface */
        $class = $this->identityClass;
        $identity = $class::findIdentityByAccessToken($token, $type);
        if ($identity && $this->login($identity)) {
            return $identity;
        }

        return null;
    }

    /**
     * @param IdentityInterface $identity
     * @return bool
     */
    public function login(IdentityInterface $identity): bool
    {
        $this->setIdentity($identity);
        return ! $this->getIsGuest();
    }

    /**
     * Sets the user identity object.
     *
     * Note that this method does not deal with session or cookie. You should usually use [[switchIdentity()]]
     * to change the identity of the current user.
     *
     * @param null|IdentityInterface $identity the identity object associated with the currently logged user.
     *                                         If null, it means the current user will be a guest without any associated identity.
     */
    public function setIdentity(?IdentityInterface $identity)
    {
        if ($identity instanceof IdentityInterface) {
            $this->_identity = $identity;
        } elseif ($identity === null) {
            $this->_identity = null;
        } else {
            throw new \InvalidArgumentException('The identity object must implement IdentityInterface.');
        }
    }

    /**
     * @return null|IdentityInterface
     */
    public function getIdentity(): ?IdentityInterface
    {
        return $this->_identity;
    }

    /**
     * Returns a value indicating whether the user is a guest (not authenticated).
     * @return bool whether the current user is a guest
     * @see getIdentity()
     */
    public function getIsGuest(): bool
    {
        return $this->getIdentity() === null;
    }
}
