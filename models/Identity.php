<?php declare(strict_types=1);

use Jasny\DB\Entity\Identifiable;
use Jasny\DB\EntitySet;

/**
 * Identity entity
 */
class Identity extends MongoDocument implements Identifiable
{    
    /**
     * Unique identifier
     * @var string
     */
    public $id;
    
    /**
     * Person / organization info
     * @var \stdClass
     */
    public $info;

    /**
     * Live contracts node the identity is using
     * @var string
     * @required
     */
    public $node;
    
    /**
     * Name of the identity
     * @var string
     */
    public $name;
    
    /**
     * Email address of the identity
     * @var string
     */
    public $email;
    
    /**
     * Cryptographic (ED25519) public keys used in signing
     * @var array
     */
    public $signkeys = [];
    
    /**
     * Cryptographic (X25519) public key used for encryption
     * @var string
     */
    public $encryptkey;
    
    /**
     * Get id property
     *
     * @return string
     */
    public static function getIdProperty(): string
    {
        return 'id';
    }
}
