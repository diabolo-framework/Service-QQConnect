<?php
namespace X\Service\QQConnect;
/**
 * handler class to operat qq's tweet.
 * @author Michael Luthor <michaelluthor@163.com>
 */
class Tweet extends Production {
    /**
     * Get information of current account in qq weibo.
     * @return array
     */
    public function getInfo() {
        return $this->doRequest('user/get_info');
    }
    
    /**
     * This value store the location information of current account.
     * @var array
     */
    private $location = array(
        /* 用户所在地理位置的经度。为实数，最多支持10位有效数字。有效范围：-180.0到+180.0，+表示东经，默认为0.0。 */
        'longitude'=>null, 
        /* 用户所在地理位置的纬度。为实数，最多支持10位有效数字。有效范围：-90.0到+90.0，+表示北纬，默认为0.0。 */
        'latitude'=>null);
    
    /**
     * Set location information of current account.
     * @param string $longitude
     * @param string $latitude
     * @return void
     */
    public function setLocation($longitude, $latitude) {
        $this->location['longitude'] = $longitude;
        $this->location['latitude'] = $latitude;
    }
    
    /**
     * This value stores the ip address of client.
     * @var string
     */
    private $clientIp = null;
    
    /**
     * Set client ip address for current account.
     * @param string $ip 
     */
    public function setClientIP( $ip ) {
        $this->clientIp = $ip;
    }
    
    /**
     * send a text message to weibo server.
     * @param string $content
     * @return string
     */
    public function add($content) {
        $params = array();
        $params['content']          = $content;
        $params['clientip']         = $this->clientIp;
        $params['longitude']        = $this->location['longitude'];
        $params['latitude']         = $this->location['latitude'];
        $params = array_filter($params);
        $result = $this->doRequest('t/add_t', $params, false);
        return $result['id'];
    }
    
    /**
     * send a text message to weibo server with a local picture.
     * @param string $content
     * @param string $picPath
     * @return array
     */
    public function addWithPicture( $content, $picPath) {
        $params = array();
        $params['content']          = $content;
        $params['pic']              = function_exists('curl_file_create') ? curl_file_create($picPath) : '@'.$picPath;
        $params['clientip']         = $this->clientIp;
        $params['longitude']        = $this->location['longitude'];
        $params['latitude']         = $this->location['latitude'];
        $params['compatibleflag']   = 0x2|0x4|0x8|0x20;
        $params = array_filter($params);
        $result = $this->doRequest('t/add_pic_t', $params, false);
        return $result['id'];
    }
    
    /**
     * Delete a weibo message by given id.
     * @param string $id
     * @return void
     */
    public function delete( $id ) {
        $this->doRequest('t/del_t', array('id'=>$id), false);
    }
    
    /**
     * get idol list of current account/
     * @param number $offset
     * @param number $length
     */
    public function getIdolList($offset=0, $length=30) {
        $params = array();
        $params['reqnum']       = $length;
        $params['startindex']   = $offset;
        $params['mode']         = 1; /* 新模式，可以拉取所有好友的信息，暂时不支持排序。 */
        $params['install']      = 0; /* 不考虑该参数。 */
        return $this->doRequest('relation/get_idollist', $params);
    }
    
    /**
     * get fans list of current account.
     * @param number $length
     * @param number $offset
     * @param number $sex 0:all;1:man; 2:woman
     */
    public function getFansList($offset=0, $length=30, $sex=0) {
        $params = array();
        $params['reqnum']       = $length;
        $params['startindex']   = $offset;
        $params['mode']         = 1; /* 新模式，可以拉取所有听众的信息，暂时不支持排序。 */
        $params['install']      = 0; /* 不考虑该参数。 */
        $params['sex']          = $sex;
        return $this->doRequest('relation/get_fanslist', $params);
    }
    
    /**
     * get account information by given weibo account name.
     * @param string $name
     * @return array
     */
    public function getUserInfoByName( $name ) {
        return $this->doRequest('user/get_other_info', array('name'=>$name));
    }
    
    /**
     * get account information by given open id.
     * @param string $openId
     * @return array
     */
    public function getUserInfoByOpenId( $openId ) {
        return $this->doRequest('user/get_other_info', array('fopenid'=>$openId));
    }
    
    /**
     * check response value.
     * @param array $response
     * @throws Exception
     * @return array
     */
    protected function checkResponse( $response ) {
        if ( 0 === (int)$response['errcode'] ) {
            return $response['data'];
        } else {
            throw new \Exception($response['msg'], $response['errcode']*1);
        }
    }
}