<?php

namespace Vinelab\NeoEloquent\Migrations;

use Vinelab\NeoEloquent\Eloquent\Model as NeoEloquent;

class MigrationModel extends NeoEloquent
{
    /**
     * {@inheritdoc}
     */
    protected $label = 'NeoEloquentMigration';

    /**
     * {@inheritdoc}
     */
    protected $fillable = array(
        'migration',
        'batch',
    );

    /**
     * {@inheritdoc}
     */
    protected $guarded = array();
}
