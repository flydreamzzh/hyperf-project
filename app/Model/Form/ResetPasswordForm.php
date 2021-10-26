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

/**
 * Password reset form.
 * @property string $password
 */
class ResetPasswordForm extends BaseModel
{
    public $fillable = ['password'];

    /**
     * @var User
     */
    private $_user;

    /**
     * Creates a form model given a token.
     *
     * @param string $token
     * @param array $config name-value pairs that will be used to initialize the object properties
     * @throws \Exception if token is empty or not valid
     */
    public function __construct(string $token, $config = [])
    {
        if (empty($token) || ! is_string($token)) {
            throw new \Exception('Password reset token cannot be blank.');
        }
        $this->_user = User::findByPasswordResetToken($token);
        if (! $this->_user) {
            throw new \Exception('Wrong password reset token.');
        }
        parent::__construct($config);
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            ['password', 'required'],
            ['password', 'regex:/(?=.*[A-Z])(?=.*[a-z])(?=.*[0-9])[A-Za-z0-9]{6,20}/', 'message' => 20003],
        ];
    }

    /**
     * @throws \Exception
     * @return bool
     */
    public function resetPassword(): bool
    {
        $user = $this->_user;
        $user->setPassword($this->password);
        $user->removePasswordResetToken();
        $user->pass_status = User::PASS_STATUS_ACTIVE;

        return $user->saveNoValidate();
    }
}
