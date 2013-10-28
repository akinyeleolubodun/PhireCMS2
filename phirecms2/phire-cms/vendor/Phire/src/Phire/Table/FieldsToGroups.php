<?php
/**
 * @namespace
 */
namespace Phire\Table;

use Pop\Db\Record;

class FieldsToGroups extends Record
{

    /**
     * @var   string
     */
    protected $tableName = 'fields_to_groups';

    /**
     * @var   string
     */
    protected $primaryId = array('field_id', 'group_id');

    /**
     * @var   boolean
     */
    protected $auto = false;

    /**
     * @var   string
     */
    protected $prefix = DB_PREFIX;

    /**
     * Static method to get a field group from a field ID
     *
     * @param int    $fid
     * @return array
     */
    public static function getFieldGroup($fid)
    {
        $groupAry = array(
            'group_id' => null,
            'fields'   => array(),
            'order'    => 0,
            'dynamic'  => false
        );

        $f2g = static::findBy(array('field_id' => $fid));
        if (isset($f2g->field_id)) {
            $sql = static::getSql();

            $sql->select()->where()->equalTo('group_id', ':group_id');
            $sql->select()->join(DB_PREFIX . 'field_groups', array('group_id', 'id'), 'LEFT JOIN');

            $group = static::execute($sql->render(true), array('group_id' => $f2g->group_id));
            if (isset($group->rows[0])) {
                foreach ($group->rows as $grp) {
                    $groupAry['group_id'] = $grp->group_id;
                    $groupAry['fields'][] = $grp->field_id;
                    $groupAry['order']    = $grp->order;
                    $groupAry['dynamic']  = $grp->dynamic;
                }
            }
        }

        return $groupAry;
    }

}

