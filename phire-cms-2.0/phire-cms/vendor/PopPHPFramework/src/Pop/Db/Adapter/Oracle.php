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
 * @package    Pop_Db
 * @author     Nick Sagona, III <nick@popphp.org>
 * @copyright  Copyright (c) 2009-2012 Moc 10 Media, LLC. (http://www.moc10media.com)
 * @license    http://www.popphp.org/LICENSE.TXT     New BSD License
 */

/**
 * @namespace
 */
namespace Pop\Db\Adapter;

use Pop\Filter\String;

/**
 * This is the Oracle adapter class for the Db component.
 *
 * @category   Pop
 * @package    Pop_Db
 * @author     Nick Sagona, III <nick@popphp.org>
 * @copyright  Copyright (c) 2009-2012 Moc 10 Media, LLC. (http://www.moc10media.com)
 * @license    http://www.popphp.org/LICENSE.TXT     New BSD License
 * @version    1.0
 */
class Oracle extends AbstractAdapter
{

    /**
     * Prepared statement
     * @var Resource
     */
    protected $statement = null;

    /**
     * Constructor
     *
     * Instantiate the Oracle database connection object.
     *
     * @param  array $options
     * @throws Exception
     * @return void
     */
    public function __construct(array $options)
    {
        if (!isset($options['database']) || !isset($options['host']) || !isset($options['username']) || !isset($options['password'])) {
            throw new Exception('Error: The proper database credentials were not passed.');
        }

        $this->connection = oci_connect($options['username'], $options['password'], $options['host'] . '/' . $options['database']);

        if ($this->connection == false) {
            throw new Exception('Error: Could not connect to database. ' . oci_error());
        }
    }

    /**
     * Throw an exception upon a database error.
     *
     * @throws Exception
     * @return void
     */
    public function showError()
    {
        throw new Exception('Error: ' . oci_error($this->connection));
    }

    /**
     * Prepare a SQL query.
     *
     * @param  string $sql
     * @return Pop\Db\Adapter\Oracle
     */
    public function prepare($sql)
    {
        $this->statement = oci_parse($this->connection, $sql);
        return $this;
    }

    /**
     * Bind parameters to for a prepared SQL query.
     *
     * @param  array  $params
     * @return Pop\Db\Adapter\Oracle
     */
    public function bindParams($params)
    {
        foreach ($params as $key => $value) {
            ${$key} = $value;
            oci_bind_by_name($this->statement, ':' . $key, ${$key});
        }

        return $this;
    }

    /**
     * Fetch and return the values.
     *
     * @return array
     */
    public function fetchResult()
    {
        $rows = array();

        while (($row = $this->fetch()) != false) {
            $rows[] = $row;
        }

        return $rows;
    }

    /**
     * Execute the prepared SQL query.
     *
     * @throws Exception
     * @return void
     */
    public function execute()
    {
        if (null === $this->statement) {
            throw new Exception('Error: The database statement resource is not currently set.');
        }

        oci_execute($this->statement);
    }

    /**
     * Execute the SQL query and create a result resource, or display the SQL error.
     *
     * @param  string $sql
     * @return void
     */
    public function query($sql)
    {
        $this->statement = oci_parse($this->connection, $sql);
        if (!($this->result = oci_execute($this->statement))) {
            $this->showError();
        }
    }

    /**
     * Return the results array from the results resource.
     *
     * @throws Exception
     * @return array
     */
    public function fetch()
    {
        if (!isset($this->statement)) {
            throw new Exception('Error: The database result resource is not currently set.');
        }

        return oci_fetch_array($this->statement, OCI_RETURN_NULLS+OCI_ASSOC);
    }

    /**
     * Return the escaped string value.
     *
     * @param  string $value
     * @return string
     */
    public function escape($value)
    {
        return (string)String::factory($value)->escape(true);
    }

    /**
     * Return the auto-increment ID of the last query.
     *
     * @return int
     */
    public function lastId()
    {
        return null;
    }

    /**
     * Return the number of rows in the result.
     *
     * @throws Exception
     * @return int
     */
    public function numRows()
    {
        if (isset($this->statement)) {
            return oci_num_rows($this->statement);
        } else {
            throw new Exception('Error: The database result resource is not currently set.');
        }
    }

    /**
     * Return the number of fields in the result.
     *
     * @throws Exception
     * @return int
     */
    public function numFields()
    {
        if (isset($this->statement)) {
            return oci_num_fields($this->statement);
        } else {
            throw new Exception('Error: The database result resource is not currently set.');
        }
    }

    /**
     * Return the database version.
     *
     * @return string
     */
    public function version()
    {
        return oci_server_version($this->connection);
    }

    /**
     * Get an array of the tables of the database.
     *
     * @return array
     */
    protected function loadTables()
    {
        $tables = array();

        $this->query("SELECT TABLE_NAME FROM USER_TABLES");
        while (($row = $this->fetch()) != false) {
            foreach($row as $value) {
                $tables[] = $value;
            }
        }

        return $tables;
    }

    /**
     * Close the DB connection.
     *
     * @return void
     */
    public function __destruct()
    {
        oci_close($this->connection);
    }

}
