<?php namespace Vinelab\NeoEloquent\Eloquent;

use Illuminate\Database\Eloquent\SoftDeletes as IlluminateSoftDeletes;

trait SoftDeletes {

    use IlluminateSoftDeletes;

    /**
     * Get the fully qualified "deleted at" column.
     *
     * @return string
     */
    public function getQualifiedDeletedAtColumn()
    {
        return $this->getDeletedAtColumn();
    }
}
