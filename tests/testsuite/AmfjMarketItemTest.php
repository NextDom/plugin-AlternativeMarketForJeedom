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

require_once('core/class/AmfjMarketItem.class.php');
require_once('core/class/AmfjDataStorage.class.php');
require_once('core/class/AmfjDownloadManager.class.php');

class AmfjMarketItemTest extends TestCase
{
    /**
     * @var AmfjMarketItem
     */
    private $marketItem;

    private $dataStorage;

    private $initialData = array(
        'name' => 'core',
        'full_name' => 'jeedom/core',
        'description' => 'A small description',
        'html_url' => 'https://github.com/jeedom/core',
        'git_user' => 'jeedom',
        'default_branch' => 'master'
    );

    public function setUp()
    {
        DB::init(true);
        $this->marketItem = new AmfjMarketItem($this->initialData);
        $this->dataStorage = new AmfjDataStorage('amfj');
        $this->dataStorage->createDataTable();
    }

    public function tearDown()
    {
        $this->dataStorage->dropDataTable();
    }

    public function testInitWithGlobalInformations() {
        $this->assertEquals('core', $this->marketItem->getGitName());
        $this->assertEquals('jeedom/core', $this->marketItem->getFullName());
        $this->assertEquals('A small description', $this->marketItem->getDescription());
        $this->assertEquals('https://github.com/jeedom/core', $this->marketItem->getUrl());
    }

    public function testAddPluginInformations() {
        $pluginInformations = array(
            'id' => 'Core',
            'name' => 'Core for test',
            'author' => 'Someone',
            'category' => 'programming',
            'description' => 'Une description'
        );
        $this->marketItem->addPluginInformations($pluginInformations);
        $this->assertEquals('Core', $this->marketItem->getId());
        $this->assertEquals('Someone', $this->marketItem->getAuthor());
        $this->assertEquals('programming', $this->marketItem->getCategory());
        $this->assertEquals('Core for test', $this->marketItem->getName());
    }

    public function testIsNeedUpdateWithNothing() {
        $result = $this->marketItem->isNeedUpdate($this->initialData);
        $this->assertTrue($result);
    }

    public function testIsNeedUpdateWithRecentData() {
        $this->dataStorage->storeRawData('repo_last_update_jeedom_core', time());
        $result = $this->marketItem->isNeedUpdate($this->initialData);
        $this->assertFalse($result);
    }

    public function testIsNeedUpdateWithOldFile() {
        $this->dataStorage->storeRawData('repo_last_update_jeedom_core', time() - 360000);
        $result = $this->marketItem->isNeedUpdate($this->initialData);
        $this->assertTrue($result);
    }

    public function testGetDataInArray() {
        $result = $this->marketItem->getDataInArray();
        $this->assertArrayHasKey('category', $result);
        $this->assertArrayHasKey('id', $result);
        $this->assertEquals('core', $result['gitName']);
    }

    public function testWriteCache() {
        $this->marketItem->writeCache();
        $data = $this->dataStorage->getJsonData('repo_data_jeedom_core');
        $this->assertNotNull($data);
        $this->assertEquals('jeedom/core', $data['fullName']);
        $lastUpdate = $this->dataStorage->getJsonData('repo_last_update_jeedom_core');
        $this->assertNotNull($lastUpdate);
        $this->assertTrue(is_numeric($lastUpdate));
    }

    public function testReadCacheWithCache() {
        $jsonData = '{"name":"replace name","full_name":"jeedom/core","description":"A small description","html_url":"https://github.com/jeedom/core", "git_user":"jeedom","category":"programming"}';
        $this->dataStorage->storeRawData('repo_data_jeedom_core', $jsonData);
        $result = $this->marketItem->readCache();
        $this->assertTrue($result);
        $this->assertEquals('replace name', $this->marketItem->getName());
        $this->assertEquals('programming', $this->marketItem->getCategory());
    }

    public function testReadCacheWithoutCache() {
        $result = $this->marketItem->readCache();
        $this->assertFalse($result);
    }

    public function testDescriptionOverrideWithPluginDescription() {
        $pluginInformations = array(
            'id' => 'Core',
            'name' => 'Core for test',
            'author' => 'Someone',
            'category' => 'programming',
            'description' => 'Une description'
        );
        $this->marketItem->addPluginInformations($pluginInformations);
        $result = $this->marketItem->getDescription();
        $this->assertEquals('Une description', $result);
    }

    public function testDescriptionOverrideWithoutPluginDescription() {
        $pluginInformations = array(
            'id' => 'Core',
            'name' => 'Core for test',
            'author' => 'Someone',
            'category' => 'programming'
        );
        $this->marketItem->addPluginInformations($pluginInformations);
        $result = $this->marketItem->getDescription();
        $this->assertEquals('A small description', $result);
    }

    public function testDownloadBranchesInformations() {
        $downloadManager = new AmfjDownloadManager();
        $this->marketItem->downloadBranchesInformations($downloadManager);
        $result = $this->marketItem->getBranchesList();
        $this->assertContains('beta', $result);
        $this->assertContains('master', $result);
    }

}
