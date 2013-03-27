<?php
/**
 * @namespace
 */
namespace Phire\Table;

use Pop\Db\Record;

class Sessions extends Record
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

}

