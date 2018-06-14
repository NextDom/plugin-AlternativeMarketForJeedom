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
        $this->assertCount(16, $actions);
        $this->assertEquals('query_execute', $actions[0]['action']);
        $this->assertEquals('query_execute', $actions[1]['action']);
        $this->assertContains("CREATE TABLE `data_amfj`", $actions[1]['content']['query']);
        $this->assertEquals('query_execute', $actions[3]['action']);
        $this->assertEquals('source_NextDom Stable', $actions[3]['content']['data'][0]);
        $this->assertEquals('query_execute', $actions[5]['action']);
        $this->assertEquals('source_NextDom draft', $actions[5]['content']['data'][0]);
    }

    public function testRemove()
    {
        AlternativeMarketForJeedom_remove();
        $actions = MockedActions::get();
        $this->assertCount(4, $actions);
        $this->assertEquals('query_execute', $actions[0]['action']);
        $this->assertEquals('DROP TABLE IF EXISTS `data_amfj`', $actions[0]['content']['query']);
        $this->assertEquals('remove', $actions[1]['action']);
        $this->assertEquals('show-disclaimer', $actions[1]['content']['key']);
        $this->assertEquals('remove', $actions[2]['action']);
        $this->assertEquals('show-duplicates', $actions[2]['content']['key']);
        $this->assertEquals('remove', $actions[3]['action']);
        $this->assertEquals('show-sources-filters', $actions[3]['content']['key']);
    }
}
