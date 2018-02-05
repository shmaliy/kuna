<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%user}}".
 *
 * @property int $id
 * @property string $email
 * @property string $password
 * @property string $ethWallet
 * @property string $username
 * @property int $status
 * @property int $approved
 * @property string $firstname
 * @property string $lastname
 * @property int $role
 * @property string $lastLogin
 * @property string $created
 * @property string $updated
 */
class User extends \yii\db\ActiveRecord  implements \yii\web\IdentityInterface
{

    const NO = 0;
    const YES = 1;
	public $authKey;
	public $accessToken;
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%user}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['email', 'firstname', 'lastname', 'username'], 'required'],
            [['role'], 'integer'],
            [['ethWallet'], 'unique'],
            [['status', 'approved'], 'integer', 'min' => self::NO, 'max' => self::YES],
            [['lastLogin', 'created', 'updated'], 'safe'],
            [['email', 'username'], 'string', 'max' => 50],
            [['password', 'firstname', 'lastname', 'ethWallet'], 'string', 'max' => 32],
	        [['email'], 'unique', 'targetAttribute' => 'email'],
	        [['username'], 'unique', 'targetAttribute' => 'username'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'email' => 'Email',
            'password' => 'Password',
            'status' => 'Status',
            'approved' => 'Approved',
            'firstname' => 'Firstname',
            'lastname' => 'Lastname',
            'role' => 'Role',
            'lastLogin' => 'Last Login',
            'created' => 'Created',
            'updated' => 'Updated',
        ];
    }

	/**
	 * @inheritdoc
	 */
	public static function findIdentity($id)
	{
		return self::findOne($id);
	}


	/**
	 * @inheritdoc
	 */
	public static function findIdentityByAccessToken($token, $type = null)
	{
		foreach (self::$users as $user) {
			if ($user['accessToken'] === $token) {
				return new static($user);
			}
		}

		return null;
	}

	/**
	 * Finds user by username
	 *
	 * @param string $username
	 * @return static|null
	 */
	public static function findByUsername($username)
	{
		return self::findOne(['email' => $username]);

	}

	/**
	 * @inheritdoc
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * @inheritdoc
	 */
	public function getAuthKey()
	{
		return $this->authKey;
	}

	/**
	 * @inheritdoc
	 */
	public function validateAuthKey($authKey)
	{
		return $this->authKey === $authKey;
	}

	public static function hashPassword($password) {
		return md5(SECURITY_PASS_SALT . $password);
	}

	/**
	 * Validates password
	 *
	 * @param string $password password to validate
	 * @return bool if password provided is valid for current user
	 */
	public function validatePassword($password)
	{
		return $this->password === self::hashPassword($password);
	}

    /**
     * @inheritdoc
     * @return \app\models\query\UserQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \app\models\query\UserQuery(get_called_class());
    }

    public function getUserView($username)
    {
    	$query = self::find()
		    ->where('username = :username and status = 1', [':username' => $username]);
		$query->joinWith('userRole');
    	return $query->one();

    }
    
    public static function login($email, $password)
    {
        $user = self::findOne([
            'email' => $email,
            'password' => self::hashPassword($password)
        ]);
        
        if (is_null($user)) return false;
        
        Yii::$app->user->login($user, 3600*24*30);
        return true;
        
    }

}
