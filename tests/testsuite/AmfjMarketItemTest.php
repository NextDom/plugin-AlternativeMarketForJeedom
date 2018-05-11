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
 *j
 * You should have received a copy of the GNU General Public License
 * along with Jeedom. If not, see <http://www.gnu.org/licenses/>.
 */

use PHPUnit\Framework\TestCase;

require_once('core/class/AmfjMarketItem.class.php');
require_once('core/class/AmfjDataStorage.class.php');
require_once('core/class/AmfjDownloadManager.class.php');
require_once('../../core/php/core.inc.php');

class AmfjMarketItemTest extends TestCase
{
    /**
     * @var AmfjMarketItem
     */
    private $marketItem;

    private $dataStorage;

    private $downloadManager;

    private $initialGitData = array(
        'name' => 'core',
        'full_name' => 'jeedom/plugin-openzwave',
        'description' => 'A small description',
        'html_url' => 'https://github.com/jeedom/plugin-openzwave',
        'git_id' => 'jeedom',
        'default_branch' => 'master'
    );

    private $initialRawData = '{"name":"AndroidRemoteControl","gitName":"plugin-AndroidRemoteControl","gitId":"NextDom","fullName":"NextDom\/plugin-AndroidRemoteControl","description":"Plugin pour Android","url":"https:\/\/github.com\/NextDom\/NextDom\/plugin-AndroidRemoteControl","id":"AndroidRemoteControl","author":"NextDom [Byackee, Slobberbone]","category":"multimedia","iconPath":"plugins\/AlternativeMarketForJeedom\/cache\/NextDom_plugin-AndroidRemoteControl.png","defaultBranch":"master","branchesList":[{"name":"develop","hash":"21337cbc82a5a2adf366443db681b85424871e55"},{"name":"master","hash":"f4e6b46d05261c12366626029475a77d37e50f03"}],"licence":"AGPL","sourceName":"NextDom Market","changelogLink":"https:\/\/NextDom.github.io\/plugin-AndroidRemoteControl\/fr_FR\/changelog.html","documentationLink":"https:\/\/NextDom.github.io\/plugin-AndroidRemoteControl\/"}';

    private $initialJsonData;

    public function setUp()
    {
        DB::init(true);
        if (file_exists('.github-token')) {
            $token = str_replace("\n", "", file_get_contents('.github-token'));
            config::addKeyToCore('github::token', $token);
        }
        $this->downloadManager = new AmfjDownloadManager(true);
        $this->initialJsonData = json_decode('{"defaultBranch":"master","gitId":"NextDom","repository":"plugin-AndroidRemoteControl","id":"AndroidRemoteControl","name":"AndroidRemoteControl","licence":"AGPL","description":"Plugin pour Android","require":"3.0","category":"multimedia","documentation":"https:\/\/NextDom.github.io\/plugin-AndroidRemoteControl\/","changelog":"https:\/\/NextDom.github.io\/plugin-AndroidRemoteControl\/fr_FR\/changelog.html","author":"NextDom [Byackee, Slobberbone]","branches":[{"name":"develop","hash":"5fa1e78312068d6c3d6ad91c237400ac5d202f0f"},{"name":"master","hash":"f4e6b46d05261c12366626029475a77d37e50f03"}],"screenshots":["https:\/\/github.com\/NextDom\/plugin-AndroidRemoteControl\/raw\/master\/docs\/images\/Screenshot1.png","https:\/\/github.com\/NextDom\/plugin-AndroidRemoteControl\/raw\/master\/docs\/images\/Screenshot2.png","https:\/\/github.com\/NextDom\/plugin-AndroidRemoteControl\/raw\/master\/docs\/images\/Screenshot3.png","https:\/\/github.com\/NextDom\/plugin-AndroidRemoteControl\/raw\/master\/docs\/images\/Screenshot4.png","https:\/\/github.com\/NextDom\/plugin-AndroidRemoteControl\/raw\/master\/docs\/images\/Screenshot5.png","https:\/\/github.com\/NextDom\/plugin-AndroidRemoteControl\/raw\/master\/docs\/images\/Screenshot6.png"]}', true);
        $this->dataStorage = new AmfjDataStorage('amfj');
        $this->dataStorage->createDataTable();
        mkdir('cache');
    }

    public function tearDown()
    {
        $this->dataStorage->dropDataTable();
        $filesList = scandir('cache');
        foreach ($filesList as $file) {
            if ($file != '.' && $file != '..') {
                unlink('cache/' . $file);
            }
        }
        rmdir('cache');
    }

    public function testCreateFromGit()
    {
        $marketItem = AmfjMarketItem::createFromGit('Test Market', $this->initialGitData);
        $this->assertEquals('jeedom/plugin-openzwave', $marketItem->getFullName());
        $this->assertEquals('A small description', $marketItem->getDescription());
        $this->assertEquals('https://github.com/jeedom/plugin-openzwave', $marketItem->getUrl());
    }

    public function testCreateFromCache()
    {
        $this->dataStorage->storeRawData('repo_data_NextDom_plugin-AndroidRemoteControl', $this->initialRawData);
        $marketItem = AmfjMarketItem::createFromCache('Test Market', 'NextDom/plugin-AndroidRemoteControl');
        $this->assertEquals('NextDom/plugin-AndroidRemoteControl', $marketItem->getFullName());
        $this->assertCount(2, $marketItem->getBranchesList());
        $this->assertEquals('multimedia', $marketItem->getCategory());
    }

    public function testCreateFromJson()
    {
        $marketItem = AmfjMarketItem::createFromJson('Test Market', $this->initialJsonData, $this->downloadManager);
        $this->assertEquals('NextDom/plugin-AndroidRemoteControl', $marketItem->getFullName());
        $this->assertCount(2, $marketItem->getBranchesList());
        $this->assertEquals('multimedia', $marketItem->getCategory());
    }

    public function testAddPluginInformations()
    {
        $pluginInformations = array(
            'id' => 'openzwave',
            'name' => 'Z-Wave',
            'author' => 'tmartinez - nechry - sarakha63 - loic',
            'category' => 'automation protocol',
            'description' => 'Plugin permettant la gestion du protocol Z-Wave'
        );
        $marketItem = new AmfjMarketItem('Test Market');
        $marketItem->addPluginInformations($pluginInformations);
        $this->assertEquals('openzwave', $marketItem->getId());
        $this->assertEquals('tmartinez - nechry - sarakha63 - loic', $marketItem->getAuthor());
        $this->assertEquals('automation protocol', $marketItem->getCategory());
        $this->assertEquals('Z-Wave', $marketItem->getName());
    }

    public function testIsNeedUpdateWithNothing()
    {
        $marketItem = new AmfjMarketItem('Test Market');
        $result = $marketItem->isNeedUpdate($this->initialGitData);
        $this->assertTrue($result);
    }

    public function testIsNeedUpdateWithRecentData()
    {
        $marketItem = AmfjMarketItem::createFromGit('Test Market', $this->initialGitData);
        $this->dataStorage->storeRawData('repo_last_update_jeedom_plugin-openzwave', time());
        $result = $marketItem->isNeedUpdate($this->initialGitData);
        $this->assertFalse($result);
    }

    public function testIsNeedUpdateWithOldFile()
    {
        $marketItem = AmfjMarketItem::createFromGit('Test Market', $this->initialGitData);
        $this->dataStorage->storeRawData('repo_last_update_jeedom_plugin-openzwave', time() - 360000);
        $result = $marketItem->isNeedUpdate($this->initialGitData);
        $this->assertTrue($result);
    }

    public function testGetDataInArray()
    {
        // Aucune information, donc le plugin n'est pas installé
        update::$byLogicalIdResult = false;
        $this->dataStorage->storeRawData('repo_data_NextDom_plugin-AndroidRemoteControl', '{"name":"AndroidRemoteControl","gitName":"plugin-AndroidRemoteControl","gitId":"NextDom","fullName":"NextDom\/plugin-AndroidRemoteControl","description":"Plugin pour piloter les Android TV et autres u00e9quipements Android","url":"https:\/\/github.com\/NextDom\/NextDom\/plugin-AndroidRemoteControl","id":"AndroidRemoteControl","author":"NextDom [Byackee, Slobberbone]","category":"multimedia","iconPath":"plugins\/AlternativeMarketForJeedom\/cache\/NextDom_plugin-AndroidRemoteControl.png","defaultBranch":"master","branchesList":[{"name":"develop","hash":"21337cbc82a5a2adf366443db681b85424871e55"},{"name":"master","hash":"f4e6b46d05261c12366626029475a77d37e50f03"}],"licence":"AGPL","sourceName":"NextDom Market","changelogLink":"https:\/\/NextDom.github.io\/plugin-AndroidRemoteControl\/fr_FR\/changelog.html","documentationLink":"https:\/\/NextDom.github.io\/plugin-AndroidRemoteControl\/"}');
        $marketItem = AmfjMarketItem::createFromCache('Test Market', 'NextDom/plugin-AndroidRemoteControl');
        $result = $marketItem->getDataInArray();
        $this->assertArrayHasKey('category', $result);
        $this->assertArrayHasKey('id', $result);
        $this->assertEquals('plugin-AndroidRemoteControl', $result['gitName']);
        $this->assertEquals('AndroidRemoteControl', $result['id']);
    }

    public function testWriteCache()
    {
        update::$byLogicalIdResult = false;
        $marketItem = AmfjMarketItem::createFromJson('Test Market', $this->initialJsonData, $this->downloadManager);
        $marketItem->writeCache();
        $data = $this->dataStorage->getJsonData('repo_data_NextDom_plugin-AndroidRemoteControl');
        $this->assertNotNull($data);
        $this->assertEquals('NextDom/plugin-AndroidRemoteControl', $data['fullName']);
        $lastUpdate = $this->dataStorage->getJsonData('repo_last_update_NextDom_plugin-AndroidRemoteControl');
        $this->assertNotNull($lastUpdate);
        $this->assertTrue(is_numeric($lastUpdate));
    }

    public function testReadCacheWithCache()
    {
        $marketItem = new AmfjMarketItem('NextDom');
        $marketItem->setFullName('NextDom/plugin-AndroidRemoteControl');
        $this->dataStorage->storeRawData('repo_data_NextDom_plugin-AndroidRemoteControl', $this->initialRawData);
        $result = $marketItem->readCache();
        $this->assertTrue($result);
        $this->assertEquals('AndroidRemoteControl', $marketItem->getName());
        $this->assertEquals('multimedia', $marketItem->getCategory());
    }

    public function testReadCacheWithoutCache()
    {
        $marketItem = new AmfjMarketItem('NextDom');
        $marketItem->setFullName('NextDom/plugin-AndroidRemoteControl');
        $result = $marketItem->readCache();
        $this->assertFalse($result);
    }

    public function testDescriptionOverrideWithPluginDescription()
    {
        $marketItem = AmfjMarketItem::createFromJson('Test Market', $this->initialJsonData, $this->downloadManager);
        $pluginInformations = array(
            'id' => 'Core',
            'name' => 'Core for test',
            'author' => 'Someone',
            'category' => 'programming',
            'description' => 'Une description'
        );
        $marketItem->addPluginInformations($pluginInformations);
        $result = $marketItem->getDescription();
        $this->assertEquals('Une description', $result);
    }

    public function testDescriptionOverrideWithoutPluginDescription()
    {
        $marketItem = AmfjMarketItem::createFromJson('Test Market', $this->initialJsonData, $this->downloadManager);
        $pluginInformations = array(
            'id' => 'Core',
            'name' => 'Core for test',
            'author' => 'Someone',
            'category' => 'programming'
        );
        $marketItem->addPluginInformations($pluginInformations);
        $result = $marketItem->getDescription();
        $this->assertEquals('Plugin pour Android', $result);
    }

    public function testDownloadBranchesInformationsBadData()
    {
        $modifiedData = $this->initialJsonData;
        $modifiedData['branches'] = [];
        $marketItem = AmfjMarketItem::createFromJson('Test Market', $modifiedData, $this->downloadManager);
        $marketItem->setFullName('ABadFullName');
        $marketItem->downloadBranchesInformations($this->downloadManager);
        $result = $marketItem->getBranchesList();
        $this->assertCount(0, $result);
    }

    public function testDownloadBranchesInformations()
    {
        $modifiedData = $this->initialJsonData;
        $modifiedData['branches'] = [];
        $marketItem = AmfjMarketItem::createFromJson('Test Market', $modifiedData, $this->downloadManager);
        $marketItem->downloadBranchesInformations($this->downloadManager);
        $result = $marketItem->getBranchesList();
        $this->assertCount(2, $result);
    }
}
