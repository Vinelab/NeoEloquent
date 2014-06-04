<?php namespace Vinelab\NeoEloquent\Eloquent;

use Illuminate\Database\Eloquent\SoftDeletingTrait as IlluminateSoftDeletingTrait;

trait SoftDeletingTrait {

    use IlluminateSoftDeletingTrait;

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
