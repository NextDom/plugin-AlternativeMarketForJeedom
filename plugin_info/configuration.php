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
    <label class="col-sm-4 control-label">{{Token GitHub}} <sup><i class="fa fa-question-circle tooltips" title="{{Permet de saisir votre token github}}" style="font-size : 1em;color:grey;"></i></sup></label>
        <div id="div_github-user-token" class="col-sm-4">
            <input type="text" data-l1key="github-user-token" class="configKey form-control" id="github-user-token"/>
        </div>
    </div>
    <div class="form-group">
    <label class="col-sm-4 control-label">{{Afficher les doublons}} <sup><i class="fa fa-question-circle tooltips" title="{{Autorise l'affichage multiple des plugins en cas de présence dans plusieurs dépots}}" style="font-size : 1em;color:grey;"></i></sup></label>
        <div id="div_show-duplicates" class="col-sm-2 tooltips">
            <input type="checkbox" id="show-duplicates" class="configKey" data-l1key="show-duplicates" placeholder="{{Afficher les doublons}}"/>
            <label for="show-duplicates">  </label>
        </div>
    </div>
    <div class="form-group">
        <label for="sources-manager" class="col-sm-4 control-label">{{Gestionnaire de sources}}</label>
        <div class="col-sm-2">
            <button id="sources-manager" class="btn btn-primary"><i class="fa fa-th-large"></i> {{Gérer}}</button>
        </div>
    </div>
</form>

<style type="text/css">
[type="checkbox"][class="configKey"]:not(:checked),
    [type="checkbox"][class="configKey"]:checked {
        position: absolute;
        left: -9999px;
    }
    [type="checkbox"][class="configKey"]:not(:checked) + label,
    [type="checkbox"][class="configKey"]:checked + label {
        position: relative;
        padding-left: 75px;
        cursor: pointer;
    }
    [type="checkbox"][class="configKey"]:not(:checked) + label:before,
    [type="checkbox"][class="configKey"]:checked + label:before,
    [type="checkbox"][class="configKey"]:not(:checked) + label:after,
    [type="checkbox"][class="configKey"]:checked + label:after {
        content: '';
        position: absolute;
    }
    [type="checkbox"][class="configKey"]:not(:checked) + label:before,
    [type="checkbox"][class="configKey"]:checked + label:before {
        left:0; top: -3px;
        width: 65px; height: 30px;
        background: #DDDDDD;
        border-radius: 15px;
        -webkit-transition: background-color .2s;
        -moz-transition: background-color .2s;
        -ms-transition: background-color .2s;
        transition: background-color .2s;
    }
    [type="checkbox"][class="configKey"]:not(:checked) + label:after,
    [type="checkbox"][class="configKey"]:checked + label:after {
        width: 20px; height: 20px;
        -webkit-transition: all .2s;
        -moz-transition: all .2s;
        -ms-transition: all .2s;
        transition: all .2s;
        border-radius: 50%;
        background: #d9534f;
        top: 2px; left: 5px;
    }

    /* on checked */
    [type="checkbox"][class="configKey"]:checked + label:before {
        background:#DDDDDD;
    }
    [type="checkbox"][class="configKey"]:checked + label:after {
        background: #62c462;
        top: 2px; left: 40px;
    }

    [type="checkbox"][class="configKey"]:checked + label .ui,
    [type="checkbox"][class="configKey"]:not(:checked) + label .ui:before,
    [type="checkbox"][class="configKey"]:checked + label .ui:after {
        position: absolute;
        left: 6px;
        width: 65px;
        border-radius: 15px;
        font-size: 14px;
        font-weight: bold;
        line-height: 22px;
        -webkit-transition: all .2s;
        -moz-transition: all .2s;
        -ms-transition: all .2s;
        transition: all .2s;
    }
    [type="checkbox"][class="configKey"]:not(:checked) + label .ui:before {
        content: "no";
        left: 32px
    }
    [type="checkbox"][class="configKey"]:checked + label .ui:after {
        content: "yes";
        color: #62c462;
    }
    [type="checkbox"][class="configKey"]:focus + label:before {
        border: 1px dashed #777;
        -webkit-box-sizing: border-box;
        -moz-box-sizing: border-box;
        -ms-box-sizing: border-box;
        box-sizing: border-box;
        margin-top: -1px;
    }
    label {
     margin-bottom:10px;
    }
</style>

<?php
include_file('desktop', 'AlternativeMarketForJeedomConfiguration', 'js', 'AlternativeMarketForJeedom');
