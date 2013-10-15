<?php
/**
 * @namespace
 */
namespace Phire\Model;

use Pop\Data\Type\Html;
use Pop\Filter\String;
use Pop\Log;
use Pop\Mail\Mail;
use Pop\Web\Session;
use Phire\Table;

class User extends AbstractModel
{

    /**
     * Login method
     *
     * @param string                 $username
     * @param \Phire\Table\UserTypes $type
     * @param boolean                $success
     * @return void
     */
    public function login($username, $type, $success = true)
    {
        $user = Table\Users::findBy(array('username' => $username));
        $sess = Session::getInstance();
        $typeUri = (strtolower($type->type) != 'user') ? '/' . strtolower($type->type) : APP_URI;

        // If login success
        if (($success) && isset($user->id)) {
            // Create and save new session database entry
            if ($type->track_sessions) {
                $session = new Table\UserSessions(array(
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

            $type = Table\UserTypes::findById($user->type_id);
            $role = Table\UserRoles::findById($user->role_id);

            // Get user login data
            $lastLogin = null;
            $lastUa = null;
            $lastIp = null;
            $lastLoginString = '(N/A)';
            $timestamp = time();
            $ua = $_SERVER['HTTP_USER_AGENT'];
            $ip = $_SERVER['REMOTE_ADDR'];

            if ($user->logins == '') {
                $logins = array(
                    $timestamp => array(
                        'ua' => $ua,
                        'ip' => $ip
                    )
                );
            } else {
                $logins = unserialize($user->logins);
                $last = end($logins);
                $lastLogin = date('Y-m-d H:i:s', key($logins));
                $lastIp = $last['ip'];
                $lastUa = $last['ua'];
                $logins[$timestamp] = array(
                    'ua' => $ua,
                    'ip' => $ip
                );
                $lastLoginString = date('D M j, Y g:i A', strtotime($lastLogin)) . ' (' . (('' !== $lastIp) ? $lastIp : 'N/A') . ')';
            }

            // Create new session object
            $sess->user = new \ArrayObject(
                array(
                    'id'            => $user->id,
                    'type_id'       => $user->type_id,
                    'type'          => $type->type,
                    'typeUri'       => $typeUri,
                    'global_access' => $type->global_access,
                    'role_id'       => (isset($role->id)) ? $role->id : 0,
                    'role'          => (isset($role->id)) ? $role->name : null,
                    'username'      => $username,
                    'email'         => $user->email,
                    'last_login'    => $lastLogin,
                    'last_ua'	    => $lastUa,
                    'last_ip'       => $lastIp,
                    'sess_id'       => $sessionId,
                    'last'          => $lastLoginString
                ),
                \ArrayObject::ARRAY_AS_PROPS
            );

            // Store timestamp and login data
            $user->logins = serialize($logins);
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
     * Get all user types method
     *
     * @return void
     */
    public function getUserTypes()
    {
        $types = Table\UserTypes::findAll('id ASC');
        $typeRows = array();
        foreach ($types->rows as $type) {
            $type->type = ucwords(str_replace('-', ' ', $type->type));
            $typeRows[] = $type;
        }
        $this->data['types'] = $typeRows;
    }

    /**
     * Get all users method
     *
     * @param  int    $typeId
     * @param  string $sort
     * @param  string $page
     * @return void
     */
    public function getAll($typeId, $sort = null, $page = null)
    {
        $order = $this->getSortOrder($sort, $page);
        $sql = Table\Users::getSql();
        $order['field'] = ($order['field'] == 'id') ? DB_PREFIX . 'users.id' : $order['field'];

        // Build the SQL statement to get users
        $sql->select(array(
            DB_PREFIX . 'users.id',
            DB_PREFIX . 'users.type_id',
            DB_PREFIX . 'users.role_id',
            DB_PREFIX . 'user_types.type',
            DB_PREFIX . 'user_roles.name',
            DB_PREFIX . 'users.username',
            DB_PREFIX . 'users.email',
            DB_PREFIX . 'users.logins'
        ))->join(DB_PREFIX . 'user_types', array('type_id', 'id'), 'LEFT JOIN')
          ->join(DB_PREFIX . 'user_roles', array('role_id', 'id'), 'LEFT JOIN')
          ->orderBy($order['field'], $order['order']);

        $sql->select()->where()->equalTo(DB_PREFIX . 'users.type_id', ':type_id');

        // Execute SQL query and get user type
        $users = Table\Users::execute($sql->render(true), array('type_id' => $typeId));
        $userType = Table\UserTypes::findById($typeId);

        $this->data['title'] .= (isset($userType->id)) ? ' ' . $this->config->separator . ' ' . ucwords(str_replace('-', ' ', $userType->type)) : null;
        $this->data['type'] = $userType->type;

        if ($this->data['acl']->isAuth('Phire\Controller\Phire\User\UsersController', 'remove')) {
            $removeCheckbox = '<input type="checkbox" name="remove_users[]" id="remove_users[{i}]" value="[{id}]" />';
            $removeCheckAll = '<input type="checkbox" id="checkall" name="checkall" value="remove_users" />';
            $submit = array(
                'class' => 'remove-btn',
                'value' => 'Remove'
            );
        } else {
            $removeCheckbox = '&nbsp;';
            $removeCheckAll = '&nbsp;';
            $submit = array(
                'class' => 'remove-btn',
                'value' => 'Remove',
                'style' => 'display: none;'
            );
        }

        // Clean up user data
        $userRows = $users->rows;
        foreach ($userRows as $key => $value) {
            $logins = unserialize($value->logins);
            if (is_array($logins)) {
                $lastAry = end($logins);
                $last = date('D  M j, Y H:i:s', key($logins)) . ', ' . $lastAry['ua'] . ' [' . $lastAry['ip'] . ']';
                if ($this->data['acl']->isAuth('Phire\Controller\Phire\User\UsersController', 'edit')) {
                    $count = '<a href="' . BASE_PATH . APP_URI . '/users/logins/' . $value->id . '">' . count($logins) . '</a>';
                } else {
                    $count = count($logins);
                }
            } else {
                $last = '(N/A)';
                $count = 0;
            }

            if ($this->data['acl']->isAuth('Phire\Controller\Phire\User\UsersController', 'edit')) {
                $userRows[$key]->username = '<a href="' . BASE_PATH . APP_URI . '/users/edit/' . $userRows[$key]->id . '">' . $userRows[$key]->username . '</a>';
            }

            $userRows[$key]->name = (null !== $value->name) ? $value->name : '(Blocked)';
            $userRows[$key]->last_login = $last;
            $userRows[$key]->login_count = $count;
        }

        $options = array(
            'form' => array(
                'id'      => 'user-remove-form',
                'action'  => BASE_PATH . APP_URI . '/users/remove/' . $typeId,
                'method'  => 'post',
                'process' => $removeCheckbox,
                'submit'  => $submit
            ),
            'table' => array(
                'headers' => array(
                    'id'          => '<a href="' . BASE_PATH . APP_URI . '/users/index/' . $typeId . '?sort=id">#</a>',
                    'name'        => '<a href="' . BASE_PATH . APP_URI . '/users/index/' . $typeId . '?sort=name">Role</a>',
                    'username'    => '<a href="' . BASE_PATH . APP_URI . '/users/index/' . $typeId . '?sort=username">Username</a>',
                    'email'       => '<a href="' . BASE_PATH . APP_URI . '/users/index/' . $typeId . '?sort=email">Email</a>',
                    'login_count' => 'Logins',
                    'process'     => $removeCheckAll
                ),
                'class'       => 'data-table',
                'cellpadding' => 0,
                'cellspacing' => 0,
                'border'      => 0
            ),
            'exclude' => array(
                'type_id', 'type', 'role_id', 'logins', 'process' => array('id' => $this->data['user']->id)
            ),
            'indent'  => '        '
        );

        if (isset($userRows[0])) {
            $this->data['table'] = Html::encode($userRows, $options, $this->config->pagination_limit, $this->config->pagination_range);
        }
    }

    /**
     * Get all users for export method
     *
     * @param  int $typeId
     * @param  string $sort
     * @param  string $page
     * @param  boolean $isFields
     * @return void
     */
    public function getExport($typeId, $sort = null, $page = null, $isFields = false)
    {
        $order = $this->getSortOrder($sort, $page);
        $sql = Table\Users::getSql();
        $order['field'] = ($order['field'] == 'id') ? DB_PREFIX . 'users.id' : $order['field'];

        // Build the SQL statement to get users
        $sql->select(array(
            DB_PREFIX . 'users.id',
            DB_PREFIX . 'users.username',
            DB_PREFIX . 'users.email',
            DB_PREFIX . 'users.logins'
        ))->orderBy($order['field'], $order['order']);

        $sql->select()->where()->equalTo(DB_PREFIX . 'users.type_id', ':type_id');

        // Execute SQL query and get user type
        $users = Table\Users::execute($sql->render(true), array('type_id' => $typeId));
        $type = Table\UserTypes::findById($typeId);

        $userRows = array();
        if (isset($users->rows[0])) {
            foreach ($users->rows as $row) {
                if (null !== $row->logins) {
                    $logins = unserialize($row->logins);
                    $row->logins = count($logins);
                    end($logins);
                    $row->last_login = date('M j Y g:i A', key($logins));
                } else {
                    $row->logins = 0;
                    $row->last_login = '(Never)';
                }

                // Get any field values
                if ($isFields) {
                    $values = \Fields\Model\FieldValue::getAll($row->id, true);
                    $row = new \ArrayObject(array_merge((array)$row, $values), \ArrayObject::ARRAY_AS_PROPS);
                }

                $userRows[] = $row;
            }
        }

        $this->data['userType'] = $type->type;
        $this->data['userRows'] = $userRows;

    }

    /**
     * Get user by ID method
     *
     * @param  int     $id
     * @param  boolean $isFields
     * @return void
     */
    public function getById($id, $isFields = false)
    {
        $user = Table\Users::findById($id);
        if (isset($user->id)) {
            $type = Table\UserTypes::findById($user->type_id);
            $userValues = $user->getValues();
            $userValues['type_name'] = (isset($type->id) ? ucwords(str_replace('-', ' ', $type->type)) : null);
            $userValues['email1'] = $userValues['email'];
            $userValues['verified'] = (int)$userValues['verified'];

            // If the Fields module is installed, and if there are fields for this form/model
            if ($isFields) {
                $userValues = array_merge($userValues, \Fields\Model\FieldValue::getAll($id));
            }

            $this->data = array_merge($this->data, $userValues);
        }
    }

    /**
     * Get user by ID method
     *
     * @param  int     $id
     * @param  boolean $isFields
     * @return void
     */
    public function getLoginsById($id, $isFields = false)
    {
        // Get user logins
        $this->getById($id, $isFields);
        $logins = unserialize($this->logins);
        $loginsAry = array();

        $i = 1;
        foreach ($logins as $time => $login) {
            $loginsAry[] = array(
                'id'         => $i,
                'timestamp'  => date('D  M j, Y H:i:s', $time),
                'user_agent' => $login['ua'],
                'ip_address' => $login['ip']
            );
            $i++;
        }

        $options = array(
            'form' => array(
                'id'      => 'user-login-remove-form',
                'action'  => BASE_PATH . APP_URI . '/users/logins/' . $this->id,
                'method'  => 'post',
                'process' => '&nbsp;',
                'submit'  => array(
                    'class' => 'remove-btn',
                    'value' => 'Clear'
                )
            ),
            'table' => array(
                'headers' => array(
                    'id'          => '#',
                    'ip_address'  => 'IP Address',
                    'process'     => '&nbsp;'
                ),
                'class'       => 'data-table',
                'cellpadding' => 0,
                'cellspacing' => 0,
                'border'      => 0
            )
        );

        $this->data['title'] = 'Users ' . $this->config->separator . ' ' . $this->type_name . ' ' . $this->config->separator . ' Logins ' . $this->config->separator . ' ' . $this->data['username'];
        $this->data['table'] = Html::encode($loginsAry, $options, $this->config()->pagination_limit, $this->config()->pagination_range);
    }

    /**
     * Save user
     *
     * @param \Pop\Form\Form $form
     * @param  boolean       $isFields
     * @return void
     */
    public function save(\Pop\Form\Form $form, $isFields = false)
    {
        $form->filter('html_entity_decode', array(ENT_QUOTES, 'UTF-8'));
        $fields = $form->getFields();
        $type = Table\UserTypes::findById($fields['type_id']);

        if (isset($fields['password1'])) {
            $password = $fields['password1'];

            // Set the password according to the user type
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
        } else {
            $password = '';
        }

        // Set the username according to user type
        $fields['username'] = (isset($fields['username'])) ? $fields['username'] : $fields['email1'];
        $fields['password'] = $password;
        $fields['email'] = $fields['email1'];

        // Set the role according to user type
        if (isset($fields['role_id'])) {
            $fields['role_id'] = ($fields['role_id'] == 0) ? null : $fields['role_id'];
        } else {
            $fields['role_id'] = ($type->approval) ? null : $type->default_role_id;
        }

        // Set verified or not
        if (!isset($fields['verified'])) {
            $fields['verified'] = ($type->verification) ? 0 : 1;
        }

        // Save the new user
        $user = new Table\Users(array(
            'type_id'         => $fields['type_id'],
            'role_id'         => $fields['role_id'],
            'username'        => $fields['username'],
            'password'        => $fields['password'],
            'email'           => $fields['email'],
            'verified'        => $fields['verified'],
            'logins'          => null,
            'failed_attempts' => 0
        ));
        $user->save();
        $this->data['id'] = $user->id;

        $sess = Session::getInstance();
        $sess->last_user_id = $user->id;

        // If the Fields module is installed, and if there are fields for this form/model
        if ($isFields) {
            \Fields\Model\FieldValue::save($fields, $user->id);
        }

        // Send verification if needed
        if (($type->verification) && !($user->verified)) {
            $this->sendVerification($user, $type);
        }
    }

    /**
     * Update user
     *
     * @param \Pop\Form\Form $form
     * @param  boolean       $isFields
     * @return void
     */
    public function update(\Pop\Form\Form $form, $isFields = false)
    {
        $form->filter('html_entity_decode', array(ENT_QUOTES, 'UTF-8'));
        $fields = $form->getFields();
        $type = Table\UserTypes::findById($fields['type_id']);
        $user = Table\Users::findById($fields['id']);

        if (isset($fields['password1'])) {
            // If there's a new password, set according to the user type
            if (($fields['password1'] != '') && ($fields['password2'] != '')) {
                $password = $fields['password1'];

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
        }

        // Set role
        if (isset($fields['role_id'])) {
            $roleId = ($fields['role_id'] == 0) ? null : $fields['role_id'];
        } else {
            $roleId = $user->role_id;
        }

        // Set verified and attempts
        $verified = (isset($fields['verified'])) ? $fields['verified'] : $user->verified;
        $failedAttempts = (isset($fields['failed_attempts'])) ? $fields['failed_attempts'] : $user->failed_attempts;

        // Save the user's updated data
        $user->role_id         = $roleId;
        $user->username        = (isset($fields['username'])) ? $fields['username'] : $fields['email1'];
        $user->email           = $fields['email1'];
        $user->verified        = $verified;
        $user->failed_attempts = $failedAttempts;

        $sess = Session::getInstance();
        $sess->last_user_id = $user->id;
        if ($sess->user->id == $user->id) {
            $sess->user->username = $user->username;
        }

        $user->update();
        $this->data['id'] = $user->id;

        // If the Fields module is installed, and if there are fields for this form/model
        if ($isFields) {
            \Fields\Model\FieldValue::update($fields, $user->id);
        }
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

        // If the user type has changed
        if ($this->type_id != $form->type_id) {
            $user = Table\Users::findById($this->id);
            $type = Table\UserTypes::findById($form->type_id);

            if (isset($user->id) && isset($type->id)) {
                // If the new type has a different username setting
                if ($type->email_as_username) {
                    $newUsername = $user->email;
                    $newUsernameField = 'email';
                } else {
                    $newUsername = $user->email;
                    $newUsernameField = 'username';
                }

                // Check for dupes
                $newUsernameAlt = $newUsername;
                $dupeUser = Table\Users::findBy(array($newUsernameField => $newUsername));
                $i = 1;

                while (isset($dupeUser->id) && ($dupeUser->id != $user->id)) {
                    $newUsernameAlt = $newUsername . $i;
                    $dupeUser = Table\Users::findBy(array($newUsernameField => $newUsernameAlt));
                    $i++;
                }

                // Save updated user's type
                $user->username = $newUsernameAlt;
                $user->type_id = $type->id;
                $user->role_id = null;
                $user->update();
            }
        }
    }

    /**
     * Remove user
     *
     * @param  array   $post
     * @param  boolean $isFields
     * @return void
     */
    public function remove(array $post, $isFields = false)
    {
        if (isset($post['remove_users'])) {
            foreach ($post['remove_users'] as $id) {
                $user = Table\Users::findById($id);
                if (isset($user->id)) {
                    $user->delete();
                }

                // If the Fields module is installed, and if there are fields for this form/model
                if ($isFields) {
                    \Fields\Model\FieldValue::remove($id);
                }
            }
        }
    }

    /**
     * Send verification email to a user
     *
     * @param \Phire\Table\Users $user
     * @param \Phire\Table\UserTypes $type
     * @return void
     */
    public function sendVerification(\Phire\Table\Users $user, $type)
    {
        // Get the base path and domain
        $basePath = (strtolower($type->type) != 'user') ? BASE_PATH . '/' . strtolower($type->type) : BASE_PATH . APP_URI;
        $domain = str_replace('www.', '', $_SERVER['HTTP_HOST']);

        // Set the recipient
        $rcpt = array(
            'name'  => $user->username,
            'email' => $user->email,
            'url'   => 'http://' . $_SERVER['HTTP_HOST'] . $basePath . '/verify/' . $user->id . '/' . sha1($user->email),
            'login' => 'http://' . $_SERVER['HTTP_HOST'] . $basePath . '/login',
            'domain'   => $domain
        );

        if (file_exists($_SERVER['DOCUMENT_ROOT'] . BASE_PATH . CONTENT_PATH . '/extensions/themes/phire/mail')) {
            $mailTmpl = file_get_contents($_SERVER['DOCUMENT_ROOT'] . BASE_PATH . CONTENT_PATH . '/extensions/themes/phire/mail/verify.txt');
        } else {
            $mailTmpl = file_get_contents(__DIR__ . '/../../../view/phire/mail/verify.txt');
        }

        // Send email verification
        $mail = new Mail($domain . ' - Email Verification', $rcpt);
        $mail->from('noreply@' . $domain);
        $mail->setText($mailTmpl);
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
            $type = Table\UserTypes::findById($user->type_id);

            // Based on user type settings, set or reset the password
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

            // Save new password
            $user->password = $newEncPassword;
            $user->save();

            // Get base path and domain
            $basePath = (strtolower($type->type) != 'user') ? BASE_PATH . '/' . strtolower($type->type) : BASE_PATH . APP_URI;
            $domain = str_replace('www.', '', $_SERVER['HTTP_HOST']);

            // Set recipient
            $rcpt = array(
                'name'     => $user->username,
                'email'    => $user->email,
                'username' => $user->username,
                'password' => $newPassword,
                'login'    => 'http://' . $_SERVER['HTTP_HOST'] . $basePath . '/login',
                'domain'   => $domain,
                'message'  => $msg
            );

            if (file_exists($_SERVER['DOCUMENT_ROOT'] . BASE_PATH . CONTENT_PATH . '/extensions/themes/phire/mail')) {
                $mailTmpl = file_get_contents($_SERVER['DOCUMENT_ROOT'] . BASE_PATH . CONTENT_PATH . '/extensions/themes/phire/mail/forgot.txt');
            } else {
                $mailTmpl = file_get_contents(__DIR__ . '/../../../view/phire/mail/forgot.txt');
            }

            // Send reminder
            $mail = new Mail($domain . ' - Password Reset', $rcpt);
            $mail->from('noreply@' . $domain);
            $mail->setText($mailTmpl);
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
     * @param \Phire\Table\UserTypes $type
     * @param \Phire\Table\Users     $user
     * @return void
     */
    protected function log($type, $user)
    {
        $exclude = array();
        if ($type->log_exclude != '') {
            $exclude = explode(',', $type->log_exclude);
        }

        $domain = str_replace('www.', '', $_SERVER['HTTP_HOST']);

        if (!in_array($_SERVER['REMOTE_ADDR'], $exclude)) {
            $emails = explode(',', $type->log_emails);
            $noreply = 'noreply@' . $domain;

            $options = array(
                'subject' => 'Phire CMS ' . ucfirst(strtolower($type->type)) . ' Login Notification (' . $domain . ')',
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

