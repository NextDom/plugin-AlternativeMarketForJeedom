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

require_once('core/class/AmfjMarket.class.php');
require_once('core/class/AmfjDataStorage.class.php');
require_once('../../core/php/core.inc.php');

class AmfjMarketTest extends TestCase
{
    /**
     * @var AmfjMarket
     */
    private $market;

    private $dataStorage;

    public function setUp()
    {
        $source = [];
        $source['type'] = 'github';
        $source['name'] = 'NextDom';
        $source['data'] = 'NextDom';
        update::$byLogicalIdResult = false;
        DB::init(true);
        $this->market = new AmfjMarket($source);
        $this->dataStorage = new AmfjDataStorage('amfj');
        $this->dataStorage->createDataTable();
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
        $this->dataStorage->dropDataTable();
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
        $this->dataStorage->storeRawData('repo_ignore_jeedom', '["to_remove"]');
        $this->market->refresh();
        $ignoreList = $this->dataStorage->getJsonData('repo_ignore_NextDom');
        $this->assertTrue(in_array('AlternativeMarket-Lists', $ignoreList));
        $this->assertFalse(in_array('to_remove', $ignoreList));
        $lastUpdate = $this->dataStorage->getJsonData('repo_last_update_NextDom');
        $this->assertTrue(is_numeric($lastUpdate));
        $globalRepo = $this->dataStorage->getJsonData('repo_data_NextDom');
        $this->assertTrue(is_array($globalRepo));
        sleep(60);
        $amfjRepo = $this->dataStorage->getJsonData('repo_data_NextDom_AlternativeMarketForJeedom');
        $optimizeRepo = $this->dataStorage->getJsonData('repo_data_NextDom_plugin-Optimize');
        $this->assertTrue(is_array($amfjRepo));
        $this->assertArrayHasKey('fullName', $optimizeRepo);
    }

    public function testRefreshWithGitData()
    {
        $repoData = '[{"name":"core","git_user":"jeedom","full_name":"jeedom/core","description":"Core","html_url":"url","default_branch":"master"},' .
            '{"name":"plugin-homebridge","git_user":"jeedom","full_name":"jeedom/plugin-homebridge","description":"HomeBridge","html_url":"url","default_branch":"master"},' .
            '{"name":"plugin-blea","git_user":"jeedom","full_name":"jeedom/plugin-blea","description":"Blea","html_url":"url","default_branch":"master"}]';
        $updateTime = time();
        $this->dataStorage->storeRawData('repo_ignore_jeedom', '["plugin-blea"]');
        $this->dataStorage->storeRawData('repo_last_update_jeedom', $updateTime);
        $this->dataStorage->storeRawData('repo_data_jeedom', $repoData);
        $this->market->refresh();

        $lastUpdate = $this->dataStorage->getRawData('repo_last_update_jeedom');
        $this->assertEquals($lastUpdate, $updateTime);
        $repoResults = $this->dataStorage->getRawData('repo_data_jeedom');
        $this->assertEquals($repoResults, $repoData);
        $pluginBlea = $this->dataStorage->getRawData('repo_data_jeedom_plugin-blea');
        $this->assertNull($pluginBlea);
        $pluginHomebridge = $this->dataStorage->getJsonData('repo_data_jeedom_plugin-homebridge');
        $this->assertNotNull($pluginHomebridge);
        $this->assertEquals('Homebridge', $pluginHomebridge['name']);
        $this->assertFileExists('cache/jeedom_plugin-homebridge.png');
        $ignoreList = $this->dataStorage->getJsonData('repo_ignore_jeedom');
        $this->assertTrue(in_array('plugin-blea', $ignoreList));
        $this->assertTrue(in_array('core', $ignoreList));
        $this->assertFalse(in_array('plugin-homebridge', $ignoreList));
    }

    public function testGetItems()
    {
        $repoData = '[{"name":"core","git_user":"jeedom","full_name":"jeedom/core","description":"Core","html_url":"url","default_branch":"master"},' .
            '{"name":"plugin-weather","git_user":"jeedom","full_name":"jeedom/plugin-weather","description":"Weather","html_url":"url","default_branch":"master"},' .
            '{"name":"plugin-blea","git_user":"jeedom","full_name":"jeedom/plugin-blea","description":"Blea","html_url":"url","default_branch":"master"}]';
        $updateTime = time();
        $this->dataStorage->storeRawData('repo_last_update_jeedom', $updateTime);
        $this->dataStorage->storeRawData('repo_data_jeedom', $repoData);
        $this->market->refresh();

        $result = $this->market->getItems();
        $result = $this->market->getItems();
        $this->assertTrue(is_array($result));
        $core = $this->getItemFromList($result, 'core');
        $this->assertNull($core);
        $weather = $this->getItemFromList($result, 'plugin-weather');
        $this->assertNotNull($weather);
        $this->assertEquals('jeedom/plugin-weather', $weather->getFullName());
        $this->assertFileExists('cache/jeedom_plugin-weather.png');
    }
}
