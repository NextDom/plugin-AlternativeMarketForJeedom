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

include_once('core/class/AlternativeMarketForJeedom.class.php');

use PHPUnit\Framework\TestCase;

class AlternativeMarketForJeedomTest extends TestCase
{
    public function testClassDeclaration()
    {
        $this->assertTrue(class_exists('AlternativeMarketForJeedom'));
        $methods = \get_class_methods('AlternativeMarketForJeedom');
        $this->assertContains('remove', $methods);
    }

    public function testCmpFunc()
    {
        $dataForTest = array(
            array('name' => 'obj1', 'order' => 5),
            array('name' => 'obj2', 'order' => 3),
            array('name' => 'obj3', 'order' => 2),
            array('name' => 'obj4', 'order' => 0),
            array('name' => 'obj5', 'order' => 1),
            array('name' => 'obj6', 'order' => 7),
        );
        $listEqLogic = array();
        foreach ($dataForTest as $data) {
            $testObj = new AlternativeMarketForJeedom();
            $testObj->setName($data['name']);
            $testObj->setConfiguration('order', $data['order']);
            \array_push($listEqLogic, $testObj);
        }
        \usort($listEqLogic, array('AlternativeMarketForJeedom', 'cmpFunc'));
        $this->assertEquals('obj4', $listEqLogic[0]->getName());
        $this->assertEquals('obj3', $listEqLogic[2]->getName());
        $this->assertEquals('obj1', $listEqLogic[4]->getName());
        $this->assertEquals('obj6', $listEqLogic[5]->getName());
    }
}