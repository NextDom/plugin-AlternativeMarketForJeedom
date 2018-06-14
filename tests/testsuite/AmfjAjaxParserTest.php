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
require_once('core/class/AmfjAjaxParser.class.php');
require_once('core/class/AlternativeMarketForJeedom.class.php');

class AmfjAjaxParserTest extends TestCase
{
    public $dataStorage;

    protected function setUp()
    {
        DB::init(true);
        $this->dataStorage = new AmfjDataStorage('amfj');
        $this->dataStorage->createDataTable();
        mkdir('cache');
    }

    protected function tearDown()
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

    public function testBadAction() {
        $result = AmfjAjaxParser::parse('bad_action', 'list', []);
        $this->assertFalse($result);
    }

    public function testRefreshBadParams() {
        $result = AmfjAjaxParser::parse('refresh', 'bad_params', []);
        $this->assertFalse($result);
    }

    public function testRefreshList() {
        $sourceData = '{"version":1526361553,"plugins":[{"defaultBranch":"master","gitId":"NextDom","repository":"plugin-AlternativeMarketForJeedom","id":"AlternativeMarketForJeedom","name":"AlternativeMarketForJeedom","licence":"GPL","description":"Market alternatif pour la solution domotique Jeedom","require":"3.0","category":"programming","documentation":"https:\/\/jeedom.github.io\/plugin-AlternativeMarketForJeedom\/#language#\/","changelog":"https:\/\/jeedom.github.io\/plugin-AlternativeMarketForJeedom\/#language#\/changelog","author":"Sylvain DANGIN","branches":[{"name":"develop","hash":"8336e911323f6b7fb665856b92b4c4c0c8e2661e"},{"name":"feature\/MarketForAll","hash":"c08d6c6cf3aef81f5d1fe74913985d5163188382"},{"name":"feature\/NamespaceComposer","hash":"958b99be02285d88af2edffeb0d0d5857c526a47"},{"name":"master","hash":"e604e4fea235c9879938afbdc9102e94b7ed5f5c"},{"name":"release\/0.4","hash":"43d8f27f0c73163d6bef74fd96b1a3c91109cff6"}]},{"defaultBranch":"master","gitId":"NextDom","repository":"plugin-MiFlora","id":"MiFlora","name":"MiFlora","licence":"GPL2.0","description":"Ce plugin permet de g\u00e8rer les Xiaomi plants ou Mi Flora. Il n\u00e9c\u00e9ssite une connection bluetooth vers les mi flora.","require":"2.4","category":"nature","documentation":"https:\/\/rjullien.github.io\/plugin-MiFlora\/#language#","changelog":"https:\/\/rjullien.github.io\/plugin-MiFlora\/#language#\/#tocAnchor-1-7","author":"Rene Jullien","branches":[{"name":"Beta","hash":"5dc402fc1b5dd51fafc5331a72b117d1d88326a9"},{"name":"Develop","hash":"b8f994039e1ae668feab546ec17d5f168904b52c"},{"name":"master","hash":"95e55487b6a970ffd3dc9a4abf3239dd233e4872"},{"name":"revert-58-fbell","hash":"800a7b550732d335fb57f2268557ae7dd6ca1e89"},{"name":"stable","hash":"e5e8a4827d707778cba33e21b2b6ebb10d82560d"}]},{"defaultBranch":"master","gitId":"NextDom","repository":"plugin-AndroidRemoteControl","id":"AndroidRemoteControl","name":"AndroidRemoteControl","licence":"AGPL","description":"Plugin pour piloter les Android TV et autres \u00e9quipements Android","require":"3.0","category":"multimedia","documentation":"https:\/\/NextDom.github.io\/plugin-AndroidRemoteControl\/","changelog":"https:\/\/NextDom.github.io\/plugin-AndroidRemoteControl\/fr_FR\/changelog.html","author":"NextDom [Byackee, Slobberbone]","branches":[{"name":"develop","hash":"e960c3dd8da3eea033a32a12fa77a1205fe0e95f"},{"name":"master","hash":"7bbbff6ce1b2775b4472d8e0de1893fb77a6cf18"}]}]}';
        $source = [];
        $source['type'] = 'json';
        $source['name'] = 'NextDom';
        $source['data'] = 'NextDom';
        $this->market = new AmfjMarket($source);
        $updateTime = time();
        $this->dataStorage->storeRawData('repo_ignore_NextDom', '["plugin-AndroidRemoteControl"]');
        $this->dataStorage->storeRawData('repo_last_update_NextDom', $updateTime);
        $this->dataStorage->storeRawData('repo_data_NextDom', $sourceData);
        JeedomVars::$initAnswers = array('action' => 'refresh', 'params' => 'list', 'data' => array('NextDom', 'NextDom Test'));
        include(dirname(__FILE__) . '/../core/ajax/AlternativeMarketForJeedom.ajax.php');
        $actions = MockedActions::get();
        $this->assertCount(3, $actions);
        $this->assertEquals('include_file', $actions[0]['action']);
        $this->assertEquals('authentification', $actions[0]['content']['name']);
        $this->assertEquals('ajax_init', $actions[1]['action']);
        $lastUpdate = $this->dataStorage->getRawData('repo_last_update_NextDom');
        $this->assertEquals($lastUpdate, $updateTime);
        $repoResults = $this->dataStorage->getRawData('repo_data_NextDom');
        $this->assertEquals($repoResults, $sourceData);
        $ignoreList = $this->dataStorage->getJsonData('repo_ignore_NextDom');
        $this->assertTrue(in_array('plugin-AndroidRemoteControl', $ignoreList));
    }

    public function testGetBadParams() {
        $result = AmfjAjaxParser::parse('get', 'bad_params', []);
        $this->assertFalse($result);
    }

    public function testGetIconWithoutCache() {
        $initialRawData = '{"name":"AndroidRemoteControl","gitName":"plugin-AndroidRemoteControl","gitId":"NextDom","fullName":"NextDom\/plugin-AndroidRemoteControl","description":"Plugin pour Android","url":"https:\/\/github.com\/NextDom\/NextDom\/plugin-AndroidRemoteControl","id":"AndroidRemoteControl","author":"NextDom [Byackee, Slobberbone]","category":"multimedia","iconPath":false,"defaultBranch":"master","branchesList":[{"name":"develop","hash":"21337cbc82a5a2adf366443db681b85424871e55"},{"name":"master","hash":"f4e6b46d05261c12366626029475a77d37e50f03"}],"licence":"AGPL","sourceName":"NextDom Market","changelogLink":"https:\/\/NextDom.github.io\/plugin-AndroidRemoteControl\/fr_FR\/changelog.html","documentationLink":"https:\/\/NextDom.github.io\/plugin-AndroidRemoteControl\/"}';
        $this->dataStorage->storeRawData('repo_data_NextDom_plugin-AndroidRemoteControl', $initialRawData);
        JeedomVars::$initAnswers = array('action' => 'get', 'params' => 'icon', 'data' => array('sourceName' => 'Test Market', 'fullName' => 'NextDom/plugin-AndroidRemoteControl'));
        include(dirname(__FILE__) . '/../core/ajax/AlternativeMarketForJeedom.ajax.php');
        $actions = MockedActions::get();
        $this->assertCount(5, $actions);
        $this->assertEquals('include_file', $actions[0]['action']);
        $this->assertEquals('authentification', $actions[0]['content']['name']);
        $this->assertEquals('ajax_init', $actions[1]['action']);
        // DEBUG en 2
        $this->assertEquals('ajax_success', $actions[3]['action']);
        $this->assertEquals('plugins/AlternativeMarketForJeedom/cache/NextDom_plugin-AndroidRemoteControl.png', $actions[3]['content']);
    }

    public function testGetIconWithoutCacheStoreData()
    {
        $initialRawData = '{"name":"AndroidRemoteControl","gitName":"plugin-AndroidRemoteControl","gitId":"NextDom","fullName":"NextDom\/plugin-AndroidRemoteControl","description":"Plugin pour Android","url":"https:\/\/github.com\/NextDom\/NextDom\/plugin-AndroidRemoteControl","id":"AndroidRemoteControl","author":"NextDom [Byackee, Slobberbone]","category":"multimedia","iconPath":false,"defaultBranch":"master","branchesList":[{"name":"develop","hash":"21337cbc82a5a2adf366443db681b85424871e55"},{"name":"master","hash":"f4e6b46d05261c12366626029475a77d37e50f03"}],"licence":"AGPL","sourceName":"NextDom Market","changelogLink":"https:\/\/NextDom.github.io\/plugin-AndroidRemoteControl\/fr_FR\/changelog.html","documentationLink":"https:\/\/NextDom.github.io\/plugin-AndroidRemoteControl\/"}';
        $this->dataStorage->storeRawData('repo_data_NextDom_plugin-AndroidRemoteControl', $initialRawData);
        JeedomVars::$initAnswers = array('action' => 'get', 'params' => 'icon', 'data' => array('sourceName' => 'Test Market', 'fullName' => 'NextDom/plugin-AndroidRemoteControl'));
        include(dirname(__FILE__) . '/../core/ajax/AlternativeMarketForJeedom.ajax.php');
        $marketItem = AmfjMarketItem::createFromCache('Test Market', 'NextDom/plugin-AndroidRemoteControl');
        $this->assertEquals('plugins/AlternativeMarketForJeedom/cache/NextDom_plugin-AndroidRemoteControl.png', $marketItem->getIconPath());
    }

    public function testGetIconWithCache() {
        $initialRawData = '{"name":"AndroidRemoteControl","gitName":"plugin-AndroidRemoteControl","gitId":"NextDom","fullName":"NextDom\/plugin-AndroidRemoteControl","description":"Plugin pour Android","url":"https:\/\/github.com\/NextDom\/NextDom\/plugin-AndroidRemoteControl","id":"AndroidRemoteControl","author":"NextDom [Byackee, Slobberbone]","category":"multimedia","iconPath":"plugins\/AlternativeMarketForJeedom\/cache\/NextDom_plugin-AndroidRemoteControl.png","defaultBranch":"master","branchesList":[{"name":"develop","hash":"21337cbc82a5a2adf366443db681b85424871e55"},{"name":"master","hash":"f4e6b46d05261c12366626029475a77d37e50f03"}],"licence":"AGPL","sourceName":"NextDom Market","changelogLink":"https:\/\/NextDom.github.io\/plugin-AndroidRemoteControl\/fr_FR\/changelog.html","documentationLink":"https:\/\/NextDom.github.io\/plugin-AndroidRemoteControl\/"}';
        $this->dataStorage->storeRawData('repo_data_NextDom_plugin-AndroidRemoteControl', $initialRawData);
        JeedomVars::$initAnswers = array('action' => 'get', 'params' => 'icon', 'data' => array('sourceName' => 'Test Market', 'fullName' => 'NextDom/plugin-AndroidRemoteControl'));
        include(dirname(__FILE__) . '/../core/ajax/AlternativeMarketForJeedom.ajax.php');
        $actions = MockedActions::get();
        $this->assertCount(4, $actions);
        $this->assertEquals('include_file', $actions[0]['action']);
        $this->assertEquals('authentification', $actions[0]['content']['name']);
        $this->assertEquals('ajax_init', $actions[1]['action']);
        $this->assertEquals('ajax_success', $actions[2]['action']);
        $this->assertEquals('plugins/AlternativeMarketForJeedom/cache/NextDom_plugin-AndroidRemoteControl.png', $actions[2]['content']);
    }

    public function testSourceBadParams() {
        $result = AmfjAjaxParser::parse('source', 'bad_params', []);
        $this->assertFalse($result);
    }

    public function testSourceAdd() {
        DB::init(false);
        JeedomVars::$initAnswers = array('action' => 'source', 'params' => 'add', 'data' => array('id' => 'NextDom', 'type' => 'github'));
        include(dirname(__FILE__) . '/../core/ajax/AlternativeMarketForJeedom.ajax.php');
        $actions = MockedActions::get();
        $this->assertCount(6, $actions);
        $this->assertEquals('include_file', $actions[0]['action']);
        $this->assertEquals('authentification', $actions[0]['content']['name']);
        $this->assertEquals('ajax_init', $actions[1]['action']);
        $this->assertEquals('query_execute', $actions[3]['action']);
        $this->assertContains('INSERT INTO', $actions[3]['content']['query']);
        $this->assertEquals('source_NextDom', $actions[3]['content']['data'][0]);
        $this->assertEquals('ajax_success', $actions[4]['action']);
    }

    public function testSourceRemove() {
        JeedomVars::$initAnswers = array('action' => 'source', 'params' => 'remove', 'data' => array('id' => 'NextDom'));
        DB::init(false);
        include(dirname(__FILE__) . '/../core/ajax/AlternativeMarketForJeedom.ajax.php');
        $actions = MockedActions::get();
        $this->assertCount(9, $actions);
        $this->assertEquals('include_file', $actions[0]['action']);
        $this->assertEquals('authentification', $actions[0]['content']['name']);
        $this->assertEquals('ajax_init', $actions[1]['action']);
        $this->assertEquals('query_execute', $actions[2]['action']);
        $this->assertContains('DELETE FROM ', $actions[2]['content']['query']);
        $this->assertContains('repo_ignore_NextDom', $actions[2]['content']['data'][0]);
        $this->assertEquals('query_execute', $actions[3]['action']);
        $this->assertContains('DELETE FROM ', $actions[3]['content']['query']);
        $this->assertContains('repo_last_change_NextDom', $actions[3]['content']['data'][0]);
        $this->assertEquals('query_execute', $actions[4]['action']);
        $this->assertContains('DELETE FROM ', $actions[4]['content']['query']);
        $this->assertContains('repo_data_NextDom', $actions[4]['content']['data'][0]);
        $this->assertEquals('query_execute', $actions[5]['action']);
        $this->assertContains('DELETE FROM ', $actions[5]['content']['query']);
        $this->assertContains('repo_last_update_NextDom', $actions[5]['content']['data'][0]);
        $this->assertEquals('query_execute', $actions[6]['action']);
        $this->assertContains('DELETE FROM ', $actions[6]['content']['query']);
        $this->assertContains('source_NextDom', $actions[6]['content']['data'][0]);
        $this->assertEquals('ajax_success', $actions[7]['action']);
    }

    public function testSaveBadParams() {
        $result = AmfjAjaxParser::parse('save', 'bad_params', []);
        $this->assertFalse($result);
    }

    public function testSaveSources() {
        DB::init(false);
        JeedomVars::$initAnswers = array('action' => 'save', 'params' => 'sources', 'data' => array(array('id' => 'NextDom', 'enable' => true), array('id' => 'Jeedom', 'enable' => false)));
        include(dirname(__FILE__) . '/../core/ajax/AlternativeMarketForJeedom.ajax.php');
        $actions = MockedActions::get();
        $this->assertCount(10, $actions);
        /*
        var_dump($actions[4]);
        var_dump($actions[5]);
        var_dump($actions[6]);
        var_dump($actions[7]);
        var_dump($actions[8]);
        var_dump($actions[9]);
        */
        $this->assertEquals('include_file', $actions[0]['action']);
        $this->assertEquals('authentification', $actions[0]['content']['name']);
        $this->assertEquals('ajax_init', $actions[1]['action']);
        $this->assertEquals('query_execute', $actions[2]['action']);
        $this->assertContains('SELECT ', $actions[2]['content']['query']);
        $this->assertEquals('query_execute', $actions[4]['action']);
        $this->assertContains('INSERT INTO ', $actions[4]['content']['query']);
        $this->assertContains('"enabled":true', $actions[4]['content']['data'][1]);
        $this->assertEquals('query_execute', $actions[7]['action']);
        $this->assertContains('INSERT INTO ', $actions[7]['content']['query']);
        $this->assertContains('"enabled":false', $actions[7]['content']['data'][1]);
        $this->assertEquals('ajax_success', $actions[8]['action']);
    }
}
