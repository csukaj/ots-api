<?php
namespace App\Relations;

/**
 * Relation for displaying Device names from organization
 */
class DevicesJSONRelation extends DevicesRelation
{

    protected $type = self::TYPE_ONE_TO_MANY_KEYS;
    protected $format = self::FORMAT_JSON;

}
