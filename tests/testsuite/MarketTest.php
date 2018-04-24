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

require_once('core/class/Market.class.php');
require_once('../../core/php/core.inc.php');

class MarketTest extends TestCase
{
    /**
     * @var Market
     */
    private $market;

    public function setUp()
    {
        $this->market = new Market('jeedom');
        mkdir('cache');
    }

    public function tearDown()
    {
        $filesList = scandir('cache');
        foreach ($filesList as $file) {
            if ($file != '.' && $file != '..') {
                unlink('cache/' . $file);
            }
        }
        rmdir('cache');
    }

    private function getItemFromList($items, $itemName)
    {
        foreach ($items as $item) {
            if ($item->getGitName() == $itemName) {
                return $item;
            }
        }
        return null;
    }

    public function testRefreshFirstTime()
    {
        file_put_contents('cache/ignore_list', '["to_remove"]');
        $this->market->refresh();
        $this->assertFileExists('cache/ignore_list');
        $this->assertFileExists('cache/jeedom');
        $this->assertFileExists('cache/jeedom_plugin-blea');
        $this->assertFileExists('cache/jeedom_plugin-homebridge');
        $ignoreListContent = file_get_contents('cache/ignore_list');
        $this->assertNotContains('"to_remove"', $ignoreListContent);
        $this->assertContains('"core"', $ignoreListContent);
        $homebridgeContent = file_get_contents('cache/jeedom_plugin-homebridge');
        $this->assertContains('"gitName":"plugin-homebridge"', $homebridgeContent);
    }

    public function testRefreshWithGitData()
    {
        file_put_contents('cache/ignore_list', '["plugin-blea"]');
        file_put_contents('cache/jeedom', '[{"name":"core","full_name":"jeedom/core","description":"Core","html_url":"url"},' .
            '{"name":"plugin-homebridge","full_name":"jeedom/plugin-homebridge","description":"HomeBridge","html_url":"url"},' .
            '{"name":"plugin-blea","full_name":"jeedom/plugin-blea","description":"Blea","html_url":"url"}]');
        $this->market->refresh();
        $this->assertFileExists('cache/ignore_list');
        $this->assertFileExists('cache/jeedom');
        $this->assertFileNotExists('cache/jeedom_plugin-blea');
        $this->assertFileNotExists('cache/jeedom_plugin-weather');
        $this->assertFileExists('cache/jeedom_plugin-homebridge');
        $ignoreListContent = file_get_contents('cache/ignore_list');
        $this->assertContains('plugin-blea', $ignoreListContent);
        $this->assertContains('core', $ignoreListContent);
        $homebridgeContent = file_get_contents('cache/jeedom_plugin-homebridge');
        $this->assertContains('"gitName":"plugin-homebridge"', $homebridgeContent);
    }

    public function testGetItems()
    {
        file_put_contents('cache/jeedom', '[{"name":"core","full_name":"jeedom/core","description":"Core","html_url":"url"},' .
            '{"name":"plugin-homebridge","full_name":"jeedom/plugin-homebridge","description":"HomeBridge","html_url":"url"},' .
            '{"name":"plugin-blea","full_name":"jeedom/plugin-blea","description":"Blea","html_url":"url"}]');
        $this->market->refresh();
        $result = $this->market->getItems();
        $this->assertTrue(is_array($result));
        $core = $this->getItemFromList($result, 'core');
        $homebridge = $this->getItemFromList($result, 'plugin-homebridge');
        $this->assertNull($core);
        $this->assertNotNull($homebridge);
        $this->assertEquals('jeedom/plugin-homebridge', $homebridge->getFullName());
        $this->assertFileExists('cache/jeedom_plugin-homebridge');
        $this->assertFileExists('cache/jeedom_plugin-homebridge.png');
        $cacheContent = file_get_contents('cache/jeedom_plugin-homebridge');
        $this->assertContains('"gitName":"plugin-homebridge"', $cacheContent);
        $this->assertEquals('communication', $homebridge->getCategory());
    }
}
