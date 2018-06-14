<?php

/* This file is part of Jeedom.
 *
 * Jeedom is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Jeedom is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Jeedom. If not, see <http://www.gnu.org/licenses/>.
 */

use PHPUnit\Framework\TestCase;

require_once('../../core/php/core.inc.php');
require_once('core/class/AlternativeMarketForJeedom.class.php');

class EqLogicTest extends eqLogic {
    public function __construct($id, $name, $configuration, $isEnable)
    {
        $this->setId($id);
        $this->setName($name);
        $this->configuration = $configuration;
        $this->isEnable = $isEnable;
    }
}

class ModalConfigAlternativeMarketForJeedomTest extends TestCase
{
    public $sources;

    protected function setUp()
    {
        DB::init(true);
        $this->dataStorage = new AmfjDataStorage('amfj');
        $this->dataStorage->createDataTable();
        $this->sources = [];
        $this->sources = [
            ['name' => 'GitHub Enabled', 'enabled' => 1, 'type' => 'github', 'order' => 1, 'data' => ''],
            ['name' => 'GitHub Disabled', 'enabled' => 0, 'type' => 'github', 'order' => 2, 'data' => ''],
            ['name' => 'Json Enabled', 'enabled' => 1, 'type' => 'json', 'order' => 3, 'data' => ''],
            ['name' => 'Json Disabled', 'enabled' => 0, 'type' => 'json', 'order' => 4, 'data' => ''],
            ['name' => 'Another Json Disabled', 'enabled' => 0, 'type' => 'json', 'order' => 6, 'data' => ''],
        ];
        foreach ($this->sources as $source) {
            $this->dataStorage->storeJsonData('source_'.$source['name'], $source);
        }

    }

    protected function tearDown()
    {
        $this->dataStorage->dropDataTable();
    }

    public function testNotConnected()
    {
        JeedomVars::$isConnected = false;
        try {
            include(dirname(__FILE__) . '/../desktop/modal/config.AlternativeMarketForJeedom.php');
            $this->fail("L'exception n'a pas été déclenchée.");
        } catch (Exception $e) {
            $this->assertEquals($e->getMessage(), '{{401 - Accès non autorisé}}');
        }
    }

    public function testWithUserConnected()
    {
        ob_start();
        include(dirname(__FILE__) . '/../desktop/modal/config.AlternativeMarketForJeedom.php');
        $content = ob_get_clean();
        $actions = MockedActions::get();
        $this->assertCount(4, $actions);
        $this->assertEquals('include_file', $actions[0]['action']);
        $this->assertEquals('authentification', $actions[0]['content']['name']);
        $this->assertEquals('sendVarToJs', $actions[1]['action']);
        $this->assertEquals('sourcesList', $actions[1]['content']['var']);
        $this->assertEquals('include_file', $actions[2]['action']);
        $this->assertEquals('config.AlternativeMarketForJeedom', $actions[2]['content']['name']);
        $this->assertEquals('include_file', $actions[3]['action']);
        $this->assertEquals('AlternativeMarketForJeedom', $actions[3]['content']['name']);
        $this->assertContains('github-list-container', $content);
        $this->assertContains('div_pluginAlternativeMarketForJeedomAlert', $content);
    }

    public function testEnabledDisabled() {
        ob_start();
        include(dirname(__FILE__) . '/../desktop/modal/config.AlternativeMarketForJeedom.php');
        $content = ob_get_clean();

        $this->assertNotContains('id="check-source-1"', $content);
        $this->assertNotContains('GitHub Enabled', $content);
        $this->assertContains('id="check-source-3" type="checkbox" checked="checked"', $content);
        $this->assertContains('data-name="Json Disabled" id="check-source-4" type="checkbox">', $content);
        $this->assertContains('data-name="Json Enabled"', $content);
    }

    public function testSourcesList() {
        ob_start();
        include(dirname(__FILE__) . '/../desktop/modal/config.AlternativeMarketForJeedom.php');
        $content = ob_get_clean();
        $actions = MockedActions::get();

        $this->assertEquals('sendVarToJs', $actions[1]['action']);
        $this->assertEquals('sourcesList', $actions[1]['content']['var']);
        $sourcesList = $actions[1]['content']['value'];
        $this->assertCount(5, $sourcesList);
        $this->assertEquals('GitHub Enabled', $sourcesList[0]['name']);
        $this->assertEquals('GitHub Disabled', $sourcesList[1]['name']);
        $this->assertEquals('json', $sourcesList[2]['type']);
        $this->assertEquals('json', $sourcesList[3]['type']);
    }
}