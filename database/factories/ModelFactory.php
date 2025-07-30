<?php

use App\Accommodation;
use App\AgeRange;
use App\Cart;
use App\DateRange;
use App\Device;
use App\DeviceUsage;
use App\Entities\OrganizationEntity;
use App\Facades\Config;
use App\MealPlan;
use App\ModelMealPlan;
use App\Order;
use App\OrderItem;
use App\OrderItemGuest;
use App\Organization;
use App\OrganizationManager;
use App\Price;
use App\PriceElement;
use App\Product;
use App\Program;
use App\Review;
use App\Schedule;
use App\UniqueProduct;
use App\User;
use Faker\Generator;
use Modules\Stylerstaxonomy\Entities\Description;
use Modules\Stylerstaxonomy\Entities\Taxonomy;

/*
  |--------------------------------------------------------------------------
  | Model Factories
  |--------------------------------------------------------------------------
  |
  | Here you may define all of your model factories. Model factories give
  | you a convenient way to create models for testing and seeding your
  | database. Just tell the factory how a default model should look.
  |
 */

$factory->define(User::class, function (Generator $faker) {
    return [
        'name' => $faker->name,
        'email' => $faker->safeEmail,
        'password' => bcrypt(str_random(10)),
        'remember_token' => str_random(10),
    ];
});

$factory->define(Description::class, function (Generator $faker) {
    return [
        'description' => $faker->word
    ];
});

$factory->define(Taxonomy::class, function (Generator $faker) {
    return [
        'name' => $faker->word,
        'parent_id' => null
    ];
});

$factory->defineAs(Organization::class, 'accommodation', function () {
    return [
        'name_description_id' => function () {
            return factory(Description::class)->create()->id;
        },
        'type_taxonomy_id' => Config::getOrFail('taxonomies.organization_types.accommodation.id')
    ];
});

$factory->define(OrganizationManager::class, function () {
    return [];
});

$factory->defineAs(Device::class, 'room', function (Generator $faker) {
    $org = factory(Organization::class, 'accommodation')->create();
    return [
        'amount' => $faker->numberBetween(1, 100),
        'deviceable_id' => $org->id,
        'deviceable_type' => Organization::class,
        'type_taxonomy_id' => Config::get('taxonomies.devices.room'),
        'name_taxonomy_id' => function () {
            return factory(Taxonomy::class)->create([
                'parent_id' => Config::getOrFail('taxonomies.names.device_name')
            ])->id;
        }
    ];
});

$factory->define(DeviceUsage::class, function (Generator $faker) {
    return [
        'device_id' => function () {
            return factory(Device::class, 'room')->create()->id;
        }
    ];
});

$factory->define(Price::class, function (Generator $faker) {
    return [
        'product_id' => null,
        'age_range_id' => null,
        'amount' => $faker->numberBetween(1, 10),
        'margin_type_taxonomy_id' => Config::get('taxonomies.margin_types.value'),
        'margin_value' => $faker->numberBetween(10, 100),
        'extra' => false,
        'mandatory' => false
    ];
});

$factory->define(PriceElement::class, function (Generator $faker) {
    $netPrice = $faker->numberBetween(100, 1000);
    $marginValue = $faker->numberBetween(10, 100);
    return [
        'price_id' => null,
        'model_meal_plan_id' => null,
        'date_range_id' => null,
        'net_price' => $netPrice,
        'rack_price' => $netPrice + $marginValue,
        'margin_type_taxonomy_id' => Config::get('taxonomies.margin_types.value'),
        'margin_value' => $marginValue
    ];
});


$factory->define(Product::class, function (Generator $faker) {
    return [
        'productable_id' => null,
        'productable_type' => null,
        'type_taxonomy_id' => Config::get('taxonomies.product_types.accommodation'),
        'margin_type_taxonomy_id' => Config::get('taxonomies.margin_types.value'),
        'margin_value' => $faker->numberBetween(10, 100)
    ];
});

$factory->define(ModelMealPlan::class, function (Generator $faker) {
    $mealPlans = MealPlan::all()->toArray();
    return [
        'meal_plan_id' => $mealPlans[array_rand($mealPlans)]['id'],
        'meal_planable_type' => null,
        'meal_planable_id' => null,
        'date_range_id' => null
    ];
});

$factory->define(DateRange::class, function (Generator $faker) {
    return [
        'name_description_id' => function () {
            return factory(Description::class)->create()->id;
        },
        'date_rangeable_type' => Organization::class,
        'date_rangeable_id' => null,
        'from_time' => null,
        'to_time' => null,
        'type_taxonomy_id' => Config::get('taxonomies.date_range_types.open'),
        'margin_type_taxonomy_id' => Config::get('taxonomies.margin_types.value'),
        'margin_value' => $faker->numberBetween(10, 100)
    ];
});

$factory->define(AgeRange::class, function (Generator $faker) {
    return [
        'age_rangeable_type' => Organization::class,
        'age_rangeable_id' => null,
        'from_age' => $faker->numberBetween(0, 18),
        'to_age' => null,
        'name_taxonomy_id' => Config::get('taxonomies.age_ranges.adult.id')
    ];
});

$factory->define(Program::class, function (Generator $faker) {
    return [
        'name_description_id' => factory(Description::class)->create()->id,
        'type_taxonomy_id' => Config::get('taxonomies.program_types.activity'),
        'organization_id' => 1,
        'location_id' => null
    ];
});

$factory->define(Schedule::class, function (Generator $faker) {
    return [
        'cruise_id' => null,
        'from_time' => null,
        'to_time' => null,
        'frequency_taxonomy_id' => Config::get('taxonomies.schedule_frequencies.weekly'),
        'relative_time_id' => null
    ];
});

$factory->define(Order::class, function (Generator $faker) {
    return [
        'first_name' => $faker->firstNameFemale,
        'last_name' => $faker->lastName,
        'nationality' => $faker->countryCode,
        'email' => $faker->safeEmail,
        'telephone' => $faker->e164PhoneNumber,
        'site' => 'ots.local',
        'remarks' => $faker->sentence,
        'billing_type_taxonomy_id' => $faker->randomElement(Config::get('taxonomies.billing_types'))
    ];
});

$factory->define(OrderItem::class, function (Generator $faker) {
    $device = factory(Device::class, 'room')->create(['amount' => 0]);

    $itemData = [
        "device_id" => $device->id,
        "mealPlan" => "b/b",
        "interval" => [
            "date_from" => "2027-01-11",
            "date_to" => "2027-01-13"
        ],
        "calculatedPrice" => [
            "discounted_price" => 110,
            "original_price" => 110,
            "discounts" => [],
            "total_discount" => [],
            "meal_plan_id" => 2,
            "order_itemable_index" => 0,
            "meal_plan" => "b/b"
        ],
        "amount" => 1,
        "itemRequestIndex" => 0,
        "productableType" => Accommodation::class,
        "productableModel" => (new OrganizationEntity($device->deviceable))->getFrontendData()
    ];
    return [
        'order_id' => null,
        'order_itemable_type' => Device::class,
        'order_itemable_id' => $itemData['device_id'],
        'from_date' => $itemData['interval']['date_from'],
        'to_date' => $itemData['interval']['date_to'],
        'amount' => $itemData['amount'],
        'meal_plan_id' => 2,
        'order_itemable_index' => $itemData['itemRequestIndex'],
        'price' => $itemData['calculatedPrice']['discounted_price'],
        'json' => \json_encode($itemData)
    ];
});

$factory->define(OrderItemGuest::class, function (Generator $faker) {
    return [
        'order_item_id' => null,
        'guest_index' => 0,
        'age_range_id' => null,
        'first_name' => $faker->firstNameFemale,
        'last_name' => $faker->lastName
    ];
});

$factory->define(Cart::class, function (Generator $faker) {
    return [
        'status_taxonomy_id' => $faker->randomElement(Config::get('taxonomies.cart_statuses')),
        'billing_type_taxonomy_id' => Config::get('taxonomies.billing_types.individual'),
        'first_name' => $faker->firstNameFemale,
        'last_name' => $faker->lastName,
        'company_name' => null,
        'site' => null,
        'tax_number' => $faker->randomNumber(8),
        'country' => $faker->countryCode,
        'zip' => $faker->postCode,
        'city' => $faker->city,
        'address' => $faker->streetAddress,
        'email' => $faker->safeEmail,
        'phone' => $faker->phoneNumber
    ];
});

$factory->define(UniqueProduct::class, function (Generator $faker) {
    return [
        'supplier_id' => null,
        'name' => $faker->sentence,
        'unit' => 'piece',
        'cart_id' => null,
        'amount' => $faker->randomDigitNotNull,
        'net_price' => $faker->randomNumber,
        'margin' => $faker->randomDigitNotNull,
        'tax' => $faker->randomDigit,

    ];
});

$factory->define(Review::class, function (Generator $faker) {
    $organization = factory(Organization::class, 'accommodation')->create();
    return [
        'review_description_id' => function () {
            return factory(Description::class)->create()->id;
        },
        'review_subject_type' => get_class($organization),
        'review_subject_id' => $organization->id,
        'author_user_id' => function () {
            return factory(User::class)->create()->id;
        }
    ];
});

