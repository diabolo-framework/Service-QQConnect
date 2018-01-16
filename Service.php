<?php
namespace X\Service\QQConnect;
/**
 * QQ Connect service.
 * @package X\Service\QQConnect
 * @author Michael Luthor <michaelluthor@163.com>
 */
class Service extends \X\Core\Service\XService {
    /**
     * Name of current service.
     * @var string
     */
    protected static $serviceName = 'QQConnect';
    
    /**
     * (non-PHPdoc)
     * @see \X\Core\Service\XService::start()
     */
    public function start() {
        parent::start();
        
        $configuration  = $this->getConfiguration();
        $this->appid    = $configuration['appid'];
        $this->appkey   = $configuration['appkey'];
        $this->callback = $configuration['callback'];
        if ( isset($configuration['scope']) ) {
            $this->scope = implode(',', $configuration['scope']);
        }
    }
    
    /**
     * App ID of current app.
     * @var string
     */
    private $appid = null;
    
    /**
     * Get current app id string.
     * @return string
     */
    public function getAppID() {
        return $this->appid;
    }
    
    /**
     * App key of current app.
     * @var string
     */
    private $appkey = null;
    
    /**
     * callback url after login succeded.
     * @var string
     */
    private $callback = null;
    
    /**
     * requsted action list.
     * @var string
     */
    private $scope = 'get_user_info,add_share,list_album,add_album,upload_pic,add_topic,add_one_blog,add_weibo,check_page_fans,add_t,add_pic_t,del_t,get_repost_list,get_info,get_other_info,get_fanslist,get_idolist,add_idol,del_idol,get_tenpay_addr1';
    
    /**
     * Access Token 信息， 包括当前token， 有效期和refresh token.
     * @var array
     */
    private $token = array();
    
    /**
     * Generate login page url for auth.
     * @return string
    */
    public function getLoginUrl( $params=null ){
        $callback = $this->callback;
        if ( null !== $params ) {
            $params = http_build_query($params);
            $connector = (false===strpos($callback, '?')) ? '?' : '&';
            $callback = $callback.$connector.$params;
        }
        
        /* 跳转到授权页面的参数 */
        $request = $this->getRequest(self::$URL_AUTH_CODE, array(
            'response_type' => 'code',
            'client_id'     => $this->appid,
            'redirect_uri'  => urlencode($callback),
            'scope'         => $this->scope,
        ));
        return $request->toString();
    }
    
    /**
     * Open ID of account to access qq service.
     * @var string
     */
    private $openId = null;
    
    /**
     * This service use to setup this service by request from qq server.
     * this method should be call in callback url.
     * @throws Exception CSRF validation failed.
     * @return void
     */
    public function setup(){
        /* 获取access token */
        $request = $this->getRequest(self::$URL_ACCESS_TOKEN, array(
            'grant_type'    => 'authorization_code',
            'client_id'     => $this->appid,
            'redirect_uri'  => urlencode($this->callback),
            'client_secret' => $this->appkey,
            'code'          => $_GET['code']
        ));
        
        /* check error first. */
        $this->token = $request->get(HttpRequest::FORMAT_JS_CALLBACK_JSON);
        if ( null !== $this->token && isset($this->token['error']) ) {
            throw new \Exception($this->token['error_description'], $this->token['error']);
        }
        
        $this->token = $request->get(HttpRequest::FORMAT_URL_PARAM);
        $this->token['expires_in'] = date('Y-m-d H:i:s',strtotime("{$this->token['expires_in']} second"));
    
        /* 获取Open ID */
        $request = $this->getRequest(self::$URL_OPENID, array('access_token' => $this->token['access_token']));
        $response = $request->get();
        $lpos = strpos($response, '(');
        $rpos = strrpos($response, ')');
        $response = substr($response, $lpos + 1, $rpos - $lpos -1);
        $user = json_decode($response);
        $this->openId = $user->openid;
    }
    
    /**
     * Get http request hander.
     * @param string $url
     * @param array $parameters
     * @return HttpRequest
     */
    private function getRequest($url, array $parameters=array()) {
        $request = new HttpRequest($url, $parameters);
        return $request;
    }
    
    /**
     * get access token of current account to qq's service.
     * @return string
     */
    public function getAccessToken(){
        return $this->token['access_token'];
    }
    
    /**
     * set access token of current account to qq's service.
     * @param string $token
     */
    public function setAccessToken( $token ) {
        $this->token['access_token'] = $token;
    }
    
    /**
     * get access token, includes current token, expired time and refresh token.
     * @return array
     */
    public function getTokenInfo() {
        return $this->token;
    }
    
    /**
     * get open id of current account to qq's service.
     * @return string
     */
    public function getOpenId(){
        return $this->openId;
    }
    
    /**
     * set open id of current account to qq's service.
     * @param string $id
     */
    public function setOpenId( $id ) {
        $this->openId = $id;
    }
    
    /**
     * Get instance of tweet handler.
     * @return \X\Service\QQConnect\Core\Tweet
     */
    public function Tweet() {
        return new Tweet($this);
    }
    
    /**
     * @return \X\Service\QQConnect\Core\QZone
     */
    public function QZone() {
        return new QZone($this);
    }
    
    /**
     * get version information of current api.
     * @var string
     */
    const VERSION = "2.0";
    
    /**
     * url to get open id.
     * @var string
     */
    private static $URL_OPENID = "https://graph.qq.com/oauth2.0/me";
    
    /**
     * url to get access token.
     * @var string
     */
    private static $URL_ACCESS_TOKEN = "https://graph.qq.com/oauth2.0/token";
    
    /**
     * url to get auth code.
     * @var string
     */
    private static $URL_AUTH_CODE = "https://graph.qq.com/oauth2.0/authorize";
}