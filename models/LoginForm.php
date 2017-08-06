<?php
namespace app\models;

use Yii;
use yii\base\Model;

/**
 * LoginForm is the model behind the login form.
 *
 * @property User|null $user This property is read-only.
 *
 */
class LoginForm extends Model
{
    public $login;
    public $pwd;
    //public $rememberMe = true;

    private $_user = false;


    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            // login and pwd are both required
            [['login', 'pwd'], 'required'],
            // rememberMe must be a boolean value
           // ['rememberMe', 'boolean'],
            // pwd is validated by validatepwd()
            ['pwd', 'validatePwd'],
        ];
    }

	public function attributeLabels() {
        return ['login'=>'логин', 'pwd'=>'пароль'];
	}

    /**
     * Validates the pwd.
     * This method serves as the inline validation for pwd.
     *
     * @param string $attribute the attribute currently being validated
     * @param array $params the additional name-value pairs given in the rule
     */
    public function validatePwd($attribute, $params)
    {
        if (!$this->hasErrors()) {
            $user = $this->getUser();

            if (!$user || !$user->validatePwd($this->pwd))
                $this->addError($attribute, Traider::ERR_AUTH_DATA);
        }
    }

    /**
     * Logs in a user using the provided login and pwd.
     * @return bool whether the user is logged in successfully
     */
    public function login()
    {
        if ($this->validate()) {
            return Yii::$app->user->login($this->getUser(), 3600*24*180);
        }
		
        return false;
    }

    /**
     * Finds user by [[login]]
     *
     * @return User|null
     */
    public function getUser()
    {
        if ($this->_user === false) {
            $this->_user = Traider::findBylogin($this->login);
        }

        return $this->_user;
    }
}
