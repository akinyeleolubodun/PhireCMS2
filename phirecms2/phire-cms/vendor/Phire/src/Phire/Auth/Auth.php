<?php
/**
 * @namespace
 */
namespace Phire\Auth;

use Pop\Auth as A;
use Phire\Table;

class Auth extends A\Auth
{

    /**
     * Constructor
     *
     * Instantiate the auth object
     *
     * @param int    $encryption
     * @param string $salt
     * @return \Phire\Auth\Auth
     */
    public function __construct($encryption = 0, $salt = null)
    {
        $adapter = new A\Adapter\Table('Phire\Table\Users', 'username', 'password', 'role_id');
        parent::__construct($adapter, $encryption, $salt);
    }

    /**
     * Config the auth object
     *
     * @param  \Phire\Table\UserTypes $type
     * @param  string                   $username
     * @return \Phire\Auth\Auth
     */
    public function config($type, $username = null)
    {
        // Set the password encryption and salt
        $this->setEncryption((int)$type->password_encryption);
        $this->setSalt($type->password_salt);

        // Set attempt limit
        $this->setAttemptLimit((int)$type->allowed_attempts);

        // Set allowed IPs
        if (!empty($type->ip_allowed)) {
            $allowed = explode(',', $type->ip_allowed);
            $this->setAllowedIps($allowed);
            $this->setAllowedSubnets($allowed);
        }

        // Set blocked IPs
        if (!empty($type->ip_blocked)) {
            $blocked = explode(',', $type->ip_blocked);
            $this->setBlockedIps($blocked);
            $this->setBlockedSubnets($blocked);
        }

        // Set failed attempts
        if (null !== $username) {
            $user = Table\Users::findBy(array('username' => $username));
            if (isset($user->id)) {
                $this->setAttempts((int)$user->failed_attempts);
            }
        }

        return $this;
    }

    /**
     * Get Auth result
     *
     * @param  \Phire\Table\UserTypes $type
     * @return string
     */
    public function getAuthResult($type)
    {
        $result = null;

        if (!$this->isValid()) {
            $result = $this->getResultMessage();
        } else {
            $user = $this->getUser();
            $session = Table\UserSessions::findBy(array('user_id' => $user['id']));
            if ((!$type->multiple_sessions) && (isset($session->id))) {
                $result = 'Multiple sessions are not allowed. Someone is already logged on from ' . $session->ip . '.';
            } else if ((!$type->mobile_access) && (\Pop\Web\Mobile::isMobileDevice())) {
                $result = 'Mobile access is not allowed.';
            } else if (!$user['verified']) {
                $result = 'The user is not verified.';
            }
        }

        return $result;
    }

}

