<?php
require_once OW_DIR_RESTLER . 'vendor' . DS . 'restler.php';
use Luracast\Restler\iAuthenticate;
use OAuth2\GrantType\UserCredentials;
use OAuth2\Storage\Pdo;
use OAuth2\Server as OAuth2Server;
use OAuth2\GrantType\AuthorizationCode;
use OAuth2\Request;
use OAuth2\Response;


/**
 * Class Server
 *
 * @package OAuth2
 *
 */
class WEBAPI_CTRL_Server extends OW_ActionController
{
    /**
     * @var OAuth2Server
     */
    protected static $server;
    /**
     * @var Pdo
     */
    protected static $storage;
    /**
     * @var Request
     */
    protected static $request;


    /**
     * function to create the OAuth2 Server Object
     */
    public function __construct()
    {
		$dsn      = 'mysql:dbname='.OW_DB_NAME.';host='.OW_DB_HOST;
		$username = OW_DB_USER;
		$password = OW_DB_PASSWORD;
        $connection = array('dsn' => $dsn, 'username' => $username, 'password' => $password);

        // create PDO-based My-Sql storage
        static::$storage = new Pdo($connection);

        // create array of supported grant types
        $grantTypes = array(
            'authorization_code' => new AuthorizationCode(static::$storage),
            'user_credentials'   => new UserCredentials(static::$storage),
        );
		static::$request = Request::createFromGlobals();
        
        // instantiate the oauth server
		static::$server = new OAuth2Server(
            static::$storage,
            array('enforce_state' => true, 'allow_implicit' => true),
            $grantTypes
        );
    }
	
	/**
     * Stage 1: Client sends the user to this page
     *
     * User responds by accepting or denying
     *
     * @view oauth2/server/authorize.twig
     * @format HtmlFormat
     */
    public function authorize()
    {
        if ( OW::getRequest()->isPost() )
        {
            if ( isset($_POST) )
            {
				$this->postAuthorize($_POST['authorize']);
            }
        }else
		{
			static::$server->getResponse(static::$request);
			$action_url = OW::getRouter()->getBaseUrl() . 'webapi'. '/' . 'authorize' . '?'. $_SERVER['QUERY_STRING'];
			$this->assign('actionUrl', $action_url);
			// validate the authorize request.  if it is invalid,
			// redirect back to the client with the errors in tow
			if (!static::$server->validateAuthorizeRequest(static::$request)) {
				static::$server->getResponse()->send();
				exit;
			}
			return array('queryString' => $_SERVER['QUERY_STRING']);
		}
    }

    /**
     * Stage 2: User response is captured here
     *
     * Success or failure is communicated back to the Client using the redirect
     * url provided by the client
     *
     * On success authorization code is sent along
     *
     *
     * @param bool $authorize
     *
     * @return \OAuth2\Response
     *
     * @format JsonFormat,UploadFormat
     */
    public function postAuthorize($authorize = false)
    {
		$userId = OW::getUser()->getId();
        static::$server->handleAuthorizeRequest(
            static::$request,
            new Response(),
            (bool)$authorize,
			$userId
        )->send();
        exit;
    }

    /**
     * Stage 3: Client directly calls this api to exchange access token
     *
     * It can then use this access token to make calls to protected api
     *
     * @format JsonFormat,UploadFormat
     */
    public function grant()
    {
        static::$server->handleTokenRequest(static::$request)->send();
        exit;
    }

    /**
     * Sample api protected with OAuth2
     *
     * For testing the oAuth token
     *
     * @access protected
     */
    public function access()
    {
        return array(
            'friends' => array('john', 'matt', 'jane')
        );
    }


    /**
     * Access verification method.
     *
     * API access will be denied when this method returns false
     *
     * @return boolean true when api access is allowed; false otherwise
     */
    public function __isAllowed()
    {
        return self::$server->verifyResourceRequest(static::$request);
    }

    public function __getWWWAuthenticateString()
    {
        return 'Bearer realm="example"';
    }
}
