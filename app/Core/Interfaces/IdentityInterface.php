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
namespace App\Core\Interfaces;

/**
 * 摘自YII2
 * Interface IdentityInterface.
 */
interface IdentityInterface
{
    /**
     * Finds an identity by the given ID.
     * @param int|string $id the ID to be looked for
     * @return null|IdentityInterface|object the identity object that matches the given ID.
     *                                       Null should be returned if such an identity cannot be found
     *                                       or the identity is not in an active state (disabled, deleted, etc.)
     */
    public static function findIdentity($id);

    /**
     * Finds an identity by the given token.
     * @param mixed $token the token to be looked for
     * @param mixed $type the type of the token. The value of this parameter depends on the implementation.
     *                    For example, [[\yii\filters\auth\HttpBearerAuth]] will set this parameter to be `yii\filters\auth\HttpBearerAuth`.
     * @return null|IdentityInterface|object the identity object that matches the given token.
     *                                       Null should be returned if such an identity cannot be found
     *                                       or the identity is not in an active state (disabled, deleted, etc.)
     */
    public static function findIdentityByAccessToken($token, $type = null);

    /**
     * Returns an ID that can uniquely identify a user identity.
     * @return int|string an ID that uniquely identifies a user identity
     */
    public function getId();

    /**
     * Returns an username that can uniquely identify a user identity.
     * @return int|string an ID that uniquely identifies a user identity
     */
    public function getUserName();
}
