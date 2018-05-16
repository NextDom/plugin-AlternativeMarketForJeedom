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
    protected function setUp()
    {
    }

    protected function tearDown()
    {
    }

    public function testBadAction() {
        $result = AmfjAjaxParser::parse('bad_action', 'list', []);
        $this->assertFalse($result);
    }

    public function testRefreshBadParams() {
        $result = AmfjAjaxParser::parse('refresh', 'bad_params', []);
        $this->assertFalse($result);
    }

    public function testGetBadParams() {
        $result = AmfjAjaxParser::parse('get', 'bad_params', []);
        $this->assertFalse($result);
    }

    public function testSaveBadParams() {
        $result = AmfjAjaxParser::parse('save', 'bad_params', []);
        $this->assertFalse($result);
    }

    public function testSourceBadParams() {
        $result = AmfjAjaxParser::parse('source', 'bad_params', []);
        $this->assertFalse($result);
    }

    public function testSourceAdd() {
        JeedomVars::$initAnswers = array('action' => 'source', 'params' => 'add', 'data' => array('id' => 'NextDom', 'type' => 'github'));
        include(dirname(__FILE__) . '/../core/ajax/AlternativeMarketForJeedom.ajax.php');
        $actions = MockedActions::get();
        $this->assertCount(5, $actions);
        $this->assertEquals('include_file', $actions[0]['action']);
        $this->assertEquals('authentification', $actions[0]['content']['name']);
        $this->assertEquals('ajax_init', $actions[1]['action']);
        $this->assertEquals('eqLogic_save', $actions[2]['action']);
        $this->assertEquals('NextDom', $actions[2]['content']);
        $this->assertEquals('ajax_success', $actions[3]['action']);
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
        $this->assertEquals('eqLogic_remove', $actions[2]['action']);
        $this->assertEquals('query_execute', $actions[3]['action']);
        $this->assertContains('DELETE FROM ', $actions[3]['content']['query']);
        $this->assertContains('repo_ignore_NextDom', $actions[3]['content']['data'][0]);
        $this->assertEquals('query_execute', $actions[4]['action']);
        $this->assertContains('DELETE FROM ', $actions[4]['content']['query']);
        $this->assertContains('repo_last_change_NextDom', $actions[4]['content']['data'][0]);
        $this->assertEquals('query_execute', $actions[5]['action']);
        $this->assertContains('DELETE FROM ', $actions[5]['content']['query']);
        $this->assertContains('repo_data_NextDom', $actions[5]['content']['data'][0]);
        $this->assertEquals('query_execute', $actions[6]['action']);
        $this->assertContains('DELETE FROM ', $actions[6]['content']['query']);
        $this->assertContains('repo_last_update_NextDom', $actions[6]['content']['data'][0]);
        $this->assertEquals('ajax_success', $actions[7]['action']);
    }
}
