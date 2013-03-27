<?php
/**
 * @namespace
 */
namespace Phire\Table;

use Pop\Db\Record;

class Types extends Record
{

    /**
     * @var   string
     */
    protected $primaryId = 'id';

    /**
     * @var   boolean
     */
    protected $auto = true;

    /**
     * @var   string
     */
    protected $prefix = DB_PREFIX;

    /**
     * Delete the database record.
     *
     * @param  array $columns
     * @return void
     */
    public function delete(array $columns = null)
    {
        $sql = self::getSql();

        // If SQL Server, a little extra clean up required
        // to set the users.type_id to null on delete
        if ($sql->getDbType() == 5) {
            $sql->setTable(DB_PREFIX . 'users')
                ->update(array('type_id' => null))
                ->where()
                ->equalTo('type_id', '?');

            self::execute($sql->render(true), array('type_id' => $this->id));
        }

        parent::delete($columns);
    }

}

