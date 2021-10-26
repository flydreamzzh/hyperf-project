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
use App\Core\Interfaces\IdentityInterface;

/**
 * @property int $id
 * @property string $username 昵称
 * @property string $nickname 昵称
 * @property string $password_hash hash密码
 * @property string $password_reset_token 密码重置Token
 * @property string $email 邮箱
 * @property int $status 状态
 * @property int $pass_status 密码状态
 * @property string $remark 简介
 * @property string $created_by 创建人
 * @property \Carbon\Carbon $created_at 创建时间
 * @property string $updated_by 修改人
 * @property \Carbon\Carbon $updated_at 更新时间
 */
class User extends Model implements IdentityInterface
{
    const STATUS_DELETED = 0;

    const STATUS_ACTIVE = 10;

    /** @var string 密码重置默认值 */
    const RESET_PASSWORD = 'Abc123';

    /** @var int 密码状态 */
    const PASS_STATUS_ACTIVE = 0; //正常

    const PASS_STATUS_RESET = 1; //密码重置状态

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'users';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['username', 'nickname', 'password_hash', 'password_reset_token', 'email', 'status', 'pass_status', 'remark'];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = ['id' => 'integer', 'status' => 'integer', 'pass_status' => 'integer', 'created_at' => 'datetime', 'updated_at' => 'datetime'];

    /**
     * 字段默认值
     * @var array
     */
    protected $attributes = [
        'status' => self::STATUS_ACTIVE,
    ];

    public function attributeLabels(): array
    {
        return [
            'username' => '账号',
        ];
    }

    /**
     * 注册用户.
     * @throws \Exception
     * @return bool
     */
    public function signUp(): bool
    {
        $this->setPassword(self::RESET_PASSWORD);
        $this->pass_status = self::PASS_STATUS_RESET;
        return $this->save();
    }

    /**
     * Returns an ID that can uniquely identify a user identity.
     * @return int|string an ID that uniquely identifies a user identity
     */
    public function getId()
    {
        return $this->getPrimaryKey();
    }

    /**
     * @return int|string
     */
    public function getUserName()
    {
        return $this->username;
    }

    public static function findIdentity($id)
    {
        return static::findOne(['id' => $id, 'status' => self::STATUS_ACTIVE]);
    }

    /**
     * {@inheritdoc}
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        return static::find()->select('user.*')
            ->alias('user')
            ->where(['accessed.access_token' => $token, 'accessed.status' => UsersAccessed::STATUS_LOGIN, 'user.status' => self::STATUS_ACTIVE])
            ->leftJoin(['accessed' => UsersAccessed::tableName()], 'accessed.user_id', '=', 'user.id')
            ->first();
    }

    /**
     * Finds user by username.
     *
     * @param string $username
     * @return null|static
     */
    public static function findByUsername(string $username): ?User
    {
        return static::findOne(['username' => $username, 'status' => self::STATUS_ACTIVE]);
    }

    /**
     * Finds user by password reset token.
     *
     * @param string $token password reset token
     * @return null|static
     */
    public static function findByPasswordResetToken(string $token): ?User
    {
        if (! static::isPasswordResetTokenValid($token)) {
            return null;
        }

        return static::findOne([
            'password_reset_token' => $token,
            'status' => self::STATUS_ACTIVE,
        ]);
    }

    /**
     * Finds out if password reset token is valid.
     *
     * @param string $token password reset token
     */
    public static function isPasswordResetTokenValid(string $token): bool
    {
        if (empty($token)) {
            return false;
        }

        $timestamp = (int) substr($token, strrpos($token, '_') + 1);
        $expire = config('user.passwordResetTokenExpire');
        return $timestamp + $expire >= time();
    }

    /**
     * Validates password.
     *
     * @param string $password password to validate
     * @throws \Exception
     * @return bool if password provided is valid for current user
     */
    public function validatePassword(string $password): bool
    {
        return Security::instance()->validatePassword($password, $this->password_hash);
    }

    /**
     * Generates password hash from password and sets it to the model.
     *
     * @param string $password
     * @throws \Exception
     */
    public function setPassword(string $password)
    {
        $this->password_hash = Security::instance()->generatePasswordHash($password);
    }

    /**
     * Generates new password reset token.
     */
    public function generatePasswordResetToken()
    {
        $this->password_reset_token = Security::instance()->generateRandomString() . '_' . time();
    }

    /**
     * Removes password reset token.
     */
    public function removePasswordResetToken()
    {
        $this->password_reset_token = null;
    }

    /**
     * 验证token是否过期|成功则更新过期时间
     * Validates if accessToken expired.
     * @param string $token
     * @param string $ip
     * @return bool
     */
    public static function validateAccessToken(string $token, string $ip): bool
    {
        if (! $token) {
            return false;
        }
        if ($bool = UsersAccessed::validateAccessToken($token, $ip)) {
            UsersAccessed::renewAccessToken($token, $ip);
        }
        return $bool;
    }

    protected function rules(): array
    {
        return [
            [['username', 'nickname', 'email'], 'required'],
            [['status', 'pass_status'], 'integer'],
            [['username', 'password_hash', 'password_reset_token', 'email'], 'max:255'],
            [['nickname', 'remark'], 'string', 'max:50'],
            [['created_by', 'updated_by'], 'max:64'],
            [['username'], 'unique_c'],
            [['email'], 'unique_c'],
            [['password_reset_token'], 'unique_c'],
        ];
    }
}
