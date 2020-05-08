<?php

namespace App\Model\Form;



use App\Core\BaseModel;
use App\Model\User;

/**
 * Password reset form
 * @property string $password
 */
class ResetPasswordForm extends BaseModel
{
    /**
     * @var User
     */
    private $_user;

    public $fillable = ['password'];

    /**
     * Creates a form model given a token.
     *
     * @param string $token
     * @param array $config name-value pairs that will be used to initialize the object properties
     * @throws \Exception if token is empty or not valid
     */
    public function __construct($token, $config = [])
    {
        if (empty($token) || !is_string($token)) {
            throw new \Exception('Password reset token cannot be blank.');
        }
        $this->_user = User::findByPasswordResetToken($token);
        if (!$this->_user) {
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
     * @return bool
     * @throws \Exception
     */
    public function resetPassword()
    {
        $user = $this->_user;
        $user->setPassword($this->password);
        $user->removePasswordResetToken();
        $user->pass_status = User::PASS_STATUS_ACTIVE;

        return $user->saveNoValidate();
    }
}
