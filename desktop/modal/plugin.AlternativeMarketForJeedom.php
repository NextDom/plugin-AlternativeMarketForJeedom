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

if (!isConnect('admin')) {
    throw new Exception('{{401 - Accès non autorisé}}');
}
?>
<div id="div_pluginAlternativeMarketForJeedomAlert"></div>
<div id="plugin-modal">
    <div id="plugin-modal-header" class="row">
        <div class="col-xs-12 col-sm-4 col-md-3 left-pane">
            <img id="plugin-icon"/>
        </div>
        <div class="col-xs-12 col-sm-8 col-md-9 right-pane">
            <span class="button-item">
                <button id="install-plugin" class="btn btn-lg btn-primary">{{Installer}}</button>
                <span id="default-branch-information" class="help-block"></span>
            </span>
            <span class="button-item">
                <div id="install-plugin-advanced" class="btn-group hidden-btn">
                    <button type="button" class="btn btn-lg btn-warning">{{Installation avancée}}</button>
                    <button type="button" class="btn btn-lg btn-warning dropdown-toggle" data-toggle="dropdown"
                            aria-haspopup="true" aria-expanded="false">
                        <span class="caret"></span>
                        <span class="sr-only">Toggle Dropdown</span>
                    </button>
                    <ul class="dropdown-menu">
                    </ul>
                </div>
                <div id="get-branches-informations">
                    <button type="button" class="btn btn-lg btn-warning"><i class="fa fa-plus"></i></button>
                </div>
            </span>
            <div id="description">
                <h3>{{Description}}</h3>
                <p id="description-content"></p>
            </div>

        </div>
    </div>
    <div id="plugin-modal-body" class="row">
        <div class="col-xs-6">
            <p id="author"><span class="list-title">{{Auteur}}</span> : <span class="list-info"></span></p>
            <p id="licence"><span class="list-title">{{Licence}}</span> : <span class="list-info"></span></p>
            <p id="category"><span class="list-title">{{Catégorie}}</span> : <span class="list-info"></span></p>
            <p id="gituser"><span class="list-title">{{Utilisateur Git}}</span> : <span class="list-info"></span></p>
            <p id="gitrepo"><span class="list-title">{{Dépôt Git}}</span> : <span class="list-info"></span></p>
        </div>
        <div class="col-xs-6">
            <p><a id="documentation-link" class="btn btn-success" target="_blank" href=""><i class="fa fa-book"></i>
                    {{Documentation}}</a></p>
            <p><a id="changelog-link" class="btn btn-success" target="_blank" href=""><i class="fa fa-list"></i> {{Changelog}}</a></p>
            <p><a id="github-link" class="btn btn-success" target="_blank" href=""><i class="fa fa-github"></i> {{GitHub}}</a></p>
        </div>
    </div>
    <div id="plugin-modal-footer" class="row">
        <span><a id="travis-badge"><img src=""/></a></span>
        <span><a id="coveralls-badge"><img src=""/></a></span>
        <span><a id="waffle-badge"><img src=""/></a></span>
    </div>
</div>
<script src="plugins/AlternativeMarketForJeedom/desktop/js/plugin.AlternativeMarketForJeedom.js"></script>
