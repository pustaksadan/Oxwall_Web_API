<?php
require_once OW_DIR_RESTLER . 'vendor' . DS . 'restler.php';

use Luracast\Restler\RestException;
use Luracast\Restler\Restler;
use Luracast\Restler\Scope;
use Luracast\Restler\Util;

class WEBAPI_CTRL_Client extends OW_ActionController
{
    /**
     * @var string url of the OAuth2 server to authorize
     */
    public static $serverUrl;
    public static $authorizeRoute = 'authorize';
    public static $tokenRoute = 'grant';
    public static $resourceMethod = 'GET';
    public static $resourceRoute = 'photos';
    public static $resourceParams = array();
    public static $resourceOptions = array();
    public static $clientId = 'demoapp';
    public static $clientSecret = 'demopass';
	public static $session_id;
    /**
     * @var string where to send the OAuth2 authorization result
     * (success or failure)
     */
    protected static $replyBackUrl;
    /**
     * @var Restler
     */
    public $restler;

    public function __construct()
    {
        //session_start(); //no need to start session
        static::$session_id = session_id();
        $this->restler = Scope::get('Restler');
        static::$replyBackUrl = 'authorized';
        if (!static::$serverUrl) {
            static::$serverUrl =
                OW::getRouter()->getBaseUrl() . 'webapi';
        }
        static::$authorizeRoute = static::fullURL(static::$authorizeRoute);
        static::$tokenRoute = static::fullURL(static::$tokenRoute);
        static::$resourceRoute = static::fullURL(static::$resourceRoute);
		static::$replyBackUrl = static::fullURL(static::$replyBackUrl);
    }

    /**
     * Prefix server url if relative path is used
     *
     * @param string $path full url or relative path
     * @return string proper url
     */
    private function fullURL($path)
    {
        return 0 === strpos($path, 'http')
            ? $path
            : static::$serverUrl . '/' . $path;
    }

    /**
     * Stage 1: Let user start the oAuth process by clicking on the button
     *
     * He will then be taken to the oAuth server to grant or deny permission
     *
     * @view   oauth2/client/index.twig
     */
    public function index()
    {
		$this->assign('response', array(
            'authorize_url' => static::$authorizeRoute,
            'authorize_redirect_url' => urlencode(static::$replyBackUrl),
			'client_id' => 'demoapp',
			'session_id' => static::$session_id
        ));	
    }

    /**
     * Stage 2: Users response is recorded by the server
     *
     * Server redirects the user back with the result.
     *
     * If successful,
     *
     * Client exchanges the authorization code by a direct call (not through
     * user's browser) to get access token which can then be used call protected
     * APIs, if completed it calls a protected api and displays the result
     * otherwise client ends up showing the error message
     *
     * Else
     *
     * Client renders the error message to the user
     *
     * @param string $code
     * @param string $error_description
     * @param string $error_uri
     *
     * @return array
     *
     */
    public function authorized()
    {
		$code = null;
        $error_description = null;
		$error_uri = null;
		
		$code = isset($_GET['code'])?$_GET['code']:$code;
		
        // the user denied the authorization request
        if (!$code && (isset($_GET['error'])))
		{
			//&& ($_GET['error'] == 'access_denied')
			$error['error_description'] 
				= isset($_GET['error_description'])?$_GET['error_description']:$error_description;
			$error['error_uri'] 
				= isset($_GET['error_uri'])?$_GET['error_uri']:$error_uri;
				
			$this->assign('response', array(
				'error' => $error
			));	
            return array('error' => compact('error_description', 'error_uri'));
        }
        // exchange authorization code for access token
        $query = array(
            'grant_type' => 'authorization_code',
            'code' => $code,
            'client_id' => static::$clientId,
            'client_secret' => static::$clientSecret,
            'redirect_uri' => static::$replyBackUrl,
        );
        //call the API using cURL
        $curl = new Curl();
        $endpoint = static::$tokenRoute;
        $response = $curl->request($endpoint, $query, 'POST');
        if (!(json_decode($response['response'], true))) {
            $status = $response['headers']['http_code'];
            echo '<h1>something went wrong - see the raw response</h1>';
            /*echo '<h2> Http ' . $status . ' - '
                . RestException::$codes[$status] . '</h2>';*/
            exit('<pre>' . print_r($response, true) . '</pre>');
        }
        $error = array();
        $response = json_decode($response['response'], true);

        // render error if applicable
        ($error['error_description'] =
            //OAuth error
            Util::nestedValue($response, 'error_description')) ||
        ($error['error_description'] =
            //Restler exception
            Util::nestedValue($response, 'error', 'message')) ||
        ($error['error_description'] =
            //cURL error
            Util::nestedValue($response, 'errorMessage')) ||
        ($error['error_description'] =
            //cURL error with out message
            Util::nestedValue($response, 'errorNumber')) ||
        ($error['error_description'] =
            'Unknown Error');

        $error_uri = Util::nestedValue($response, 'error_uri');

        if ($error_uri) {
            $error['error_uri'] = $error_uri;
        }

        // if it is successful, call the API with the retrieved token
        if (($token = Util::nestedValue($response, 'access_token'))) {
            // make request to the API for awesome data
            $data = static::$resourceParams + array('access_token' => $token);
            $response = $curl->request(
                static::$resourceRoute,
                $data,
                static::$resourceMethod,
                static::$resourceOptions
            );
			$this->assign('response', array(
                'token' => $token,
                'endpoint' => static::$resourceRoute . '?' . http_build_query($data)
            ));
            return;
        }
		$this->assign('response', array(
				'error' => $error
			));	
        return array('error' => $error);
    }
}
