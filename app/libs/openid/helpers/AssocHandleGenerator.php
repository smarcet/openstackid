<?php
/**
 * Created by PhpStorm.
 * User: smarcet
 * Date: 10/26/13
 * Time: 6:09 PM
 */

namespace openid\helpers;
use Zend\Math\Rand;

/**
 * Class AssocHandleGenerator
 *  The association handle is used as a key to refer to this association in subsequent messages.
 * A string 255 characters or less in length. It MUST consist only of ASCII characters in the
 * range 33-126 inclusive (printable non-whitespace characters).
 * @package openid\helpers
 */
class AssocHandleGenerator {

    const PrintableNonWhitespaceCharacters ='!"#$%&\'()*+,-./0123456789:;<=>?@ABCDEFGHIJKLMNOPQRSTUVWXYZ[\]^_`abcdefghijklmnopqrstuvwxyz{|}~';

    /**
     * @param int $len
     * @return string
     * @throws \InvalidArgumentException
     */
    public static function generate($len = 32){
        if($len>255) throw new \InvalidArgumentException(sprintf("assoc handle len must be lower or equal to 255(%d)",$len));
        $new_assoc_handle = Rand::getString($len,self::PrintableNonWhitespaceCharacters,true);
        return $new_assoc_handle;
    }
} 