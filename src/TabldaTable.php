<?php

namespace Tablda\DataReceiver;

use Illuminate\Database\Eloquent\Model;
use Sofa\Eloquence\Eloquence;
use Sofa\Eloquence\Mappable;

class TabldaTable extends Model
{
    use Eloquence, Mappable;

    static protected $s_maps = [];

    protected $maps = [];

    /**
     * TabldaTable constructor.
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->applyMaps();
    }

    /**
     * Set static 'maps' and apply.
     *
     * @param array $maps
     * @return $this
     */
    public function setMaps(array $maps)
    {
        static::$s_maps = $maps;
        $this->applyMaps();

        return $this;
    }

    /**
     * Apply active static 'maps' to current Model.
     */
    private function applyMaps()
    {
        $this->maps = static::$s_maps;
        $this->setAppends(array_keys($this->maps));
        $this->setVisible(array_keys($this->maps));
    }
}
