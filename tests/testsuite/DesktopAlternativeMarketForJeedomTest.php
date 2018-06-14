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
require_once('core/class/AmfjDataStorage.class.php');

class DesktopAlternativeMarketForJeedomTest extends TestCase
{
    public $dataStorage;

    protected function setUp()
    {
        DB::init(true);
        $this->dataStorage = new AmfjDataStorage('amfj');
        $this->dataStorage->dropDataTable();
        $this->dataStorage->createDataTable();
        MockedActions::clear();
    }

    protected function tearDown()
    {
        $this->dataStorage->dropDataTable();
        MockedActions::clear();
    }

    public function testNotConnected()
    {
        JeedomVars::$isConnected = false;
        try {
            include(dirname(__FILE__) . '/../desktop/php/AlternativeMarketForJeedom.php');
            $this->fail("L'exception n'a pas été déclenchée.");
        } catch (Exception $e) {
            $this->assertEquals($e->getMessage(), '{{401 - Accès non autorisé}}');
        }
    }

    public function testWithUserConnected()
    {
        ob_start();
        include(dirname(__FILE__) . '/../desktop/php/AlternativeMarketForJeedom.php');
        $content = ob_get_clean();
        $actions = MockedActions::get();
        $this->assertCount(11, $actions);
        $this->assertEquals('include_file', $actions[0]['action']);
        $this->assertEquals('authentification', $actions[0]['content']['name']);
        $this->assertEquals('sendVarToJs', $actions[1]['action']);
        $this->assertEquals('sourcesList', $actions[1]['content']['var']);
        $this->assertEquals('include_file', $actions[8]['action']);
        $this->assertEquals('AlternativeMarketForJeedom', $actions[8]['content']['name']);
        $this->assertEquals('include_file', $actions[9]['action']);
        $this->assertEquals('AlternativeMarketForJeedom', $actions[9]['content']['name']);
        $this->assertEquals('include_file', $actions[10]['action']);
        $this->assertEquals('plugin.template', $actions[10]['content']['name']);
        $this->assertContains('market-filters', $content);
        $this->assertContains('market-filter-category', $content);
    }

    public function testWithoutSources()
    {
        ob_start();
        include(dirname(__FILE__) . '/../desktop/php/AlternativeMarketForJeedom.php');
        $content = ob_get_clean();
        $this->assertNotContains('<button type="button" class="btn btn-primary" data-source="', $content);
    }

    public function testWithSources()
    {
        config::$byKeyPluginData['AlternativeMarketForJeedom'] = [];
        config::$byKeyPluginData['AlternativeMarketForJeedom']['show-sources-filters'] = true;
        $dataForTest = array(
            array('name' => 'src1', 'enabled' => 1, 'order' => 1, 'type' => 'json', 'data' => ''),
            array('name' => 'src2', 'enabled' => 1, 'order' => 2, 'type' => 'json', 'data' => ''),
            array('name' => 'src3', 'enabled' => 0, 'order' => 3, 'type' => 'github', 'data' => '')
        );
        foreach ($dataForTest as $data) {
            $this->dataStorage->storeJsonData('source_' . $data['name'], $data);
        }
        ob_start();
        include(dirname(__FILE__) . '/../desktop/php/AlternativeMarketForJeedom.php');
        $content = ob_get_clean();
        $this->assertContains('market-filter-src', $content);
        $this->assertContains('<button type="button" class="btn btn-primary" data-source="src1"', $content);
        $this->assertContains('<button type="button" class="btn btn-primary" data-source="src2"', $content);
        $this->assertNotContains('<button type="button" class="btn btn-primary" data-source="src3"', $content);
    }

    public function testWithMessage()
    {
        $_GET['message'] = 0;
        ob_start();
        include(dirname(__FILE__) . '/../desktop/php/AlternativeMarketForJeedom.php');
        ob_get_clean();
        $actions = MockedActions::get();
        $this->assertCount(12, $actions);
        $this->assertEquals('message_add', $actions[8]['action']);
        $this->assertContains('mise à jour', $actions[8]['content']['message']);
    }
}