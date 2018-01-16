<?php
namespace X\Service\QQConnect;
/**
 * @author admin
 */
class QZone extends Production {
    /**
     * 获取网站登录用户信息，目前可获取用户在QQ空间的昵称、头像信息及黄钻信息。
     * @return array
     */
    public function getInfo() {
        return $this->doRequest('user/get_user_info');
    }
}