<?php
/**
 * @namespace
 */
namespace Phire\Table;

use Pop\Db\Record;

class UserSessions extends Record
{

    /**
     * @var   string
     */
    protected $tableName = 'user_sessions';

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
     * Method is see if the session has expired.
     *
     * @param  int  $exp
     * @return boolean
     */
    public function hasExpired($exp)
    {
        $expired = false;

        if ((time() - strtotime($this->last)) > ($exp * 60)) {
            $expired = true;
        }

        return $expired;
    }

    /**
     * Static method to clear sessions
     *
     * @param  int $id
     * @return void
     */
    public static function clearSessions($id)
    {
        $sql = static::getSql();
        $sql->delete()
            ->where()->equalTo('user_id', ':user_id')
            ->lessThanOrEqualTo('last', ':last');

        static::execute(
            $sql->render(true),
            array(
                'user_id' => $id,
                'last' => date('Y-m-d H:i:s', time() - 86400)
            )
        );
    }

}

