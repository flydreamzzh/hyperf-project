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
namespace App\Model\Form;

use App\Core\BaseModel;
use App\Model\User;
use App\Model\UsersAccessed;
use Hyperf\Utils\Context;
use Hyperf\Validation\Validator;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class LoginForm.
 * @property string $username 账号
 * @property string $password 密码
 */
class LoginForm extends BaseModel
{
    /**
     * Token.
     * @var string
     */
    public $access_token;

    protected $fillable = ['username', 'password'];

    /**
     * @var User
     */
    private $_user;

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['username', 'password'], 'required', 'message' => 20002],
            ['password', 'method:validatePassword', 'message' => 20001],
        ];
    }

    /**
     * @return array
     */
    public function attributeLabels(): array
    {
        return [
            'username' => '账号',
            'password' => '密码',
        ];
    }

    /**
     * @param $attribute
     * @param Validator $validator
     * @throws \Exception
     * @return bool
     */
    public function validatePassword($attribute, $validator): bool
    {
        $user = $this->getUser();
        if (! $user || ! $user->validatePassword($this->password)) {
            return false;
        }
        return true;
    }

    /**
     * Logs in a user using the provided username and password.
     *
     * @return bool whether the user is logged in successfully
     */
    public function login()
    {
        if ($this->validate()) {
            $user = $this->getUser();
            if ($user->pass_status != User::PASS_STATUS_ACTIVE) {
                $this->addError('username', 10004);
                return false;
            }
            $request = Context::get(ServerRequestInterface::class);
            $params = $request->getServerParams();
            $ip = isset($params['remote_addr']) ? $params['remote_addr'] : '';
            if ($ip && $accessed = UsersAccessed::doLogin($user->id, $ip)) {
                $this->access_token = $accessed->access_token;
                return true;
            }
            $this->addError('username', 10003);
        }

        return false;
    }

    /**
     * 注销用户登录状态
     * @return bool
     */
    public static function logout()
    {
        $params = config('user');
        $headerName = isset($params['accessTokenHeader']) ? $params['accessTokenHeader'] : 'X-Api-Key';
        $request = Context::get(ServerRequestInterface::class);
        $token = $request->getHeaderLine($headerName);
        $params = $request->getServerParams();
        $ip = isset($params['remote_addr']) ? $params['remote_addr'] : '';
        return UsersAccessed::doLogout($token, $ip);
    }

    /**
     * Finds user by [[username]].
     *
     * @return null|User
     */
    public function getUser()
    {
        if ($this->_user === null) {
            $this->_user = User::findByUsername($this->username);
        }

        return $this->_user;
    }
}
