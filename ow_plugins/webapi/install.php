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
//TBD: enable this later
$dbPrefix = "";
//$dbPrefix = OW_DB_PREFIX;
$redirect_uri = OW::getRouter()->getBaseUrl() . 'webapi'. '/' . 'authorized';
$sql =
    <<<EOT

	CREATE TABLE IF NOT EXISTS `{$dbPrefix}oauth_clients` 
	( 
		client_id VARCHAR(80) NOT NULL, 
		client_secret VARCHAR(80) NOT NULL, 
		redirect_uri VARCHAR(2000)  NOT NULL, 
		CONSTRAINT client_id_pk PRIMARY KEY (client_id)
	);

	CREATE TABLE IF NOT EXISTS `{$dbPrefix}oauth_access_tokens` (
		access_token VARCHAR(40) NOT NULL, 
		client_id VARCHAR(80) NOT NULL, 
		user_id VARCHAR(255), 
		expires TIMESTAMP NOT NULL,
		scope VARCHAR(2000), 
		CONSTRAINT access_token_pk PRIMARY KEY (access_token)
	);

	CREATE TABLE IF NOT EXISTS `{$dbPrefix}oauth_authorization_codes` (
		authorization_code VARCHAR(40) NOT NULL, 
		client_id VARCHAR(80) NOT NULL, 
		user_id VARCHAR(255), 
		redirect_uri VARCHAR(2000) NOT NULL, 
		expires TIMESTAMP NOT NULL, 
		scope VARCHAR(2000), 
		CONSTRAINT auth_code_pk PRIMARY KEY (authorization_code)
	);

	CREATE TABLE IF NOT EXISTS `{$dbPrefix}oauth_refresh_tokens` ( 
		refresh_token VARCHAR(40) NOT NULL, 
		client_id VARCHAR(80) NOT NULL, 
		user_id VARCHAR(255), 
		expires TIMESTAMP NOT NULL, 
		scope VARCHAR(2000), 
		CONSTRAINT refresh_token_pk PRIMARY KEY (refresh_token)
	);

	CREATE TABLE IF NOT EXISTS `{$dbPrefix}oauth_users` (
		username VARCHAR(255) NOT NULL, 
		password VARCHAR(2000), 
		first_name VARCHAR(255), 
		last_name VARCHAR(255), 
		CONSTRAINT username_pk PRIMARY KEY (username)
	);
	
	CREATE TABLE IF NOT EXISTS `{$dbPrefix}oauth_scopes` (
          scope               VARCHAR(80)  NOT NULL,
          is_default          BOOLEAN,
          PRIMARY KEY (scope)
        );
	
	INSERT INTO `{$dbPrefix}oauth_clients` (client_id, client_secret, redirect_uri) VALUES ("demoapp", "demopass", "{$redirect_uri}");
EOT;

OW::getDbo()->query($sql);

BOL_LanguageService::getInstance()->addPrefix('webapi', 'Web APIs');

OW::getLanguage()->importPluginLangs(OW::getPluginManager()->getPlugin('webapi')->getRootDir().'langs.zip', 'webapi');