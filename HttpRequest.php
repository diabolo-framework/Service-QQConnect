<?php
namespace X\Service\QQConnect;
/**
 * Http request handler class.
 */
class HttpRequest {
    /**
     * parameters to current request.
     * @var array
     */
    private $parameters = array();
    
    /**
     * requsted url.
     * @var string
     */
    private $url = null;
    
    /**
     * init current instance.
     * @param string $url
     * @param array $parms
     */
    public function __construct( $url, $parms=array()) {
        $this->url          = $url;
        $this->parameters   = $parms;
    }
    
    /**
     * convert current requst to string.
     * @return string
     */
    public function toString() {
        $parms = array();
        foreach ( $this->parameters as $key=>$value ) {
            $parms[] = sprintf('%s=%s', $key, $value);
        }
        $connector = ( false === strpos($this->url, '?') ) ? '?' : '&';
        $url = $this->url.$connector.implode('&', $parms);
        return $url;
    }
    
    /**
     * This value saved the response text.
     * @var string
     */
    private $response = null;
    
    /**
     * Execute get requst and parse response.
     * @param string $format
     * @param boolean $refresh
     * @return mixed
     */
    public function get( $format='', $refresh=false ) {
        if ( null === $this->response || $refresh ) {
            $combined = $this->toString();
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
            curl_setopt($ch, CURLOPT_URL, $combined);
            $this->response =  curl_exec($ch);
            curl_close($ch);
        }
        return $this->formatResponse($this->response, $format);
    }
    
    /**
     * Execute post requst and parse response.
     * @param string $format
     * @param boolean $refresh
     * @return mixed
     */
    public function post( $format='', $refresh=false) {
        if ( null === $this->response || $refresh ) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
            curl_setopt($ch, CURLOPT_POST, TRUE);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $this->parameters);
            curl_setopt($ch, CURLOPT_URL, $this->url);
            $this->response = curl_exec($ch);
            curl_close($ch);
        }
        return $this->formatResponse($this->response, $format);
    }
    
    /**
     * format response string by given formation.
     * @param string $response
     * @param string $format
     * @return mixed
     */
    private function formatResponse( $response, $format='' ) {
        $handler = sprintf('formatResponse%s', $format);
        if ( ''!==$format && method_exists($this, $handler) ) {
            return $this->$handler($response);
        } else {
            return $response;
        }
    }
    
    /**
     * get data from json string.
     * @param string $response
     * @return array
     */
    private function formatResponseJSON( $response ) {
        return json_decode($response, true);
    }
    
    /**
     * get data from url parameter string.
     * @param string $response
     * @return multitype:
     */
    private function formatResponseURLParam( $response ) {
        $params = array();
        parse_str($response, $params);
        return $params;
    }
    
    /**
     * get data from javascript callback string.
     * @param string $response
     * @return multitype:
     */
    private function formatResponseJSCallbackJSON( $response ) {
        $response = substr($response, 10);
        $response = substr($response, 0, strlen($response)-4);
        $response = json_decode($response, true);
        return $response;
    }
    
    /**
     * fromat data from json.
     * @var string
     */
    const FOTMAT_JSON       = 'JSON';
    
    /**
     * format data from url parameter.
     * @var string
     */
    const FORMAT_URL_PARAM  = 'URLParam';
    
    /**
     * format data from js callback string.
     * @var string
     */
    const FORMAT_JS_CALLBACK_JSON = 'JSCallbackJSON';
}