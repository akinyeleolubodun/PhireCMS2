<?php
/**
 * @namespace
 */
namespace Phire\Model;

use Phire\Table;

class Extension extends AbstractModel
{

    /**
     * Get all themes method
     *
     * @return void
     */
    public function getThemes()
    {

    }

    /**
     * Get all modules method
     *
     * @param  \Phire\Project $project
     * @return void
     */
    public function getModules(\Phire\Project $project)
    {
        $modules = $project->modules();
        unset($modules['Phire']);
        $this->data['modules'] = $modules;
    }

}

