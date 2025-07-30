<?php
namespace Tests\Procedure\Ots\Offer;

use App\Organization;
use App\PriceModifier;
use App\PriceModifierClassification;
use App\PriceModifierMeta;
use Illuminate\Support\Facades\DB;
use Tests\Procedure\ProcedureTestCase;

class OfferTestCase extends ProcedureTestCase
{

    static public $setupMode = self::SETUPMODE_ONCE;
    protected $imports;
    protected $priceModifierName = 'Free Nights Offer';
    protected $organizationId = 1;
    protected $priceModifierClass;
    protected $models;

    private function prepareRoomSearch($organizationId = 1, $request = [], $fromDate = null, $toDate = null, $bookingDate = null, $weddingDate = null)
    {
      
        $params = json_encode([
            'request' =>$request,
            'interval' =>[
                'date_from' => $fromDate,
                'date_to' => $toDate
            ],
            'booking_date' =>$bookingDate,
            'wedding_date' =>$weddingDate,
        ]);

        return "RoomSearch(plpy=plpy_mocker, organization_id={$organizationId}, params='{$params}')";
    }

    protected function getRooms($organizationId, $request, $fromDate, $toDate, $bookingDate, $weddingDate = null, $additionalHeader = null)
    {
        $roomSearch = $this->prepareRoomSearch($organizationId, $request, $fromDate, $toDate, $bookingDate, $weddingDate);

        $result = $this->runPythonScript($roomSearch);
        $script = !empty($additionalHeader) ? $additionalHeader . PHP_EOL : '';
        $script .= "from json import dumps" . PHP_EOL;
        $script .= "import jsonpickle" . PHP_EOL;
        $script .= "from ots.search.room_search import RoomSearch" . PHP_EOL;
        $script .= "print jsonpickle.encode({$roomSearch}.get_rooms())" . PHP_EOL;

        $result = $this->runPythonScript($script);
        $decoded = \json_decode($result, true);
        return !empty($decoded) ? $decoded : $result;
    }

    protected function prepareTests()
    {
        $this->imports = 'from ots.price_modifier.price_modifier import PriceModifier' . PHP_EOL .
            'from ots.pricing.room_price_search import RoomPriceSearch' . PHP_EOL .
            'from ots.search.room_search import RoomSearch' . PHP_EOL .
            'from json import loads, dumps' . PHP_EOL;

        $priceModifier = $this->getExclusivePriceModifier($this->priceModifierName, $this->organizationId);
        $this->imports .= $this->setupExclusivePricemodifierPythonString($this->priceModifierName, $this->organizationId) . PHP_EOL;
        $properties = DB::select('SELECT 
                "price_modifiers"."id", 
                "price_modifiers"."name_description_id", 
                "price_modifiers"."modifier_type_taxonomy_id", 
                "price_modifiers"."condition_taxonomy_id", 
                "price_modifiers"."offer_taxonomy_id", 
                "price_modifiers"."priority", 
                "price_modifiers"."description_description_id", 
                "condition_tx"."parent_id" AS "application_level_taxonomy_id",
                -- --
    (
    SELECT array_to_json(array_agg(row_to_json(d)))
           FROM (
               SELECT
                    price_modifier_metas.taxonomy_id,
                    price_modifier_metas.value
                  FROM price_modifier_metas
                  WHERE price_modifier_metas.price_modifier_id = price_modifiers.id AND
    price_modifier_metas.deleted_at IS NULL
                ) d
         ) AS type_metas,
         (
         SELECT array_to_json(array_agg(row_to_json(d)))
           FROM (
               SELECT value_taxonomy_id
                  FROM price_modifier_classifications
                  WHERE price_modifier_classifications.price_modifier_id = price_modifiers.id AND
    price_modifier_classifications.deleted_at IS NULL
                ) d
         ) AS type_classifications,
         (
         SELECT array_to_json(array_agg(row_to_json(d)))
           FROM (
               SELECT
                    taxonomy_id,
                    value
                  FROM offer_metas
                  WHERE price_modifier_id = price_modifiers.id AND deleted_at IS NULL
                ) d
         ) AS offer_metas,
         (
         SELECT array_to_json(array_agg(row_to_json(d)))
           FROM (
               SELECT value_taxonomy_id
                  FROM offer_classifications
                  WHERE price_modifier_id = price_modifiers.id AND deleted_at IS NULL
                ) d
         ) AS offer_classifications
    -- --
    FROM "price_modifiers"
                INNER JOIN "taxonomies" AS "condition_tx" ON "condition_tx"."id" = "price_modifiers"."condition_taxonomy_id"
                WHERE "price_modifiers"."id" ='.
            $priceModifier->id
            .' 
                AND "price_modifiers"."deleted_at" IS NULL
    AND "price_modifiers"."is_active"
                ORDER BY "price_modifiers"."priority" ASC')
        ;
        $priceModifierAttributes = $properties[0];
        $priceModifierJson = str_replace(array(':true', ':false', ':null'), array(':True', ':False', ':None'), json_encode($priceModifierAttributes));
        $organization = Organization::findOrFail($this->organizationId);
        $device = $organization->devices[0];
        $deviceUsage = $device->usages[0];
        $this->models = [$priceModifier, $priceModifierJson, $organization, $device, $deviceUsage];

        return $this->models;
    }

    protected function getExclusivePriceModifier($priceModifierName, $organizationId)
    {
        return PriceModifier::findByName($priceModifierName, Organization::class, $organizationId);
    }

    protected function setupExclusivePricemodifierPythonString($priceModifierName, $organizationId)
    {
        $priceModifier = $this->getExclusivePriceModifier($priceModifierName, $organizationId);
        return "plpy_mocker.cursor.execute('UPDATE price_modifiers SET deleted_at= NOW() WHERE id != " . $priceModifier->id . "')" . PHP_EOL;
    }

    protected function underscoredToCamelcase($string)
    {
        $parts = explode('_', $string);
        return implode('', array_map('ucfirst', $parts));
    }

    protected function getMetaModifierScript($priceModifierId, $offerMetaTxId, $metaValue)
    {
        return "plpy_mocker.cursor.execute(\"UPDATE offer_metas SET value='{$metaValue}'::TEXT WHERE price_modifier_id=" . $priceModifierId . " AND taxonomy_id=" . $offerMetaTxId . "\")" . PHP_EOL;
    }
}
