<?php
/**
 * @namespace
 */
namespace Phire\Model;

use Pop\Auth\Role;
use Pop\Filter\String;
use Pop\Log;
use Pop\Mail\Mail;
use Pop\Web\Session;
use Phire\Table;

class User extends \Pop\Mvc\Model
{

    /**
     * Instantiate the model object.
     *
     * @param  mixed  $data
     * @param  string $name
     * @return self
     */
    public function __construct($data = null, $name = null)
    {
        parent::__construct($data, $name);

        $sess = \Pop\Web\Session::getInstance();

        if (isset($sess->user)) {
            $this->data['user'] = $sess->user;
            $this->data['role'] = Table\Roles::getRole($sess->user->role_id);
            $this->data['globalAccess'] = $sess->user->global_access;
        }
    }

    /**
     * Login method
     *
     * @param string               $username
     * @param \Phire\Table\Types $type
     * @param boolean              $success
     * @return void
     */
    public function login($username, $type, $success = true)
    {
        $user = Table\Users::findBy(array('username' => $username));
        $sess = Session::getInstance();

        // If login success
        if (($success) && isset($user->id)) {
            // Create and save new session database entry
            if ($type->track_sessions) {
                $session = new Table\Sessions(array(
                    'user_id' => $user->id,
                    'ip'      => $_SERVER['REMOTE_ADDR'],
                    'ua'      => $_SERVER['HTTP_USER_AGENT'],
                    'start'   => date('Y-m-d H:i:s'),
                    'last'    => date('Y-m-d H:i:s')
                ));
                $session->save();
                $sessionId = $session->id;
            } else {
                $sessionId = null;
            }

            $type = Table\Types::findById($user->type_id);
            $role = Table\Roles::findById($user->role_id);

            // Create new session object
            $sess->user = new \ArrayObject(
                array(
                    'id'            => $user->id,
                    'type_id'       => $user->type_id,
                    'type'          => $type->type,
                    'global_access' => $type->global_access,
                    'role_id'       => (isset($role->id)) ? $role->id : 0,
                    'role'          => (isset($role->id)) ? $role->name : null,
                    'username'      => $username,
                    'email'         => $user->email,
                    'first_name'    => $user->fname,
                    'last_name'     => $user->lname,
                    'last_login'    => $user->last_login,
                    'last_ua'	    => $user->last_ua,
                    'last_ip'       => $user->last_ip,
                    'sess_id'       => $sessionId
                ),
                \ArrayObject::ARRAY_AS_PROPS
            );

            // Store timestamp and login data
            $user->last_login = date('Y-m-d H:i:s');
            $user->last_ua = $_SERVER['HTTP_USER_AGENT'];
            $user->last_ip = $_SERVER['REMOTE_ADDR'];
            $user->failed_attempts = 0;
            $user->save();

            // If set, log the login
            if ($type->log_emails != '') {
                $this->log($type, $user);
            }
        // Else, log failed attempt
        } else {
            if (isset($user->id)) {
                $user->failed_attempts++;
                $user->save();
            }
        }
    }

    /**
     * Get all users method
     *
     * @return void
     */
    public function getAll()
    {
        $sql = Table\Users::getSql();
        $sql->select(array(
            DB_PREFIX . 'users.id',
            DB_PREFIX . 'users.type_id',
            DB_PREFIX . 'users.role_id',
            DB_PREFIX . 'users.first_name',
            DB_PREFIX . 'users.last_name',
            DB_PREFIX . 'users.email',
            DB_PREFIX . 'users.username',
            DB_PREFIX . 'users.last_login',
            DB_PREFIX . 'users.last_ip',
            DB_PREFIX . 'types.type',
            DB_PREFIX . 'roles.name'
        ))->join(DB_PREFIX . 'types', array('type_id', 'id'), 'LEFT JOIN')
          ->join(DB_PREFIX . 'roles', array('role_id', 'id'), 'LEFT JOIN')
          ->orderBy(DB_PREFIX . 'users.id', 'ASC');

        $users = Table\Users::execute($sql->render(true));
        $this->data['users'] = $users->rows;
    }

    /**
     * Get user by ID method
     *
     * @param  int $id
     * @return void
     */
    public function getById($id)
    {
        $user = Table\Users::findById($id);
        if (isset($user->id)) {
            $userValues = $user->getValues();
            $userValues['email1'] = $userValues['email'];

            if ((null !== $userValues['birth_date']) && ($userValues['birth_date'] != '0000-00-00')) {
                $birthDateAry = explode('-', $userValues['birth_date']);
                $userValues['birth_date_month'] = $birthDateAry[1];
                $userValues['birth_date_day'] = $birthDateAry[2];
                $userValues['birth_date_year'] = $birthDateAry[0];
            }

            $userValues['updates'] = (int)$userValues['updates'];
            $userValues['verified'] = (int)$userValues['verified'];
            $this->data = array_merge($this->data, $userValues);
        }
    }

    /**
     * Save user
     *
     * @param \Pop\Form\Form $form
     * @return void
     */
    public function save(\Pop\Form\Form $form)
    {
        $form->filter('html_entity_decode', array(ENT_QUOTES, 'UTF-8'));
        $fields = $form->getFields();

        $password = $fields['password1'];

        $type = Table\Types::findById($fields['type_id']);
        if (isset($type->id)) {
            switch ($type->password_encryption) {
                case 3:
                    $password = crypt($fields['password1'], $type->password_salt);
                    break;
                case 2:
                    $password = sha1($fields['password1']);
                    break;
                case 1:
                    $password = md5($fields['password1']);
                    break;
                case 0:
                    $password = $fields['password1'];
                    break;
            }
        }

        $fields['username'] = (isset($fields['username'])) ? $fields['username'] : $fields['email1'];
        $fields['password'] = $password;
        $fields['email'] = $fields['email1'];

        if (isset($fields['role_id'])) {
            $fields['role_id'] = ($fields['role_id'] == 0) ? null : $fields['role_id'];
        } else {
            if ($type->approval) {
                $fields['role_id'] = null;
            } else {
                $roles = Table\Roles::findBy(array('type_id' => $fields['type_id']), 'value ASC');
                $fields['role_id'] = (isset($roles->rows[0])) ? $roles->rows[0]->id : null;
            }
        }

        if (!isset($fields['verified'])) {
            $fields['verified'] = ($type->verification) ? 0 : 1;
        }

        if ($fields['country'] == '--') {
            $fields['country'] = null;
        }

        if (($fields['birth_date_month'] != '--') && ($fields['birth_date_day'] != '--') && ($fields['birth_date_year'] != '----')) {
            $fields['birth_date'] = $fields['birth_date_year'] . '-' . $fields['birth_date_month'] . '-' . $fields['birth_date_day'];
        } else {
            $fields['birth_date'] = null;
        }

        if ($fields['gender'] == '--') {
            $fields['gender'] = null;
        }

        $fields['failed_attempts'] = 0;

        unset($fields['password1']);
        unset($fields['password2']);
        unset($fields['email1']);
        unset($fields['email2']);
        unset($fields['birth_date_month']);
        unset($fields['birth_date_day']);
        unset($fields['birth_date_year']);
        unset($fields['id']);
        unset($fields['submit']);

        $user = new Table\Users($fields);
        $user->save();

        if (($type->verification) && !($user->verified)) {
            $this->sendVerification($user, $type);
        }
    }

    /**
     * Update user
     *
     * @param \Pop\Form\Form $form
     * @return void
     */
    public function update(\Pop\Form\Form $form)
    {
        $form->filter('html_entity_decode', array(ENT_QUOTES, 'UTF-8'));
        $fields = $form->getFields();

        $user = Table\Users::findById($fields['id']);

        if ($fields['password1'] != '') {
            $password = $fields['password1'];

            $type = Table\Types::findById($fields['type_id']);
            if (isset($type->id)) {
                switch ($type->password_encryption) {
                    case 3:
                        $password = crypt($fields['password1'], $type->password_salt);
                        break;
                    case 2:
                        $password = sha1($fields['password1']);
                        break;
                    case 1:
                        $password = md5($fields['password1']);
                        break;
                    case 0:
                        $password = $fields['password1'];
                        break;
                }
            }

            $user->password = $password;
        }

        if ($fields['country'] == '--') {
            $fields['country'] = null;
        }

        if (($fields['birth_date_month'] != '--') && ($fields['birth_date_day'] != '--') && ($fields['birth_date_year'] != '----')) {
            $fields['birth_date'] = $fields['birth_date_year'] . '-' . $fields['birth_date_month'] . '-' . $fields['birth_date_day'];
        } else {
            $fields['birth_date'] = null;
        }

        if ($fields['gender'] == '--') {
            $fields['gender'] = null;
        }

        if (isset($fields['role_id'])) {
            $roleId = ($fields['role_id'] == 0) ? null : $fields['role_id'];
        } else {
            $roleId = $user->role_id;
        }

        $verified = (isset($fields['verified'])) ? $fields['verified'] : $user->verified;
        $failedAttempts = (isset($fields['failed_attempts'])) ? $fields['failed_attempts'] : $user->failed_attempts;

        $user->role_id         = $roleId;
        $user->username        = (isset($fields['username'])) ? $fields['username'] : $fields['email1'];
        $user->first_name      = $fields['first_name'];
        $user->last_name       = $fields['last_name'];
        $user->email           = $fields['email1'];
        $user->address         = $fields['address'];
        $user->city            = $fields['city'];
        $user->state           = $fields['state'];
        $user->zip             = $fields['zip'];
        $user->country         = $fields['country'];
        $user->phone           = $fields['phone'];
        $user->organization    = $fields['organization'];
        $user->position        = $fields['position'];
        $user->birth_date      = $fields['birth_date'];
        $user->gender          = $fields['gender'];
        $user->updates         = $fields['updates'];
        $user->verified        = $verified;
        $user->failed_attempts = $failedAttempts;

        $user->update();
    }

    /**
     * Update user type
     *
     * @param \Pop\Form\Form $form
     * @return void
     */
    public function updateType(\Pop\Form\Form $form)
    {
        $form->filter('html_entity_decode', array(ENT_QUOTES, 'UTF-8'));

        if ($this->type_id != $form->type_id) {
            $user = Table\Users::findById($this->id);
            $type = Table\Types::findById($form->type_id);

            if (isset($user->id) && isset($type->id)) {
                if ($type->email_as_username) {
                    $newUsername = $user->email;
                    $newUsernameField = 'email';
                } else {
                    $newUsername = strtolower(substr($user->first_name, 0, 1) . $user->last_name);
                    $newUsernameField = 'username';
                }

                $newUsernameAlt = $newUsername;
                $dupeUser = Table\Users::findBy(array($newUsernameField => $newUsername));
                $i = 1;

                while (isset($dupeUser->id)) {
                    $newUsernameAlt = $newUsername . $i;
                    $dupeUser = Table\Users::findBy(array($newUsernameField => $newUsernameAlt));
                    $i++;
                }

                $user->username = $newUsernameAlt;
                $user->type_id = $type->id;
                $user->role_id = null;
                $user->update();
            }
        }
    }

    /**
     * Send verification email to a user
     *
     * @param \Phire\Table\Users $user
     * @param \Phire\Table\Types $type
     * @return void
     */
    public function sendVerification(\Phire\Table\Users $user, $type)
    {
        $basePath = ($type->type != 'User') ? BASE_PATH . '/' . strtolower($type->type) : BASE_PATH . APP_URI;
        $domain = str_replace('www', '', $_SERVER['HTTP_HOST']);

        $rcpt = array(
            'name'  => $user->first_name . ' ' . $user->last_name,
            'email' => $user->email,
            'url'   => 'http://' . $_SERVER['HTTP_HOST'] . $basePath . '/verify/' . $user->id . '/' . sha1($user->email),
            'login' => 'http://' . $_SERVER['HTTP_HOST'] . $basePath . '/login',
            'domain'   => $domain
        );

        $mail = new Mail($domain . ' - Email Verification', $rcpt);
        $mail->from('noreply@' . $domain);
        $mail->setText(file_get_contents(__DIR__ . '/../../../view/' . strtolower($type->type) . '/mail/verify.txt'));
        $mail->send();
    }

    /**
     * Verify user
     *
     * @return void
     */
    public function verify()
    {
        $user = Table\Users::findById($this->id);
        if (isset($user->id)) {
            $user->verified = 1;
            $user->update();
        }
    }

    /**
     * Send password reminder to user
     *
     * @param \Pop\Form\Form $form
     * @return void
     */
    public function sendReminder(\Pop\Form\Form $form)
    {
        $form->filter('html_entity_decode', array(ENT_QUOTES, 'UTF-8'));
        $user = Table\Users::findBy(array('email' => $form->email));

        if (isset($user->id)) {
            $type = Table\Types::findById($user->type_id);
            switch ($type->password_encryption) {
                case 0:
                    $newPassword = $this->password;
                    $newEncPassword = $newPassword;
                    $msg = 'Your username and password is:';
                    break;
                case 1;
                    $newPassword = (string)String::random(8, String::ALPHANUM);
                    $newEncPassword = md5($newPassword);
                    $msg = 'Your password has been reset for security reasons. Your username and new password is:';
                    break;
                case 2:
                    $newPassword = (string)String::random(8, String::ALPHANUM);
                    $newEncPassword = sha1($newPassword);
                    $msg = 'Your password has been reset for security reasons. Your username and new password is:';
                    break;
                case 3:
                    $newPassword = (string)String::random(8, String::ALPHANUM);
                    $newEncPassword = crypt($newPassword, $type->password_salt);
                    $msg = 'Your password has been reset for security reasons. Your username and new password is:';
                    break;
            }

            $user->password = $newEncPassword;
            $user->save();

            $basePath = ($type->type != 'User') ? BASE_PATH . '/' . strtolower($type->type) : BASE_PATH . APP_URI;
            $domain = str_replace('www', '', $_SERVER['HTTP_HOST']);

            $rcpt = array(
                'name'     => $user->first_name . ' ' . $user->last_name,
                'email'    => $user->email,
                'username' => $user->username,
                'password' => $newPassword,
                'login'    => 'http://' . $_SERVER['HTTP_HOST'] . $basePath . '/login',
                'domain'   => $domain,
                'message'  => $msg
            );

            $mail = new Mail($domain . ' - Password Reset', $rcpt);
            $mail->from('noreply@' . $domain);
            $mail->setText(file_get_contents(__DIR__ . '/../../../view/' . strtolower($type->type) . '/mail/forgot.txt'));
            $mail->send();

        }
    }

    /**
     * Unsubscribe a user
     *
     * @param \Pop\Form\Form $form
     * @return void
     */
    public function unsubscribe(\Pop\Form\Form $form)
    {
        $form->filter('html_entity_decode', array(ENT_QUOTES, 'UTF-8'));

        $user = Table\Users::findBy(array('email' => $form->email));
        if (isset($user->id)) {
            $user->delete();
        }
    }

    /**
     * Log a user login
     *
     * @param \Phire\Table\Types $type
     * @param \Phire\Table\Users $user
     * @return void
     */
    protected function log($type, $user)
    {
        $exclude = array();
        if ($type->log_exclude != '') {
            $exclude = explode(',', $type->log_exclude);
        }
        if (!in_array($_SERVER['REMOTE_ADDR'], $exclude)) {
            $emails = explode(',', $type->log_emails);
            $noreply = 'noreply@' . str_replace('www', '', $_SERVER['HTTP_HOST']);

            $options = array(
                'subject' => $type->type . ' Login ',
                'headers' => array(
                    'From'       => $noreply . ' <' . $noreply . '>',
                    'Reply-To'   => $noreply . ' <' . $noreply . '>'
                )
            );

            $msg = "Someone has logged in as a " . strtolower($type->type) . " from " . $_SERVER['REMOTE_ADDR'] . " using '" . $user->username . "'.";

            $logger = new Log\Logger(new Log\Writer\Mail($emails));
            $logger->notice($msg, $options);
        }
    }

}

