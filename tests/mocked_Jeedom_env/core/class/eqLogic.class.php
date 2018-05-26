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

require_once('../../mocked_core.php');

class eqLogic
{
    public $id;

    public $name;

    public $configuration = [];

    public static $byTypeAnswer = [];

    public $isEnable = true;

    public function save() {
        MockedActions::add('eqLogic_save', $this->name);
    }

    public function remove() {
        MockedActions::add('eqLogic_remove', $this->name);
    }

    public function setName($name) {
        $this->name = $name;
    }

    public function getName() {
        return $this->name;
    }

    public function setLogicalId($logicalId) {

    }

    public function setEqType_name($eqType_name) {

    }

    public function setConfiguration($key, $configuration) {
        $this->configuration[$key] = $configuration;
    }

    public function getConfiguration($key = null) {
        if ($key == null) {
            return $this->configuration;
        }
        else {
            return $this->configuration[$key];
        }
    }

    public function setIsEnable($isEnable) {
        $this->isEnable = $isEnable;
    }

    public function getIsEnable() {
        return $this->isEnable;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    public static function byType($typeId, $onlyEnable = false) {
        return self::$byTypeAnswer;
    }

    public static function byLogicalId($logicalId) {
        return new eqLogic();
    }

    public static function byId($eqLogicId) {
        $result = new eqLogic();
        $result->setId($eqLogicId);
        return $result;
    }
}