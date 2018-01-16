<?php
namespace X\Service\QQConnect;
/**
 * base class for qq connect production class.
 */
abstract class Production {
    /**
     * instance of qq connect service.
     * @var \X\Service\QQConnect\Service
     */
    private $service = null;
    
    /**
     * init current service.
     * @param \X\Service\QQConnect\Service $service
     */
    public function __construct( $service ) {
        $this->service = $service;
    }
    
    /**
     * get json data from http response by get method.
     * @param string $url
     * @param array $parms
     * @return array
     */
    protected function httpGetJSON( $url, array $parms=array() ) {
        $parameters = array();
        $parameters['oauth_consumer_key']    = $this->service->getAppID();
        $parameters['access_token']          = $this->service->getAccessToken();
        $parameters['openid']                = $this->service->getOpenId();
        $parameters['format']                = 'JSON';
        $parameters = array_merge($parameters, $parms);
        $request = new HttpRequest($url, $parameters);
        return $request->get(HttpRequest::FOTMAT_JSON);
    }
    
    /**
     * get json data from http response by post method.
     * @param string $url
     * @param array $parms
     * @return array
     */
    protected function httpPostJSON($url, array $parms=array()){
        $parameters = array();
        $parameters['oauth_consumer_key']    = $this->service->getAppID();
        $parameters['access_token']          = $this->service->getAccessToken();
        $parameters['openid']                = $this->service->getOpenId();
        $parameters['format']                = 'JSON';
        $parameters = array_merge($parameters, $parms);
        $request = new HttpRequest($url, $parameters);
        return $request->post(HttpRequest::FOTMAT_JSON);
    }
    
    /**
     * call api by given name.
     * @param string $api
     * @param array $params
     * @param string $isGet
     * @throws Exception
     * @return array
     */
    protected function doRequest( $api, $params=array(), $isGet=true  ) {
        $url = sprintf('https://graph.qq.com/%s', $api);
        $params = array_filter($params);
        if ( $isGet ) {
            $result = $this->httpGetJSON($url, $params);
        } else {
            $result = $this->httpPostJSON($url, $params);
        }
        return $this->checkResponse($result);
    }
    
    /**
     * check response result.
     * @param array $response
     * @throws Exception
     * @return array
     */
    protected function checkResponse( $response ) {
        if ( 0 === (int)$response['ret'] ) {
            return $response;
        } else {
            throw new \Exception($response['msg'], (int)$response['ret']);
        }
    }
}