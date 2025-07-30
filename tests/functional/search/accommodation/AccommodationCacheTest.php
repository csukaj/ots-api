<?php

namespace Tests\Functional\Search\Accommodation;

use App\Accommodation;
use App\Entities\Search\AccommodationSearchEntity;
use Tests\TestCase;

class AccommodationCacheTest extends TestCase
{

    static public $setupMode = self::SETUPMODE_ONCE;
    public $organizationId = 1;

    /**
     * @test
     */
    function it_can_be_queried_from_cache()
    {
        $accommodationSearchEntity = new AccommodationSearchEntity();
        $actual = $accommodationSearchEntity->getSerializedOrganization($this->organizationId);
        $this->assertEquals($this->organizationId, $actual['id']);
    }

    /**
     * @test
     */
    function cache_automatically_updated_when_accommodation_updated()
    {
        $accommodationSearchEntity = new AccommodationSearchEntity();
        $actual = $accommodationSearchEntity->getSerializedOrganization($this->organizationId, true);
        $this->assertTrue($actual['is_active']);
        $accommodation = Accommodation::find($this->organizationId);
        $accommodation->is_active = false;
        $accommodation->save();
        $actual = $accommodationSearchEntity->getSerializedOrganization($this->organizationId);
        $this->assertFalse($actual['is_active']);
    }

    /**
     * @test
     */
    function accommodation_updated_when_some_data_updated()
    {
        $accommodation = Accommodation::find($this->organizationId);
        $updatedAtBefore = $accommodation->updated_at;
        $accommodation->is_active = false;
        $accommodation->save();
        $this->assertGreaterThan($updatedAtBefore, $accommodation->updated_at);
    }

    /**
     * @test
     */
    function accommodation_updated_when_name_or_desc_updated()
    {
        $this->markTestIncomplete('Organization update made by organizationsetter so test modification with it');
        //name, short, long desc
        $accommodation = Accommodation::find($this->organizationId);
        $updatedAtBefore = $accommodation->updated_at;
        $name = $accommodation->name;
        $name->description = 'Modified';
        $name->save();
        //$this->assertGreaterThan($updatedAtBefore, Accommodation::find($this->organizationId)->updated_at);

        $updatedAtBefore = Accommodation::find($this->organizationId)->updated_at;
        $desc = $accommodation->descriptions[0]->description;
        $desc->description = 'ModifiedDesc';
        $desc->save();
        $this->assertGreaterThan($updatedAtBefore, Accommodation::find($this->organizationId)->updated_at);
    }

    /**
     * @test
     */
    function accommodation_updated_when_some_property_updated()
    {
        $accommodation = Accommodation::find($this->organizationId);
        $updatedAtBefore = $accommodation->updated_at;
        $meta = $accommodation->metas[0];
        $meta->value = 'ModifiedMeta';
        $meta->save();
        $this->assertGreaterThan($updatedAtBefore, Accommodation::find($this->organizationId)->updated_at);
    }

    /**
     * @test
     */
    function accommodation_updated_when_some_age_range_updated()
    {
        $accommodation = Accommodation::find($this->organizationId);
        $updatedAtBefore = $accommodation->updated_at;
        $ageRange = $accommodation->ageRanges[0];
        $ageRange->to_age = 1;
        $ageRange->save();
        $this->assertGreaterThan($updatedAtBefore, Accommodation::find($this->organizationId)->updated_at);
    }

    /**
     * @test
     */
    function accommodation_updated_when_some_period_updated()
    {
        $accommodation = Accommodation::find($this->organizationId);
        $updatedAtBefore = $accommodation->updated_at;
        $ageRange = $accommodation->dateRanges()->open()->orderBy('from_time')->first();
        $ageRange->from_time = '2015-01-12';
        $ageRange->save();
        $this->assertGreaterThan($updatedAtBefore, Accommodation::find($this->organizationId)->updated_at);
    }

    /**
     * @test
     */
    function accommodation_updated_when_some_device_updated()
    {
        $accommodation = Accommodation::find($this->organizationId);
        $updatedAtBefore = $accommodation->updated_at;
        $deviceName = $accommodation->devices[0];
        $deviceName->amount = 23;
        $deviceName->save();
        $this->assertGreaterThan($updatedAtBefore, Accommodation::find($this->organizationId)->updated_at);
    }

    /**
     * @test
     */
    function accommodation_updated_when_some_device_minimum_nights_updated()
    {
        $accommodation = Accommodation::find($this->organizationId);
        $updatedAtBefore = $accommodation->updated_at;
        $minNights = $accommodation->devices[0]->minimumNights[0];
        $minNights->minimum_nights = 99;
        $minNights->save();
        $this->assertGreaterThan($updatedAtBefore, Accommodation::find($this->organizationId)->updated_at);
    }

    /**
     * @test
     */
    function accommodation_updated_when_some_availability_updated()
    {
        $accommodation = Accommodation::find($this->organizationId);
        $updatedAtBefore = $accommodation->updated_at;
        $availability = $accommodation->devices[0]->availabilities[0];
        $availability->amount = 99;
        $availability->save();
        $this->assertGreaterThan($updatedAtBefore, Accommodation::find($this->organizationId)->updated_at);
    }

    /**
     * @test
     */
    function accommodation_updated_when_some_price_updated()
    {
        $accommodation = Accommodation::find($this->organizationId);
        $updatedAtBefore = $accommodation->updated_at;
        $price_element = $accommodation->devices[0]->products[0]->prices[0]->elements[0];
        $price_element->net_price = 99;
        $price_element->save();
        $this->assertGreaterThan($updatedAtBefore, Accommodation::find($this->organizationId)->updated_at);
    }

}
