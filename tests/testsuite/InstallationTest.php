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

require_once('../../core/class/DB.class.php');
require_once('plugin_info/install.php');

class InstallationTest extends TestCase
{
    public $dataStorage;

    protected function setUp()
    {
        DB::init();
    }

    protected function tearDown()
    {
        MockedActions::clear();
    }

    public function testInstall()
    {
        AlternativeMarketForJeedom_install();
        $actions = MockedActions::get();
        $this->assertCount(12, $actions);
        $this->assertEquals('query_execute', $actions[0]['action']);
        $this->assertEquals('query_execute', $actions[1]['action']);
        $this->assertContains("CREATE TABLE `data_amfj`", $actions[1]['content']['query']);
        $this->assertEquals('eqLogic_save', $actions[2]['action']);
        $this->assertEquals('NextDom Stable', $actions[2]['content']);
        $this->assertEquals('save', $actions[11]['action']);
        $this->assertEquals('github::enable', $actions[10]['content']['key']);
        $this->assertEquals(1, $actions[10]['content']['data']);
        $this->assertEquals('show-disclaimer', $actions[11]['content']['key']);
        $this->assertEquals(true, $actions[11]['content']['data']);
    }

    public function testRemove()
    {
        AlternativeMarketForJeedom_remove();
        $actions = MockedActions::get();
        var_dump($actions);
        $this->assertCount(4, $actions);
        $this->assertEquals('query_execute', $actions[0]['action']);
        $this->assertEquals('remove', $actions[1]['action']);
        $this->assertEquals('show-disclaimer', $actions[1]['content']['key']);
        $this->assertEquals('remove', $actions[2]['action']);
        $this->assertEquals('show-duplicates', $actions[2]['content']['key']);
        $this->assertEquals('remove', $actions[3]['action']);
        $this->assertEquals('show-sources-filters', $actions[3]['content']['key']);
    }
}
