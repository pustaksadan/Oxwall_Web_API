<?php

/**
 * This software is intended for use with Oxwall Free Community Software http://www.oxwall.org/ and is
 * licensed under The BSD license.

 * ---
 * Copyright (c) 2016, PustakSadan
 * All rights reserved.

 * Redistribution and use in source and binary forms, with or without modification, are permitted provided that the
 * following conditions are met:
 *
 *  - Redistributions of source code must retain the above copyright notice, this list of conditions and
 *  the following disclaimer.
 *
 *  - Redistributions in binary form must reproduce the above copyright notice, this list of conditions and
 *  the following disclaimer in the documentation and/or other materials provided with the distribution.
 *
 *  - Neither the name of the Oxwall Foundation nor the names of its contributors may be used to endorse or promote products
 *  derived from this software without specific prior written permission.

 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES,
 * INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR
 * PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,
 * INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO,
 * PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED
 * AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 */

class WEBAPI_CLASS_PhotoApiBridge
{
    private $photoService;
	private $photoAlbumService;

    /**
     * Constructor.
     *
     */
    protected function __construct()
    {
        $this->photoService = PHOTO_BOL_PhotoService::getInstance();
		$this->photoAlbumService = PHOTO_BOL_PhotoAlbumService::getInstance();
    }
    /**
     * Singleton instance.
     *
     * @var PHOTO_BOL_PhotoAlbumDao
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return PHOTO_BOL_PhotoAlbumDao
     */
    public static function getInstance()
    {
        if ( self::$classInstance === null )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

	public function getCount()
	{
		if ( empty($_GET['username']) ||
			(($userDto = BOL_UserService::getInstance()->findByUsername($_GET['username'])) === NULL))
		{
			throw new RestException(404);
		}
		
		$count = $this->photoService->countUserPhotos($userDto->id);
		
		return $count;
	}
	
	public function getPhotos()
	{
		$validLists = array('featured', 'latest', 'toprated', 'tagged', 'most_discussed');
		$listType = !empty($_GET['listType']) ? $_GET['listType'] : 'latest';
		if ( !in_array($listType, $validLists) )
        {
            $listType = 'latest';
        }
		$offset = !empty($_GET['offset']) ? abs((int)$_GET['offset']) : 1;
		$limit = !empty($_GET['limit']) ? abs((int)$_GET['limit']) : (int)OW::getConfig()->getValue('photo', 'photos_per_page');
        		
		$photos = $this->photoService->findPhotoList($listType, $offset, $limit);
		
		return $photos;
	}
	
	public function userAlbum($userId)
    {
        if ( empty($_GET['username']) || ($userDto = BOL_UserService::getInstance()->findByUsername($_GET['username'])) === NULL )
        {
            throw new RestException(404);
        }

        if ( empty($_GET['albumId']) || ($albumDto = $this->photoAlbumService->findAlbumById($_GET['albumId'])) === NULL )
        {
            throw new RestException(404);
        }

        $isOwner = ($albumDto->userId == $userDto->id) && ($userDto->id == $userId);
        //$isModerator = OW::getUser()->isAuthorized('photo');
        
        if ( ($coverDto = PHOTO_BOL_PhotoAlbumCoverDao::getInstance()->findByAlbumId($albumDto->id)) === NULL )
        {
            if ( ($photo = PHOTO_BOL_PhotoAlbumService::getInstance()->getLastPhotoByAlbumId($albumDto->id)) === NULL )
            {
                $coverUrl = PHOTO_BOL_PhotoAlbumCoverDao::getInstance()->getAlbumCoverDefaultUrl();
            }
            else
            {
                $coverUrl = PHOTO_BOL_PhotoDao::getInstance()->getPhotoUrlByType($photo->id, PHOTO_BOL_PhotoService::TYPE_MAIN, $photo->hash, !empty($photo->dimension) ? $photo->dimension : FALSE);
            }
            
            $coverUrlOrig = $coverUrl;
        }
        else
        {
            $coverUrl = PHOTO_BOL_PhotoAlbumCoverDao::getInstance()->getAlbumCoverUrlForCoverEntity($coverDto);
            $coverUrlOrig = PHOTO_BOL_PhotoAlbumCoverDao::getInstance()->getAlbumCoverOrigUrlForCoverEntity($coverDto);
        }
        
        if ( $isOwner || $isModerator )
        {
			$offset = !empty($_GET['offset']) ? abs((int)$_GET['offset']) : 1;
			$limit = !empty($_GET['limit']) ? abs((int)$_GET['limit']) : (int)OW::getConfig()->getValue('photo', 'photos_per_page');
			$idList = array();
            $exclude = array($albumDto->id);
            $newsfeedAlbum = PHOTO_BOL_PhotoAlbumService::getInstance()->getNewsfeedAlbum($albumDto->userId);
            
            if ( !empty($newsfeedAlbum) )
            {
                $exclude[] = $newsfeedAlbum->id;
            }
			unset($albumDto->entityType);
			unset($albumDto->entityId);
			$albumDto->count = $this->photoAlbumService->countAlbumPhotos($albumDto->id);
			
			$albumDto->photos = $this->photoService->findPhotoListByAlbumId($albumDto->id, $offset, $limit, $idList);
                
			return array('result' => TRUE, 'status' => 'success', 'data' => $albumDto);
        }
		return array('result' => FALSE, 'status' => 'error', 'msg' => OW::getLanguage()->text('photo', 'auth_view_permissions'));
    }
 }
 ?>