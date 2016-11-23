<?php
require_once OW_DIR_RESTLER . 'vendor' . DS . 'restler.php';
use Luracast\Restler\Defaults;

class Photos
{
    private $photoService;
	private $photoAlbumService;
	private $photoApiBridge;

    function __construct()
    {
        $this->photoService = PHOTO_BOL_PhotoService::getInstance();
		$this->photoAlbumService = PHOTO_BOL_PhotoAlbumService::getInstance();
		$this->photoApiBridge = WEBAPI_CLASS_PhotoApiBridge::getInstance();
    }

    /**
	 * Get all photos by listType.
     * @access protected
     * @return array
     */
    function index()
    {
		$count=0;
		$user = Defaults::$userIdentifierClass;
		$userId = $user::getUniqueIdentifier();
		
		/**************** Get Photo Count of User identified by Access Token */
		if ( array_key_exists ('count', $_GET) )
		{
			return $this->photoApiBridge->getCount();
		}else if ( array_key_exists ('albumId', $_GET) && !empty($_GET['albumId']) )
		{
			return $this->photoApiBridge->userAlbum($userId);
		}
    
		/**************** Get Photo Lists of User identified by Access Token */
		return $this->photoApiBridge->getPhotos();
    }

    /**
     * Get Photo by id
     *
     * @param int $id
     * @access protected
     * @return Photo
     *
     */
    function get($id)
    {
		$user = Defaults::$userIdentifierClass;
		$userId = $user::getUniqueIdentifier();
		
        if ( ($photo = $this->photoService->findPhotoById($id)) === NULL )
        {
            throw new RestException(404);
        }
		return $photo;
    }

    /**
     * Add new Photo
     *
     * @param string $data {@from body}
     *
     * @return Photo
     *
     */
    function post($data=NULL)
    {
        return true;
    }

    /**
     * @param int    $id
     * @param array  $data      {@from body}
     * @param int    $position  {@from body}
     * @return Photo
     *
     */
    function put($id, $text = null, $position = null)
    {
        return true;
    }

    /**
     * delete a Photo by id
     *
     * @param int $id
     *
     * @return Photo
     *
     */
    function delete($id)
    {
        return true;
    }
	
	
}