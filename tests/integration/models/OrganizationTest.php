<?php

namespace Tests\Integration\Models;

use App\Facades\Config;
use App\Organization;
use App\OrganizationClassification;
use App\OrganizationManager;
use App\User;
use Illuminate\Support\Facades\DB;
use Modules\Stylerstaxonomy\Entities\Description;
use Modules\Stylerstaxonomy\Manipulators\DescriptionSetter;
use Tests\TestCase;

class OrganizationTest extends TestCase
{

    static public $setupMode = self::SETUPMODE_ONCE;

    private function prepare_an_organization_model(): Organization
    {
        $description = new Description(['description' => $descDesc = $this->faker->word]);
        $this->assertTrue($description->save());

        $orgType = Config::get('taxonomies.organization_types.accommodation');
        $org = new Organization();
        $org->name_description_id = $description->id;
        $org->type_taxonomy_id = $orgType['id'];
        $org->save();

        return $org;
    }

    /**
     * @test
     */
    function it_can_be_created()
    {
        $desc = new Description(['description' => $descDesc = $this->faker->word]);
        $this->assertTrue($desc->save());

        $orgType = Config::get('taxonomies.organization_types.accommodation');

        $org = new Organization();
        $org->name_description_id = $desc->id;
        $org->type_taxonomy_id = $orgType['id'];
        $this->assertTrue($org->save());
        $this->assertEquals($descDesc, $org->name->description);
    }

    /**
     * @test
     */
    function it_can_be_active()
    {
        $org = $this->prepare_an_organization_model();

        $org->is_active = true;
        $org->save();
        $this->assertTrue($org->is_active);

        $org->is_active = false;
        $org->save();
        $this->assertFalse($org->is_active);
    }

    /**
     * @test
     */
    function it_has_hierarchy()
    {
        $orgParent = new Organization();
        $orgParent->name_description_id = (new DescriptionSetter(['en' => $this->faker->word]))->set()->id;
        $orgParent->type_taxonomy_id = Config::getOrFail('taxonomies.organization_types.hotel_chain.id');
        $orgParent->is_active = true;
        $orgParent->save();

        $orgChild = $this->prepare_an_organization_model();
        $orgChild->is_active = true;
        $orgChild->parentable_type = Organization::class;
        $orgChild->parentable_id = $orgParent->id;
        $orgChild->save();

        $this->assertEquals($orgParent->id, $orgChild->parentOrganization->id);
        $this->assertNotEmpty($orgParent->children);
        $this->assertEquals($orgChild->id, $orgParent->children->first()->id);
    }

    /**
     * @test
     */
    function it_can_have_managers()
    {
        $org = $this->prepare_an_organization_model();

        $user = new User();
        $user->email = $this->faker->email;
        $user->name = $this->faker->name;
        $password = $this->faker->word;
        $user->password = $password;
        $user->save();

        $orgManager = new OrganizationManager();
        $orgManager->organization_id = $org->id;
        $orgManager->user_id = $user->id;
        $this->assertTrue($orgManager->save());

        $this->assertEquals($user->id, $org->managers[0]->user->id);
        $this->assertEquals($org->id, $user->organizations[0]->id);
    }

    /**
     * @test
     */
    function availability_mode_can_be_set()
    {
        $org = $this->prepare_an_organization_model();

        $orgCl = $org->setClassification(
            Config::get('taxonomies.organization_properties.categories.settings.items.availability_mode.id'), Config::get('taxonomies.organization_properties.categories.settings.items.availability_mode.elements.binary')
        );

        $this->assertTrue(!!$orgCl->id);
        $this->assertEquals($org->id, $orgCl->organization_id);
        $this->assertEquals(Config::get('taxonomies.organization_properties.categories.settings.items.availability_mode.id'), $orgCl->classification_taxonomy_id);
        $this->assertEquals(Config::get('taxonomies.organization_properties.categories.settings.items.availability_mode.elements.binary'), $orgCl->value_taxonomy_id);
    }

    /**
     * @test
     */
    function availability_mode_can_be_queried()
    {
        $org = $this->prepare_an_organization_model();

        $orgClSet = $org->setClassification(Config::get('taxonomies.organization_properties.categories.settings.items.availability_mode.id'), Config::get('taxonomies.organization_properties.categories.settings.items.availability_mode.elements.binary'));
        $orgClGot = $org->getAvailabilityMode();

        $this->assertEquals($orgClSet->id, $orgClGot->id);
        $this->assertEquals($orgClSet->organization_id, $orgClGot->organization_id);
        $this->assertEquals($orgClSet->classification_taxonomy_id, $orgClGot->classification_taxonomy_id);
        $this->assertEquals($orgClSet->value_taxonomy_id, $orgClGot->value_taxonomy_id);
    }

    /**
     * @test
     */
    function it_can_have_pricing_logic_and_margin_type()
    {
        $org = $this->prepare_an_organization_model();

        $org->pricing_logic_taxonomy_id = Config::get('taxonomies.pricing_logics.from_rack_price');
        $org->margin_type_taxonomy_id = Config::get('taxonomies.margin_types.value');
        $this->assertTrue($org->save());
        $this->assertEquals(Config::get('taxonomies.pricing_logics.from_rack_price'), $org->pricing_logic_taxonomy_id);
        $this->assertEquals(Config::get('taxonomies.margin_types.value'), $org->margin_type_taxonomy_id);
    }

    /**
     * @test
     */
    function it_can_have_classification()
    {
        $org = $this->prepare_an_organization_model();

        $notEmptyOrgCls = array_filter(Config::get('taxonomies.organization_properties.categories'), function ($itm) {
            $itemCount = (bool)count(isset($itm['items']) ? $itm['items'] : []);
            if (!$itemCount) {
                return false;
            }
            $itm['items'] = array_filter($itm['items'], function ($itm2) {
                return (bool)count(isset($itm2['elements']) ? $itm2['elements'] : []);
            });
            return (bool)count($itm['items']);
        });

        $orgClCategory = $notEmptyOrgCls[array_rand($notEmptyOrgCls)];
        foreach ($orgClCategory['items'] as $orgCl) {
            if (!empty($orgCl['elements'])) {
                foreach ($orgCl['elements'] as $orgClValue) {
                    $clObj = $org->setClassification($orgCl['id'], $orgClValue);
                    $clReturnObj = $org->getClassification($orgCl['id']);

                    $this->assertInstanceOf(OrganizationClassification::class, $clObj);
                    if (!is_null($clReturnObj)) {
                        $this->assertInstanceOf(OrganizationClassification::class, $clReturnObj);
                        $this->assertEquals($orgClValue, $clReturnObj->value_taxonomy_id);
                    }
                }
            }
        }
    }

    /**
     * @test
     */
    function it_can_return_organizations_by_name()
    {
        $actual = TestCase::getOrganizationsByName('Hotel A');
        $this->assertEquals(Organization::class, get_class($actual[0]));
        $this->assertEquals(1, $actual[0]->id);
    }

    /**
     * @test
     */
    function it_can_get_last_update()
    {
        $this->assertEquals(DB::table('organizations')->max('updated_at'), Organization::getLastUpdate());
    }

    /**
     * @test
     * @throws \Exception
     */
    function it_can_getChannelManagedOrganizationIds()
    {
        $channelManagerTaxonomyId = Config::getOrFail('taxonomies.organization_properties.categories.settings.items.channel_manager.elements.Hotel Link Solutions');
        $actual = Organization::getChannelManagedOrganizationIds($channelManagerTaxonomyId);
        $this->assertEquals([21], $actual);
    }
}
