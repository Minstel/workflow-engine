<?php

use Jasny\DB\Entity\Dynamic;

/**
 * Storable data
 */
class Asset extends BasicEntity implements Dynamic
{
    use DeepClone;

    /**
     * @var string
     */
    public $schema = 'https://specs.livecontracts.io/v0.2.0/asset/schema.json#';

    /**
     * @var string
     */
    public $key;
}
