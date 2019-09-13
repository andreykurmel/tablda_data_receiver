<?php

namespace Tablda\DataReceiver;

use Illuminate\Database\Eloquent\Model;
use Sofa\Eloquence\Eloquence;
use Sofa\Eloquence\Mappable;

class TabldaTable extends Model
{
    use Eloquence, Mappable;

    public $timestamps = false;

    protected $maps = [];

    /**
     * Set static 'maps' and apply.
     *
     * @param array $maps
     * @return $this
     */
    public function setMaps(array $maps)
    {
        $this->maps = $maps;
        $this->setAppends(array_keys($this->maps));
        $this->setVisible(array_keys($this->maps));

        return $this;
    }

    /**
     * Get Model's maps.
     *
     * @return array
     */
    public function getMaps()
    {
        return $this->maps;
    }

}
