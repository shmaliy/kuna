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
 *
 * @property UserRole $userRole
 * @property UserStatus $userStatus
 * @property UserAuthorFollowing[] $followers
 * @property UserAuthorFollowing[] $following
 * @property Post[] $posts
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
            [['role'], 'exist', 'targetClass' => UserRole::className(), 'targetAttribute' => 'id'],
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

	public function getUserStatus()
	{
		return self::hasOne(UserStatus::className(), ['id' => 'status']);
	}

	public function getUserRole()
	{
		return self::hasOne(UserRole::className(), ['id' => 'role']);
	}

	public function getFollowers()
	{
		return self::hasMany(UserAuthorFollowing::className(), ['authorId' => 'id'])
		           ->joinWith('follower fol');
	}

	public function getFollowing()
	{
		return self::hasMany(UserAuthorFollowing::className(), ['userId' => 'id'])
		           ->joinWith('user us');
	}

	public function getPosts()
	{
		return self::hasMany(Post::className(), ['userId' => 'id'])->onCondition('published = 1 and deleted = 0');
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

    public function getAuthors($me)
    {
    	return self::find()
		    ->joinWith('followers f')
		    ->joinWith('posts p')
		    ->where('{{%user}}.role = 2 and {{%user}}.status = 1')
		    ->orderBy('created desc')
		    ->all();
    }

    public function relationStatus($me)
    {
	    $row = UserAuthorFollowing::findOne([
	    	'authorId' => $this->id,
		    'userId' => $me->id,
		    'active' => 1
	    ]);

	    if (!is_null($row)) return 1;
	    return 0;
    }

    public function getAuthorUrl()
    {
    	return '/user/' . $this->username;
    }
}
