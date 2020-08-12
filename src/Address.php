<?php

namespace kornrunner\Ethereum;

use InvalidArgumentException;
use kornrunner\Keccak;
use Mdanter\Ecc\EccFactory;
use Mdanter\Ecc\Crypto\Key\PrivateKeyInterface;
use Mdanter\Ecc\Serializer\PublicKey\DerPublicKeySerializer;

class Address
{

    public function __construct($privateKey = '')
    {
        $generator = EccFactory::getSecgCurves()->generator256k1();
        if (empty ($privateKey)) {
            $this->privateKey = $generator->createPrivateKey();
        } else {
            if (substr($privateKey, 0, 2) == '0x') {
                $privateKey = substr($privateKey, 2);
            }
            if (!ctype_xdigit($privateKey)) {
                throw new InvalidArgumentException('Private key must be a hexadecimal number');
            }
            if (strlen($privateKey) != 64) {
                throw new InvalidArgumentException('Private key should be exactly 64 chars long');
            }

            $key = gmp_init($privateKey, 16);
            $this->privateKey = $generator->getPrivateKeyFrom($key);
        }
    }

    public function getPrivateKey()
    {
        return '0x' . gmp_strval($this->privateKey->getSecret(), 16);
    }

    public function getPublicKey()
    {
        $publicKey = $this->privateKey->getPublicKey();
        $publicKeySerializer = new DerPublicKeySerializer(EccFactory::getAdapter());
        return '0x' . substr($publicKeySerializer->getUncompressedKey($publicKey), 2);
    }

    public function get()
    {
        $hash = Keccak::hash(hex2bin($this->getPublicKey()), 256);
        return '0x' . substr($hash, -40);
    }

    /**
     * @var PrivateKeyInterface
     */
    private $privateKey;
}