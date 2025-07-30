<?php

namespace Tests\Integration\Entities;

use App\Entities\TranslationEntity;
use Tests\TestCase;

class TranslationEntityTest extends TestCase {

    static public $setupMode = self::SETUPMODE_ONCE;

    private function prepare_entity() {
        return new TranslationEntity();
    }

    /**
     * @test
     */
    function it_can_present_iso_codes() {
        $translationEntity = $this->prepare_entity();
        $this->assertTrue(in_array('en',$translationEntity->getIsoCodes()));
        $this->assertTrue(in_array('hu',$translationEntity->getIsoCodes()));
    }

    /**
     * @test
     */
    function it_can_flatten() {
        $translationEntity = $this->prepare_entity();
        $source = [
            'level01' => [
                'elementKey01' => 'value01',
                'level02' => [
                    'elementKey02' => 'value02'
                ]
            ]
        ];
        $expected = [
            'level01.elementKey01' => 'value01',
            'level01.level02.elementKey02' => 'value02'
        ];
        $this->assertEquals($expected,$translationEntity->flatten($source));
    }

    /**
     * @test
     */
    function it_can_unflatten() {
        $translationEntity = $this->prepare_entity();
        $expected = [
            'level01' => [
                'elementKey01' => 'value01',
                'level02' => [
                    'elementKey02' => 'value02'
                ]
            ]
        ];
        $source = [
            'level01.elementKey01' => 'value01',
            'level01.level02.elementKey02' => 'value02'
        ];
        $this->assertEquals($expected,$translationEntity->unflatten($source));
    }



}
