<?php
require_once OW_DIR_RESTLER . 'vendor' . DS . 'restler.php';
use Luracast\Restler\Restler;



class WEBAPI_CTRL_ApiBase extends OW_ActionController 
{

	public function __construct() 
	{
		parent::__construct();
	    
		$r = new Restler();
	   
		$r->setSupportedFormats('JsonFormat');
		$r->addAPIClass('Photos', '/webapi/photos');
		$r->addAPIClass('Resources', '');
		$r->addAuthenticationClass('Server', '');
		$r->setOverridingFormats('JsonFormat', 'HtmlFormat', 'UploadFormat');
		$r->handle();
	}

   public function index() 
   {

   }

}

?>