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

class ModalDisclaimerAlternativeMarketForJeedomTest extends TestCase
{
    protected function setUp()
    {
    }

    protected function tearDown()
    {
    }

    public function testNotConnected()
    {
        JeedomVars::$isConnected = false;
        try {
            include(dirname(__FILE__) . '/../desktop/modal/disclaimer.AlternativeMarketForJeedom.php');
            $this->fail("L'exception n'a pas été déclenchée.");
        } catch (Exception $e) {
            $this->assertEquals($e->getMessage(), '{{401 - Accès non autorisé}}');
        }
    }

    public function testWithUserConnected()
    {
        ob_start();
        include(dirname(__FILE__) . '/../desktop/modal/disclaimer.AlternativeMarketForJeedom.php');
        $content = ob_get_clean();
        $actions = MockedActions::get();
        $this->assertCount(2, $actions);
        $this->assertEquals('include_file', $actions[0]['action']);
        $this->assertEquals('authentification', $actions[0]['content']['name']);
        $this->assertEquals('include_file', $actions[1]['action']);
        $this->assertEquals('disclaimer.AlternativeMarketForJeedom', $actions[1]['content']['name']);
        $this->assertContains('alert-danger', $content);
        $this->assertContains('div_pluginAlternativeMarketForJeedomAlert', $content);
    }
}