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
namespace App\Model;

use App\Core\Components\Security;

/**
 * @property int $id
 * @property int $user_id
 * @property string $access_token
 * @property string $ip
 * @property int $expired
 * @property int $status
 */
class UsersAccessed extends Model
{
    /** 默认过期时间 */
    const DEFAULT_EXPIRED = 7200;

    /** 状态为正在登录 */
    const STATUS_LOGIN = 0;

    /** 状态为已经注销 */
    const STATUS_LOGOUT = 1;

    public $operators = false;

    public $timestamps = false;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'users_accessed';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['user_id', 'access_token', 'ip', 'expired', 'status'];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = ['id' => 'integer', 'user_id' => 'integer', 'expired' => 'integer', 'status' => 'integer'];

    /**
     * 验证token.
     * @param string $token
     * @param string $ip
     * @return bool
     */
    public static function validateAccessToken(string $token, string $ip): bool
    {
        if ($accessed = self::findOne(['access_token' => $token, 'ip' => $ip, 'status' => static::STATUS_LOGIN])) {
            return $accessed->expired >= time();
        }
        return false;
    }

    /**
     * 更新token的过期时间.
     * @param string $token
     * @param string $ip
     * @return bool|null
     */
    public static function renewAccessToken(string $token, string $ip): ?bool
    {
        if ($accessed = self::findOne(['access_token' => $token, 'ip' => $ip, 'status' => static::STATUS_LOGIN])) {
            $params = config('user');
            $expired = isset($params['user.accessTokenExpire']) ? $params['user.accessTokenExpire'] : static::DEFAULT_EXPIRED;
            $accessed->expired = time() + $expired;
            return $accessed->saveNoValidate();
        }
        return null;
    }

    /**
     * 生成登录的access_token.
     * @param int|string $user_id
     * @param string $ip
     * @return bool|UsersAccessed
     */
    public static function doLogin($user_id, string $ip)
    {
        $accessed = UsersAccessed::firstOrNew(['user_id' => $user_id, 'ip' => $ip]);
        $params = config('user');
        $expired = isset($params['user.accessTokenExpire']) ? $params['user.accessTokenExpire'] : static::DEFAULT_EXPIRED;
        $accessed->user_id = $user_id;
        $accessed->expired = time() + $expired;
        $accessed->status = self::STATUS_LOGIN;
        $accessed->generateAccessToken();
        if ($accessed->save()) {
            return $accessed;
        }
        return false;
    }

    /**
     * 注销token.
     * @param string $token
     * @param string $ip
     * @return bool
     */
    public static function doLogout($token, $ip)
    {
        if ($accessed = self::findOne(['access_token' => $token, 'ip' => $ip])) {
            $accessed->status = static::STATUS_LOGOUT;
            return $accessed->save();
        }
        return true;
    }

    /**
     * 生成随机的token并加上时间戳
     * Generated random accessToken with timestamp.
     */
    public function generateAccessToken()
    {
        $this->access_token = Security::instance()->generateRandomString() . '-' . time();
    }
}
