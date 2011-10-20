<?php

class Users extends MongoBase
{
    const KEY_ID             = "_id";
    const KEY_USERNAME       = "username";
    const KEY_PASSWORD       = "password";
    const KEY_EMAIL          = "email";
    const KEY_LAST_ACTIVE    = "lastActive";
    const KEY_KEYWORD        = "keyword";
    const KEY_STATUS         = "status";
    const KEY_WARN_LEVEL     = "warnLevel";

    const STATUS_ACTIVATED   = "activated";
    const STATUS_UNACTIVATED = "unactivated";
    const STATUS_DEACTIVATED = "deactivated";
    const STATUS_BANNED      = "banned";
    
    const ERROR_USERNAME_EXISTS  = 0;
    const ERROR_EMAIL_EXISTS     = 1;
    const ERROR_NO_SUCH_USER     = 2;
    const ERROR_BAD_CREDENTIALS  = 3;
    const ERROR_ACCOUNT_UNACTIVE = 4;
    const ERROR_ACCOUNT_DEACTIVE = 5;
    const ERROR_ACCOUNT_BANNED   = 6;

    private static $errors = array(
        self::ERROR_USERNAME_EXISTS  => "Selected username is already in use. Please choose another.",
        self::ERROR_EMAIL_EXISTS     => "That email address is already associated with an account. Please register with a unique email address",
        self::ERROR_NO_SUCH_USER     => "Invalid user/keword combination",
        self::ERROR_BAD_CREDENTIALS  => "Invalid user/password combination",
        self::ERROR_ACCOUNT_UNACTIVE => "The account you are trying to access has yet to be activated",
        self::ERROR_ACCOUNT_DEACTIVE => "The account you are trying to access has been deactivated. Would you like to reactivate it?",
        self::ERROR_ACCOUNT_BANNED   => "The account you are trying to access has been banned."
    );

    private $users;
    private $mongoConnection;
    private $lastError;

    public function __construct($mongoConnection)
    {
        $this->mongo = $mongoConnection;
        $this->users = $mongoConnection->users;
    }

    public function create($username, $password, $email)
    {
        // run the supplied password against the password strength test and
        // verify that it is secure enough, return false and set the error
        // message otherwise
        if (!$this->_passwordStrength($password))
        {
            // proper erro code should be set in the passwordStrength method
            // so we just return false here.
            return false;
        }

        // query the mongodb user collection to see if a user record exists
        // that has either the supplied username or email address associated
        // with it.
        $found = $this->users->findOne(
            array(
                '$or' => array(
                    array(self::KEY_USERNAME => $username),
                    array(self::KEY_EMAIL    => $email)
                ),
                array(
                    self::KEY_USERNAME => '1',
                    self::KEY_EMAIL    => '1'
                )
            ),
            array(
                self::KEY_USERNAME => 1,
                self::KEY_EMAIL    => 1
            )
        );

        // if we returned an entry this means that a user already exists with
        // that email address or that user name, so we examine the returned
        // user recourd to see which one it is and set the propper error message
        if ($found !== null)
        {
            if ($found[self::KEY_USERNAME] === $username)
            {
                return $this->setError(self::ERROR_USERNAME_EXISTS);
            }

            // if it wasn't the username that matched then it had to of been
            // the email address
            return $this->setError(self::ERROR_EMAIL_EXISTS);
        }

        // the bare minimum user validation has been passed so go
        // ahead and persist the user data to the DB.
        $this->users->insert(array(
            self::KEY_USERNAME   => $username,
            self::KEY_EMAIL      => $email,
            self::KEY_PASSWORD   => $this->_passwordHash($password),
            self::KEY_STATUS     => self::STATUS_UNACTIVATED,
            self::KEY_KEYWORD    => $this->_generateKeyword(),
            self::KEY_WARN_LEVEL => 0,
        ));

        // TODO: send activation email, preferably via an MQ

        return true;
    }

    public function getUserByUsername($username, $only = false)
    {
        $query = array(self::KEY_USERNAME => $username);
    
        if (is_array($only))
        {
            return $this->users->findOne($query, $only);
        }
        else
        {
            return $this->users->findOne($query);
        }
    }

    public function usernameExists($username)
    {
        // functional programming style for checking if the username exists
        return (
            $this->users->findOne(array(
                self::KEY_USERNAME => $username
            )) !== null
        );
    }

    public function performedActionByUsername($username)
    {
        return $this->_performedAction(
            array(
                self::KEY_USERNAME => $username
            )
        );
    }

    public function performedActionById($id)
    {
        //TODO: implement $this->_toMongoId() in base class
        return $this->_performedAction(
            array(
                self::KEY_ID => $this->_toMongoId($id)
            )
        );
    }

    public function activate($username, $keyword)
    {
        // we query the users mongodb collection for a user record that matches
        // the supplied username and keyword and has the status of unactivated
        // the reason why we don't get more granular is because we purposefully
        // avoid information leaking so this doesn't become a vector for prodding
        // user existence etc.
        $user = $this->users->findOne(
            array(
                self::KEY_USERNAME => $username,
                self::KEY_KEYWORD  => $keyword,
                self::KEY_STATS    => self::STATS_UNACTIVATED,
            ),
            array(
                self::KEY_ID => '1'
            )
        );

        if ($user === null) 
        {
            return $this->setError(self::ERROR_NO_SUCH_USER);
        }

        // set the users record to activated and remove the keyword field
        // from the users record
        $this->users->update(
            array(
                self::KEY_ID => $user[self::KEY_ID]
            ),
            array(
                '$set' => array(
                    self::KEY_STATUS => self::STATUS_ACTIVATED
                ),
                '$unset' => array(
                    self::KEY_KEYWORD => '1'
                )
            )
        );
        return true;
    }

    public function deactivate($username)
    {
        return $this->_setUserSatus($username, self::STATUS_DEACTIVATED);
    }

    public function authenticate($username, $password)
    {
        // query the users collection in mongodb for the username password
        // combination
        $user = $this->users->findOne(
            array(
                self::KEY_USERNAME => $username,
                self::KEY_PASSWORD => $this->_passwordHash($password)
            )
        );

        // if the results from the query failed to return a user record return
        // a false and set the proper error message
        if ($user === null) 
        { 
            return $this->setError(self::ERROR_BAD_CREDENTIALS);
        }

        // if the query returned a user record but the status is anything but
        // self::STATUS_ACTIVATED then return false and set the proper error
        if ($user[self::KEY_STATUS] !== self::STATUS_ACTIVATED)
        {
            if ($user[self::KEY_STATUS] === self::STATUS_UNACTIVATED)
            {
                return $this->setError(self::ERROR_ACCOUNT_UNACTIVE);
            }

            if ($user[self::KEY_STATUS] === self::STATUS_DEACTIVATED)
            {
                return $this->setError(self::ERROR_ACCOUNT_DEACTIVE);
            }

            if ($user[self::KEY_STATUS] === self::STATUS_BANNED)
            {
                return $this->setError(self::ERROR_ACCOUNT_BANNED);
            }
        }

        // all is well so return the user object
        return $user;
    }

    public function ban($username)
    {
        return $this->_setUserSatus($username, self::STATUS_BANNED);
    }

    // not top priority at the moment
    public function warn($username)
    {
        // TODO:
    }

    // not top priority at the moment
    public function lowerWarn($username)
    {
        // TODO:
    }

    public function getError()
    {
        return self::$errors[$this->lastError];
    }

    public function setError($error)
    {
        $this->lastError = $error;
        return false;
    }

    private function _performedAction($where)
    {
        $this->users->update(
            $where,
            array('$set' => time())
        );
    
        return true;
    }

    private function _setUserSatus($username, $status)
    {
        $this->users->update(
            array(
                self::KEY_USERNAME => $username
            ),
            array(
                '$set' => $status
            )
        );

        return true;
    }

    private function _generateKeyword()
    {
        // lol, retarded simple yet effective
        return sha1(rand());
    }

    private function _passwordHash($password)
    {
        // super simple but it's localized and we chan swap it out for
        // bcrypt later one
        return sha1($password);
    }

    private function _passwordStrength($password)
    {
        // TODO: nothing super important yet.
        return true;
    }
}