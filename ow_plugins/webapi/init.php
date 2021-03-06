<?php

/**
 * This software is intended for use with Oxwall Free Community Software http://www.oxwall.org/ and is a proprietary licensed product. 
 * For more information see License.txt in the plugin folder.

 * ---
 * Copyright (c) 2016, PustakSadan
 * All rights reserved.

 * Redistribution and use in source and binary forms, with or without modification, are not permitted provided.

 * This plugin should be bought from the developer by paying money to PayPal account (pustaksadan.india@gmail.com).

 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES,
 * INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR
 * PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,
 * INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO,
 * PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED
 * AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 */
$plugin = OW::getPluginManager()->getPlugin('webapi');

$classesToAutoload = array(
	'Server' => $plugin->getRootDir() . 'classes'. DS . 'Auth'. DS .'Server.php',
	'Curl' => $plugin->getRootDir() . 'classes'. DS . 'Auth'. DS .'Curl.php',
	'Photos' => $plugin->getRootDir() . 'classes'. DS .'Photos.php',
);

OW::getAutoloader()->addClassArray($classesToAutoload);
OW::getRouter()->addRoute(new OW_Route('webapi', 'webapi/', 'WEBAPI_CTRL_Client', 'index'));
OW::getRouter()->addRoute(new OW_Route('api.client.authorize', 'webapi/', 'WEBAPI_CTRL_Client', 'index'));
OW::getRouter()->addRoute(new OW_Route('api.client.authorized', 'webapi/authorized', 'WEBAPI_CTRL_Client', 'authorized'));
OW::getRouter()->addRoute(new OW_Route('api.authorize', 'webapi/authorize', 'WEBAPI_CTRL_Server', 'authorize'));
OW::getRouter()->addRoute(new OW_Route('api.grant', 'webapi/grant', 'WEBAPI_CTRL_Server', 'grant'));
OW::getRouter()->addRoute(new OW_Route('api.access', 'webapi/photo/access', 'WEBAPI_CTRL_ApiBase', 'index'));


OW::getRouter()->addRoute(new OW_Route('api.photos', 'webapi/photos', 'WEBAPI_CTRL_ApiBase', 'index'));
OW::getRouter()->addRoute(new OW_Route('api.photos.id', 'webapi/photos/:id', 'WEBAPI_CTRL_ApiBase', 'index'));


//WEBAPI_CLASS_Photo::getInstance()->init();
