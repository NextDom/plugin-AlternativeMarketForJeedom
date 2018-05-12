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

require_once __DIR__ . '/../../../../core/php/core.inc.php';

/**
 * Classe des objets de Jeedom
 */
class AlternativeMarketForJeedom extends eqLogic
{
    public static function cmpFunc($obj1, $obj2)
    {
        $result = null;
        $obj1Order = $obj1->getConfiguration()['order'];
        $obj2Order = $obj2->getConfiguration()['order'];
        if ($obj1Order == $obj2Order) {
            $result = 0;
        } else {
            if ($obj1Order < $obj2Order) {
                $result = -1;
            } else {
                $result = 1;
            }
        }
        return $result;
    }
}
