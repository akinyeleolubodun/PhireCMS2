<?php
/**
 * @namespace
 */
namespace Phire\Model;

use Pop\Filter\String;
use Phire\Table\UserRoles;
use Phire\Table\UserTypes;
use Phire\Table\Users;

class Type extends \Pop\Mvc\Model
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
            $this->data['role'] = UserRoles::getRole($sess->user->role_id);
            $this->data['globalAccess'] = $sess->user->global_access;
        }
    }

    /**
     * Get all types method
     *
     * @return void
     */
    public function getAll()
    {
        $types = UserTypes::findAll('id ASC');
        $this->data['types'] = $types->rows;
    }

    /**
     * Get type by ID method
     *
     * @param  int $id
     * @return void
     */
    public function getById($id)
    {
        $type = UserTypes::findById($id);
        $this->data = array_merge($this->data, $type->getValues());
    }

    /**
     * Save type
     *
     * @param \Pop\Form\Form $form
     * @return void
     */
    public function save(\Pop\Form\Form $form)
    {
        $form->filter('html_entity_decode', array(ENT_QUOTES, 'UTF-8'));
        $fields = $form->getFields();

        $fields['log_emails'] = str_replace(', ', ',', $fields['log_emails']);
        $fields['log_exclude'] = str_replace(', ', ',', $fields['log_exclude']);

        unset($fields['id']);
        unset($fields['submit']);

        $type = new UserTypes($fields);
        $type->save();
    }

    /**
     * Update type
     *
     * @param \Pop\Form\Form $form
     * @return void
     */
    public function update(\Pop\Form\Form $form)
    {
        $form->filter('html_entity_decode', array(ENT_QUOTES, 'UTF-8'));
        $fields = $form->getFields();

        $fields['log_emails'] = str_replace(', ', ',', $fields['log_emails']);
        $fields['log_exclude'] = str_replace(', ', ',', $fields['log_exclude']);

        unset($fields['submit']);

        $type = UserTypes::findById($form->id);

        // If the password encryption changed
        if ($type->password_encryption != $fields['password_encryption']) {
            $users = Users::findAll(null, array('type_id' => $type->id));
            foreach ($users->rows as $u) {
                $user = Users::findById($u->id);
                if (isset($user->id)) {
                    switch ($fields['password_encryption']) {
                        case 0:
                            $fields['password_salt'] = '';
                            $newPassword = $this->password;
                            break;
                        case 1;
                            $fields['password_salt'] = '';
                            $newPassword = md5((string)String::random(8, String::ALPHANUM));
                            break;
                        case 2:
                            $fields['password_salt'] = '';
                            $newPassword = sha1((string)String::random(8, String::ALPHANUM));
                            break;
                        case 3:
                            $newPassword = crypt((string)String::random(8, String::ALPHANUM), $fields['password_salt']);
                            break;
                    }

                    $user->password = $newPassword;
                    $user->save();
                }
            }
        }

        $type->setValues($fields);
        $type->update();
    }

}

