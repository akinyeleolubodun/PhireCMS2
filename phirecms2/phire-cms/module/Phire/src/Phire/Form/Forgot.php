<?php
/**
 * @namespace
 */
namespace Phire\Form;

use Pop\Form\Form;
use Pop\Form\Element;
use Pop\Validator;
use Phire\Table;

class Forgot extends Form
{

    /**
     * Constructor method to instantiate the form object
     *
     * @param  string $action
     * @param  string $method
     * @param  array  $fields
     * @param  string $indent
     * @return self
     */
    public function __construct($action, $method, array $fields = null, $indent = null)
    {
        $this->initFieldsValues = array (
            array (
                'type' => 'text',
                'name' => 'email',
                'label' => 'Email:',
                'required' => true,
                'attributes' => array ('size', 40),
                'validators' => new Validator\Email()
            ),
            array (
                'type' => 'submit',
                'name' => 'submit',
                'label' => '&nbsp;',
                'value' => 'Submit'
            )
        );

        parent::__construct($action, $method, $fields, $indent);
    }

    /**
     * Set the field values
     *
     * @param  array $values
     * @param  mixed $filters
     * @param  mixed $params
     * @return \Pop\Form\Form
     */
    public function setFieldValues(array $values = null, $filters = null, $params = null)
    {
        parent::setFieldValues($values, $filters, $params);

        if ($_POST) {
            if (Validator\Email::factory()->evaluate($this->email)) {
                $user = Table\Users::findBy(array('email' => $this->email));
                if (!isset($user->id)) {
                    $this->getElement('email')
                         ->addValidator(new Validator\NotEqual($this->email, 'That email does not exist.'));
                }
            }
        }

        return $this;
    }

}

