<?php
/**
 * Pop PHP Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.TXT.
 * It is also available through the world-wide-web at this URL:
 * http://www.popphp.org/LICENSE.TXT
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to info@popphp.org so we can send you a copy immediately.
 *
 * @category   Pop
 * @package    Pop_Form
 * @author     Nick Sagona, III <nick@popphp.org>
 * @copyright  Copyright (c) 2009-2012 Moc 10 Media, LLC. (http://www.moc10media.com)
 * @license    http://www.popphp.org/LICENSE.TXT     New BSD License
 */

/**
 * @namespace
 */
namespace Pop\Form;

use Pop\Dom\Dom,
    Pop\Dom\Child;

/**
 * This is the Form class for the Form component.
 *
 * @category   Pop
 * @package    Pop_Form
 * @author     Nick Sagona, III <nick@popphp.org>
 * @copyright  Copyright (c) 2009-2012 Moc 10 Media, LLC. (http://www.moc10media.com)
 * @license    http://www.popphp.org/LICENSE.TXT   T  New BSD License
 * @version    1.0.2
 */
class Form extends Dom
{

    /**
     * Form element node
     * @var Child
     */
    protected $form = null;

    /**
     * Form action
     * @var string
     */
    protected $action = null;

    /**
     * Form method
     * @var string
     */
    protected $method = null;

    /**
     * Form template for HTML formatting.
     * @var string
     */
    protected $template = null;

    /**
     * Field names of the database table
     * @var array
     */
    protected $fields = array();

    /**
     * Form init field values
     * @var array
     */
    protected $initFieldsValues = array();

    /**
     * Constructor
     *
     * Instantiate the form object
     *
     * @param  string $action
     * @param  string $method
     * @param  array  $fields
     * @param  string $indent
     * @return void
     */
    public function __construct($action, $method, array $fields = null, $indent = null)
    {
        // Set the form's action and method.
        $this->action = $action;
        $this->method = $method;

        // Create the parent DOM element and the form child element.
        parent::__construct(null, 'utf-8', null, $indent);
        $this->form = new Child('form', null, null, false, $indent);
        $this->form->setAttributes(array('action' => $this->action, 'method' => $this->method));
        $this->addChild($this->form);

        if (null !== $fields) {
            $this->setFields($fields);
        }
    }

    /**
     * Set the fields of the form object.
     *
     * @param  array $fields
     * @throws Exception
     * @return Pop\Form\Form
     */
    public function setFields(array $fields)
    {
        $isValid = true;
        foreach ($fields as $key => $value) {
            if (!is_array($value)) {
                $isValid = false;
            }
        }

        if (!$isValid) {
            throw new Exception('The array parameter passed must contain an array of field values.');
        }

        foreach ($fields as $key => $value) {
            $this->fields[$value['name']] = (isset($value['value'])) ? $value['value'] : null;
        }

        $this->initFieldsValues = $fields;
        return $this;
    }

    /**
     * Get the form fields
     *
     * @return array
     */
    public function getFields()
    {
        return $this->fields;
    }

    /**
     * Set the field values
     *
     * @param  array $values
     * @param  mixed $filters
     * @param  mixed $params
     * @return Pop\Form\Form
     */
    public function setFieldValues(array $values = null, $filters = null, $params = null)
    {
        // Filter values if passed
        if ((null !== $values) && (null !== $filters)) {
            $values = $this->filterValues($values, $filters, $params);
        }

        // Loop through the initial fields values and build the fields
        // based on the _initFieldsValues property.
        if (isset($this->initFieldsValues[0])) {
            foreach ($this->initFieldsValues as $field) {
                if (is_array($field) && isset($field['type']) && isset($field['name'])) {
                    $type = $field['type'];
                    $name = $field['name'];
                    $label = (isset($field['label'])) ? $field['label'] : null;
                    $required = (isset($field['required'])) ? $field['required'] : null;
                    $attributes = (isset($field['attributes'])) ? $field['attributes'] : null;
                    $validators = (isset($field['validators'])) ? $field['validators'] : null;

                    if ((null !== $values) && array_key_exists($name, $values)) {
                        if (($type == 'checkbox') || ($type == 'radio') || ($type == 'select')) {
                            $value = (isset($field['value'])) ? $field['value'] : null;
                            $marked = $values[$name];
                        } else {
                            $value = $values[$name];
                            $marked = (isset($field['marked'])) ? $field['marked'] : null;
                        }
                    } else {
                        $value = (isset($field['value'])) ? $field['value'] : null;
                        $marked = (isset($field['marked'])) ? $field['marked'] : null;
                    }
                    // Initialize the form element.
                    switch ($type) {
                        case 'checkbox':
                            $elem = new Element\Checkbox($name, $value, $marked);
                            break;
                        case 'radio':
                            $elem = new Element\Radio($name, $value, $marked);
                            break;
                        case 'select':
                            $elem = new Element\Select($name, $value, $marked);
                            break;
                        case 'textarea':
                            $elem = new Element\Textarea($name, $value, $marked);
                            break;
                        default:
                            $elem = new Element($type, $name, $value, $marked);
                    }

                    // Set the label.
                    if (null !== $label) {
                        $elem->setLabel($label);
                    }

                    // Set if required.
                    if (null !== $required) {
                        $elem->setRequired($required);
                    }

                    // Set any attributes.
                    if (null !== $attributes) {
                        if (is_array($attributes)) {
                            if ((count($attributes) == 2) && !is_array($attributes[0]) && !is_array($attributes[1])) {
                                $elem->setAttributes($attributes[0], $attributes[1]);
                            } else {
                                foreach ($attributes as $att) {
                                    if (isset($att[0]) && isset($att[1])) {
                                        $elem->setAttributes($att[0], $att[1]);
                                    }
                                }
                            }
                        }
                    }

                    // Set any validators.
                    if (null !== $validators) {
                        if (is_array($validators)) {
                            foreach ($validators as $val) {
                                $elem->addValidator($val);
                            }
                        } else {
                            $elem->addValidator($validators);
                        }
                    }

                    $this->addElements($elem);
                }
            }
        // Else, set the passed values to the elements that
        // are already added to the form object
        } else {
            $fields = $this->getElements();
            if ((null !== $values) && (count($fields) > 0)) {
                foreach ($fields as $field) {
                    // If a multi-value form element
                    if (isset($values[$field->name])) {
                        if (isset($field->values)) {
                            $field->marked = $values[$field->name];
                            $this->fields[$field->name] = $values[$field->name];
                            // Loop through the field's children
                            if ($field->hasChildren()) {
                                $children = $field->getChildren();
                                foreach ($children as $key => $child) {
                                    // If checkbox or radio
                                    if (($child->getAttribute('type') == 'checkbox') || ($child->getAttribute('type') == 'radio')) {
                                        if (is_array($field->marked) && in_array($child->getAttribute('value'), $field->marked)) {
                                            $field->getChild($key)->setAttributes('checked', 'checked');
                                        } else if ($child->getAttribute('value') == $field->marked) {
                                            $field->getChild($key)->setAttributes('checked', 'checked');
                                        }
                                    // If select option
                                    } else if ($child->getNodeName() == 'option') {
                                        if ($child->getAttribute('value') == $field->marked) {
                                            $field->getChild($key)->setAttributes('selected', 'selected');
                                        }
                                    }
                                }
                            }
                        // Else, if a single-value form element
                        } else {
                            $field->value = $values[$field->name];
                            $this->fields[$field->name] = $values[$field->name];
                            if ($field->getNodeName() == 'textarea') {
                                $field->setNodeValue($values[$field->name]);
                            } else {
                                $field->setAttributes('value', $values[$field->name]);
                            }
                        }
                    }
                }
            }
        }

        return $this;
    }

    /**
     * Set a form template for the render method to utilize.
     *
     * @param  string $tmpl
     * @return Pop\Form\Form
     */
    public function setTemplate($tmpl)
    {
        if (file_exists($tmpl)) {
            $this->template = file_get_contents($tmpl);
        } else {
            $this->template = $tmpl;
        }
        return $this;
    }

    /**
     * Get the form template for the render method to utilize.
     *
     * @return string
     */
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * Set an attribute or attributes for the form object.
     *
     * @param  array|string $a
     * @param  string $v
     * @return Pop\Form\Form
     */
    public function setAttributes($a, $v = null)
    {
        $this->form->setAttributes($a, $v);
        return $this;
    }

    /**
     * Get the attributes of the form object.
     *
     * @return array
     */
    public function getAttributes()
    {
        return $this->form->getAttributes();
    }

    /**
     * Add a form element or elements to the form object.
     *
     * @param  array|string $e
     * @return Pop\Form\Form
     */
    public function addElements($e)
    {
        if (is_array($e)) {
            $this->form->addChildren($e);
        } else {
            $this->form->addChild($e);
        }

        $children = $this->form->getChildren();

        foreach ($children as $child) {
            $attribs = $child->getAttributes();
            if ($child instanceof Element\Textarea) {
                if (isset($attribs['name'])) {
                    $this->fields[$attribs['name']] = (isset($child->value) ? $child->value : null);
                }
            } else if ($child instanceof Element\Select) {
                if (isset($attribs['name'])) {
                    $this->fields[$attribs['name']] = (isset($child->marked) ? $child->marked : null);
                }
            } else if ($child instanceof Element\Radio) {
                $radioChildren = $child->getChildren();
                $childAttribs = $radioChildren[0]->getAttributes();
                if (isset($childAttribs['name'])) {
                    $this->fields[$childAttribs['name']] = (isset($child->marked) ? $child->marked : null);
                }
            } else if ($child instanceof Element\Checkbox) {
                $radioChildren = $child->getChildren();
                $childAttribs = $radioChildren[0]->getAttributes();
                if (isset($childAttribs['name'])) {
                    $key = str_replace('[]', '', $childAttribs['name']);
                    $this->fields[$key] = (isset($child->marked) ? $child->marked : null);
                }
            } else {
                if (isset($attribs['name'])) {
                    $this->fields[$attribs['name']] = (isset($attribs['value']) ? $attribs['value'] : null);
                }
            }
        }

        return $this;
    }

    /**
     * Get the main form element.
     *
     * @return array
     */
    public function getFormElement()
    {
        return $this->form;
    }

    /**
     * Get the elements of the form object.
     *
     * @return array
     */
    public function getElements()
    {
        return $this->form->getChildren();
    }

    /**
     * Get an element object of the form by name.
     *
     * @param string $elementName
     * @return Pop\Form\Element
     */
    public function getElement($elementName)
    {
        return $this->form->getChild($this->getElementIndex($elementName));
    }

    /**
     * Get the index of an element object of the form by name.
     *
     * @param string $elementName
     * @return int
     */
    public function getElementIndex($elementName)
    {
        $name = null;
        $elem = null;
        $index = null;
        $elems =  $this->form->getChildren();

        foreach ($elems as $i => $e) {
            if ($e->getNodeName() == 'fieldset') {
                $children = $e->getChildren();
                foreach ($children as $c) {
                    if ($c->getNodeName() == 'input') {
                        $attribs = $c->getAttributes();
                        $name = str_replace('[]', '', $attribs['name']);
                    }
                }
            } else {
                $attribs = $e->getAttributes();
                $name = $attribs['name'];
            }
            if ($name == $elementName) {
                $index = $i;
                $elem = $e;
            }
        }

        return $index;
    }

    /**
     * Determine whether or not the form object is valid and return the result.
     *
     * @return boolean
     */
    public function isValid()
    {
        $noErrors = true;
        $children = $this->form->getChildren();

        // Check each element for validators, validate them and return the result.
        foreach ($children as $child) {
            if ($child->validate() == false) {
                $noErrors = false;
            }
        }

        return $noErrors;
    }

    /**
     * Render the form object either using the defined template or by a basic
     * 1:1 DL/DD tag structure. The template should use a simple search and
     * replace format that contains [{element}] and/or [{element_label}] for
     * the placeholders that will be swapped out. Required fields' labels have
     * have class="required" and error messages have class="error" for
     * styling purposes.
     *
     * @param  boolean $ret
     * @throws Exception
     * @return void
     */
    public function render($ret = false)
    {
        // Check to make sure form elements exist.
        if ((count($this->form->getChildren()) == 0) && (count($this->initFieldsValues) == 0)) {
            throw new Exception('Error: There are no form elements declared for this form object.');
        } else if ((count($this->form->getChildren()) == 0) && (count($this->initFieldsValues) > 0)) {
            $this->setFieldValues();
        }

        // If the template is not set, default to the basic output.
        if (null === $this->template) {
            $this->renderWithoutTemplate();
        // Else, start building the form's HTML output based on the template.
        } else {
            $this->renderWithTemplate();
        }

        // Return or print the form output.
        if ($ret) {
            return $this->output;
        } else {
            echo $this->output;
        }

    }

    /**
     * Method to filter the values
     *
     * @param  array $values
     * @param  mixed $filters
     * @param  mixed $params
     * @return array
     */
    protected function filterValues($values, $filters, $params)
    {
        $filteredValues = array();

        if (!is_array($filters)) {
            $filters = array($filters);
        }

        if ((null !== $params) && !is_array($params)) {
            $params = array($params);
        }

        foreach ($values as $key => $value) {
            foreach ($filters as $fk => $filter) {
                if (function_exists($filter)) {
                    if ($value instanceof \ArrayObject) {
                        $value = (array)$value;
                    }
                    if (is_array($value)) {
                        $filteredAry = array();
                        foreach ($value as $k => $v) {
                            if ((null !== $params) && isset($params[$fk])) {
                                $pars = (!is_array($params[$fk])) ?
                                    array($v, $params[$fk]) :
                                    array_merge(array($v), $params[$fk]);
                                $filteredAry[$k] = call_user_func_array($filter, $pars);
                            } else {
                                $filteredAry[$k] = $filter($v);
                            }
                        }
                        $filteredValues[$key] = $filteredAry;
                        $value = $filteredAry;
                    } else {
                        if ((null !== $params) && isset($params[$fk])) {
                            $pars = (!is_array($params[$fk])) ?
                                    array($value, $params[$fk]) :
                                    array_merge(array($value), $params[$fk]);
                            $filteredValues[$key] = call_user_func_array($filter, $pars);
                        } else {
                            $filteredValues[$key] = $filter($value);
                        }
                        $value = $filteredValues[$key];
                    }
                } else {
                    $filteredValues[$key] = $value;
                }
            }
        }

        return $filteredValues;
    }

    /**
     * Method to render the form using a basic 1:1 DD/DL layout
     *
     * @return void
     */
    protected function renderWithoutTemplate()
    {
        // Initialize properties.
        $this->output = null;
        $children = $this->form->getChildren();
        $this->form->removeChildren();

        // Create DL element.
        $dl = new Child('dl', null, null, false, $this->form->getIndent());

        // Loop through the children and create and attach the appropriate DT and DT elements, with labels where applicable.
        foreach ($children as $child) {
            // Clear the password field from display.
            if ($child->getAttribute('type') == 'password') {
                $child->value = null;
                $child->setAttributes('value', null);
            }

            // If the element label is set, render the appropriate DT and DD elements.
            if (isset($child->label) && (null !== $child->label)) {
                // Create the DT and DD elements.
                $dt = new Child('dt', null, null, false, ($this->form->getIndent() . '    '));
                $dd = new Child('dd', null, null, false, ($this->form->getIndent() . '    '));

                // Format the label name.
                $lbl_name = ($child->getNodeName() == 'fieldset') ? '1' : '';
                $label = new Child('label', $child->label, null, false, ($this->form->getIndent() . '        '));

                if ($child->getNodeName() == 'fieldset') {
                    $chdrn = $child->getChildren();
                    $attribs = $chdrn[0]->getAttributes();
                } else {
                    $attribs = $child->getAttributes();
                }

                $name = (isset($attribs['name'])) ? $attribs['name'] : '';
                $name = str_replace('[]', '', $name);

                if ($child->required) {
                    $label->setAttributes(array('for' => ($name . $lbl_name), 'class' => 'required'));
                } else {
                    $label->setAttributes('for', ($name . $lbl_name));
                }

                // Add the appropriate children to the appropriate elements.
                $dt->addChild($label);
                $child->setIndent(($this->form->getIndent() . '        '));
                $childChildren = $child->getChildren();
                $child->removeChildren();

                foreach ($childChildren as $cChild) {
                    $cChild->setIndent(($this->form->getIndent() . '            '));
                    $child->addChild($cChild);
                }

                $dd->addChild($child);
                $dl->addChildren(array($dt, $dd));
            // Else, render only a DD element.
            } else {
                $dd = new Child('dd', null, null, false, ($this->form->getIndent() . '    '));
                $child->setIndent(($this->form->getIndent() . '        '));
                $dd->addChild($child);
                $dl->addChild($dd);
            }
        }

        // Add the DL element and its children to the form element.
        $this->form->addChild($dl);
        $this->output = $this->form->render(true);
    }

    /**
     * Method to render the form using the template
     *
     * @return void
     */
    protected function renderWithTemplate()
    {
        // Initialize properties and variables.
        $this->output = null;
        $children = $this->form->getChildren();

        // Loop through the child elements of the form.
        foreach ($children as $child) {
            // Clear the password field from display.
            if ($child->getAttribute('type') == 'password') {
                $child->value = null;
                $child->setAttributes('value', null);
            }

            // Get the element name.
            if ($child->getNodeName() == 'fieldset') {
                $chdrn = $child->getChildren();
                $attribs = $chdrn[0]->getAttributes();
            } else {
                $attribs = $child->getAttributes();
            }
            $name = (isset($attribs['name'])) ? $attribs['name'] : '';
            $name = str_replace('[]', '', $name);

            // Set the element's label, if applicable.
            if (null !== $child->label) {

                // Format the label name.
                $label = new Child('label', $child->label);

                if ($child->required) {
                    $label->setAttributes(array('for' => $name, 'class' => 'required'));
                } else {
                    $label->setAttributes('for', $name);
                }

                // Swap the element's label placeholder with the rendered label element.
                $labelSearch = '[{' . $name . '_label}]';
                $labelReplace = $label->render(true);
                $this->template = str_replace($labelSearch, substr($labelReplace, 0, -1), $this->template);
            }

            // Calculate the element's indentation.
            $indent = null;
            $childIndent = substr($this->template, 0, strpos($this->template, ('[{' . $name . '}]')));
            $childIndent = substr($childIndent, (strrpos($childIndent, "\n") + 1));

            $matches = array();
            preg_match_all('/[^\s]/', $childIndent, $matches);
            if (isset($matches[0])) {
                foreach ($matches[0] as $str) {
                    $childIndent = str_replace($str, ' ', $childIndent);
                }
            }

            // Set each child element's indentation.
            $childChildren = $child->getChildren();
            $child->removeChildren();
            foreach ($childChildren as $cChild) {
                $cChild->setIndent(($childIndent . '    '));
                $child->addChild($cChild);
            }

            // Swap the element's placeholder with the rendered element.
            $elementSearch = '[{' . $name . '}]';
            $elementReplace = $child->render(true, 0, $indent, $childIndent);
            $elementReplace = substr($elementReplace, 0, -1);
            $elementReplace = str_replace('</select>', $childIndent . '</select>', $elementReplace);
            $elementReplace = str_replace('</fieldset>', $childIndent . '</fieldset>', $elementReplace);
            $this->template = str_replace($elementSearch, $elementReplace, $this->template);
        }

        // Set the rendered form content and remove the children.
        $this->form->setNodeValue("\n" . $this->template . "\n" . $this->form->getIndent());
        $this->form->removeChildren();
        $this->output = $this->form->render(true);
    }

    /**
     * Set method to set the property to the value of _fields[$name].
     *
     * @param  string $name
     * @param  mixed $value
     * @throws Exception
     * @return void
     */
    public function __set($name, $value)
    {
        $this->fields[$name] = $value;
    }

    /**
     * Get method to return the value of _fields[$name].
     *
     * @param  string $name
     * @throws Exception
     * @return mixed
     */
    public function __get($name)
    {
        return (!array_key_exists($name, $this->fields)) ? null : $this->fields[$name];
    }

    /**
     * Output the form object in a basic HTML format. Each form element is formatted to a 1:1 label to element
     * ratio, using <dl>, <dt> and <dd> tags. Required fields' labels have class="required" and error messages
     * have class="error" for styling purposes.
     *
     * @return string
     */

    public function __toString()
    {
        return $this->render(true);
    }

}
