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

require_once dirname(__FILE__) . '/../../../core/php/core.inc.php';

include_file('core', 'authentification', 'php');

if (!isConnect()) {
    // @codeCoverageIgnoreStart
    include_file('desktop', '404', 'php');
    die();
    // @codeCoverageIgnoreEnd
}

?>
<form class="form-horizontal">
    <div class="form-group">
        <label for="github-user-token" class="col-sm-2 control-label">{{Token GitHub}}</label>
        <div class="col-sm-10">
            <input type="text" data-l1key="github-user-token" class="configKey form-control" id="github-user-token"/>
        </div>
    </div>
    <div class="form-group">
        <label for="show-duplicates" class="col-sm-2 control-label">{{Afficher les doublons}}</label>
        <div class="col-sm-10">
            <input type="checkbox" class="configKey form-control" id="show-duplicates" data-l1key="show-duplicates"/>
        </div>
    </div>
</form>