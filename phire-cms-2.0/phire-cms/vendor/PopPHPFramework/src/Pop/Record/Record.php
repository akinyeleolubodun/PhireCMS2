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
 * @package    Pop_Record
 * @author     Nick Sagona, III <nick@popphp.org>
 * @copyright  Copyright (c) 2009-2012 Moc 10 Media, LLC. (http://www.moc10media.com)
 * @license    http://www.popphp.org/LICENSE.TXT     New BSD License
 */

/**
 * @namespace
 */
namespace Pop\Record;

use Pop\Db\Db,
    Pop\Filter\String;

/**
 * This is the Record class for the Record component.
 *
 * @category   Pop
 * @package    Pop_Record
 * @author     Nick Sagona, III <nick@popphp.org>
 * @copyright  Copyright (c) 2009-2012 Moc 10 Media, LLC. (http://www.moc10media.com)
 * @license    http://www.popphp.org/LICENSE.TXT     New BSD License
 * @version    1.0.2
 */
class Record
{

    /**
     * Constant to set the type to INSERT on save
     * @var int
     */
    const INSERT = 0;

    /**
     * Constant to set the type to UPDATE on save
     * @var int
     */
    const UPDATE = 1;

    /**
     * Database connection(s)
     * @var array
     */
    public static $db = array('default' => null);

    /**
     * Rows of multiple return results from a database query
     * in an ArrayObject format.
     * @var array
     */
    public $rows = array();

    /**
     * Database adapter
     * @var Pop\Record\Escaped|Pop\Record\Prepared
     */
    protected $interface = null;

    /**
     * Table prefix
     * @var string
     */
    protected $prefix = null;

    /**
     * Table name of the database tablel
     * @var string
     */
    protected $tableName = null;

    /**
     * Primary ID column name of the database table
     * @var string|array
     */
    protected $primaryId = 'id';

    /**
     * Property that determines whether or not the primary ID is auto-increment or not
     * @var boolean
     */
    protected $auto = true;

    /**
     * Column names of the database table
     * @var array
     */
    protected $columns = array();

    /**
     * Flag on whether or not to use prepared statements
     * @var boolean
     */
    protected $usePrepared = true;

    /**
     * Flag on which quote identifier to use.
     * @var int
     */
    protected $idQuote = null;

    /**
     * Constructor
     *
     * Instantiate the database record object.
     *
     * @param  array $columns
     * @param  Db    $db
     * @return void
     */
    public function __construct(array $columns = null, Db $db = null)
    {
        $class = get_class($this);

        if (null !== $db) {
            $class::setDb($db);
        }

        // If the $columns argument is set, set the _columns properties.
        if (null !== $columns) {
            $this->columns = $columns;
        }

        if (null === $this->tableName) {
            if (strpos($class, '_') !== false) {
                $cls = substr($class, (strrpos($class, '_') + 1));
            } else if (strpos($class, '\\') !== false) {
                $cls = substr($class, (strrpos($class, '\\') + 1));
            } else {
                $cls = $class;
            }
            $this->tableName = $this->prefix . String::camelCaseToUnderscore($cls);
        } else {
            $this->tableName = $this->prefix . $this->tableName;
        }

        $options = array(
            'tableName' => $this->tableName,
            'primaryId' => $this->primaryId,
            'auto'      => $this->auto,
            'idQuote'   => $this->idQuote
        );

        $type = self::getDb()->getAdapterType();

        if (($type == 'Mysql') || (!$this->usePrepared)) {
            $this->interface = new Escaped(self::getDb(), $options);
        } else {
            $this->interface = new Prepared(self::getDb(), $options);
        }
    }

    /**
     * Set DB connection
     *
     * @param  Db      $db
     * @param  boolean $isDefault
     * @return void
     */
    public static function setDb(Db $db, $isDefault = false)
    {
        $class = get_called_class();

        static::$db[$class] = $db;
        if (($isDefault) || ($class === __CLASS__)) {
            static::$db['default'] = $db;
        }
    }

    /**
     * Get DB connection
     *
     * @throws Exception
     * @return Pop\Pop\Db
     */
    public static function getDb()
    {
        $class = get_called_class();

        if (isset(static::$db[$class])) {
            return static::$db[$class];
        } else if (isset(static::$db['default'])) {
            return static::$db['default'];
        } else {
            throw new Exception('No database adapter was found.');
        }
    }

    /**
     * Find a database row by the primary ID passed through the method argument.
     *
     * @param  mixed $id
     * @param  int   $limit
     * @throws Exception
     * @return Pop\Record\Record
     */
    public static function findById($id, $limit = null)
    {
        $record = new static();
        $record->interface->findById($id, $limit);
        $record->setResults($record->interface->getResult());

        return $record;
    }

    /**
     * Find a database row by the column passed through the method argument.
     *
     * @param  string $column
     * @param  int|string $value
     * @param  int|string $limit
     * @return Pop\Record\Record
     */
    public static function findBy($column, $value, $limit = null)
    {
        $record = new static();
        $record->interface->findBy($column, $value, $limit);
        $record->setResults($record->interface->getResult());

        return $record;
    }


    /**
     * Find all of the database rows by the column passed through the method argument.
     *
     * @param  string     $order
     * @param  string     $column
     * @param  int|string $value
     * @param  int|string $limit
     * @return Pop\Record\Record
     */
    public static function findAll($order = null, $column = null, $value = null, $limit = null)
    {
        $record = new static();
        $record->interface->findAll($order, $column, $value, $limit);
        $record->setResults($record->interface->getResult());

        return $record;
    }

    /**
     * Find singular and distinct entries in the database based on the search criteria.
     *
     * @param  array $distinctColumns
     * @param  string $order
     * @param  string $column
     * @param  int|string $value
     * @param  int|string $limit
     * @return Pop\Record\Record
     */
    public static function distinct($distinctColumns, $order = null, $column = null, $value = null, $limit = null)
    {
        $record = new static();
        $record->interface->distinct($distinctColumns, $order, $column, $value, $limit);
        $record->setResults($record->interface->getResult());

        return $record;
    }

    /**
     * Search the database for rows based on the search criteria.
     *
     * @param  array $searchColumns
     * @param  string $order
     * @param  int|string $limit
     * @return Pop\Record\Record
     */
    public static function search($searchColumns, $order = null, $limit = null)
    {
        $record = new static();
        $record->interface->search($searchColumns, $order, $limit);
        $record->setResults($record->interface->getResult());

        return $record;
    }

    /**
     * Join data from two tables that share a related column.
     *
     * @param  string $tableToJoin
     * @param  string $commonColumn
     * @param  string $order
     * @param  string $column
     * @param  int|string $value
     * @param  int|string $limit
     * @return Pop\Record\Record
     */
    public static function join($tableToJoin, $commonColumn, $order = null, $column = null, $value = null, $limit = null)
    {
        $record = new static();
        $record->interface->join($tableToJoin, $commonColumn, $order, $column, $value, $limit);
        $record->setResults($record->interface->getResult());

        return $record;
    }

    /**
     * Execute a custom prepared SQL query.
     *
     * @param  string $sql
     * @param  array  $params
     * @return Pop\Record\Record
     */
    public static function execute($sql, $params = null)
    {
        $record = new static();
        $record->interface->execute($sql, $params);
        $record->setResults($record->interface->getResult());

        return $record;
    }

    /**
     * Execute a custom SQL query.
     *
     * @param  string $sql
     * @return Pop\Record\Record
     */
    public static function query($sql)
    {
        $record = new static();
        $record->interface->query($sql);
        $record->setResults($record->interface->getResult());

        return $record;
    }

    /**
     * Get if the table is an autocrement table
     *
     * @return boolean
     */
    public function isAuto()
    {
        return $this->auto;
    }

    /**
     * Get the table primary ID
     *
     * @return mixed
     */
    public function getId()
    {
        return $this->primaryId;
    }

    /**
     * Get the table prefix
     *
     * @return string
     */
    public function getPrefix()
    {
        return $this->prefix;
    }

    /**
     * Get the table name
     *
     * @return string
     */
    public function getTableName()
    {
        if (null !== $this->prefix) {
            return str_replace($this->prefix, '', $this->tableName);
        } else {
            return $this->tableName;
        }
    }

    /**
     * Method to return the current number of records.
     *
     * @return int
     */
    public function count()
    {
        return count($this->rows);
    }

    /**
     * Set all the table column values at once.
     *
     * @param  array $columns
     * @throws Exception
     * @return void
     */
    public function setValues($columns = null)
    {
        // If null, clear the columns.
        if (null === $columns) {
            $this->columns = array();
            $this->rows = array();
        // Else, if an array, set the columns.
        } else if (is_array($columns)) {
            $this->columns = $columns;
            $this->rows[0] = new \ArrayObject($columns, \ArrayObject::ARRAY_AS_PROPS);
        // Else, throw an exception.
        } else {
            throw new Exception('The parameter passed must be either an array or null.');
        }
    }

    /**
     * Get all the table column values at once as an associative array.
     *
     * @return array
     */
    public function getValues()
    {
        return (array)$this->columns;
    }

    /**
     * Update (save) the existing database record.
     *
     * @return void
     */
    public function update()
    {
        $this->save(self::UPDATE);
    }

    /**
     * Save the database record.
     *
     * @param  int $type
     * @return void
     */
    public function save($type = Record::INSERT)
    {
        $this->interface->save($this->columns, $type);
        $this->setResults($this->interface->getResult());
    }

    /**
     * Delete the database record.
     *
     * @param  string $column
     * @param  string $value
     * @throws Exception
     * @return void
     */
    public function delete($column = null, $value = null)
    {
        $this->interface->delete($this->columns, $column, $value);
        $this->setResults($this->interface->getResult());
    }

    /**
     * Return the escaped string value.
     *
     * @param  string $value
     * @return string
     */
    public function escape($value)
    {
        return $this->interface->db->adapter()->escape($value);
    }

    /**
     * Return the auto-increment ID of the last query.
     *
     * @return int
     */
    public function lastId()
    {
        return $this->interface->db->adapter()->lastId();
    }

    /**
     * Return the number of rows in the result.
     *
     * @return int
     */
    public function numRows()
    {
        return $this->interface->db->adapter()->numRows();
    }

    /**
     * Return the number of fields in the result.
     *
     * @return int
     */
    public function numFields()
    {
        return $this->interface->db->adapter()->numFields();
    }

    /**
     * Set the query results.
     *
     * @param  array $result
     * @return void
     */
    protected function setResults($result)
    {
        $this->rows = $result['rows'];
        $this->columns = $result['columns'];
    }

    /**
     * Set method to set the property to the value of _columns[$name].
     *
     * @param  string $name
     * @param  mixed $value
     * @return void
     */
    public function __set($name, $value)
    {
        $this->columns[$name] = $value;
    }

    /**
     * Get method to return the value of _columns[$name].
     *
     * @param  string $name
     * @return mixed
     */
    public function __get($name)
    {
        return (isset($this->columns[$name])) ? $this->columns[$name] : null;
    }

    /**
     * Return the isset value of _columns[$name].
     *
     * @param  string $name
     * @return boolean
     */
    public function __isset($name)
    {
        return isset($this->columns[$name]);
    }

    /**
     * Unset _columns[$name].
     *
     * @param  string $name
     * @return void
     */
    public function __unset($name)
    {
        $this->columns[$name] = null;
    }

}
