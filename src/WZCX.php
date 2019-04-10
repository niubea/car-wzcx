<?php
/*
 *  违章查询
 *  李波 <libo@usa.com>
 *  请注意及时更新 IP 数据库版本
 *  Code for ThinkPHP 5.1.* only
 */

namespace libo\car_wzcx;

use think\Request;
use think\Exception;
use think\facade\Config;
use think\facade\Cache;
use libo\car_wzcx\Token;

class WZCX extends Token
{
    /**
     * 访问第三方违章查询平台的AccessToken
     * @var string
     */
    public $access_token = '';

    /**
     * 返回给商家的标准输出
     */
    public $return_fomat = array(
        "code"  =>  "40301",
        "message"   =>  "不合法的access_token",
        "error"   =>  "illegal access_token",
        "data"  =>  []
    );

    public function __construct() {
        // echo "__construct";
        $this->get_access_token();
        // echo $this->access_token."cao";
    }
    
    /**
	 * 获取第三方违章查询平台的access_token
	 */
	public function car_query($params) {
        $appsecret = config("wzcx.appsecret");
		$sign = $this->_make_sign($params, $appsecret);

		$params["sign"] = $sign;

		$url = config('wzcx.api_url') . "/v1/car_query";
        
        $header = [
            "Authorization: " . $this->get_authorization($this->access_token)
        ];
		// echo "<pre>" . print_r($header);
		try {
            $result = $this->curl_post($url, $params, $header);
            return $result;
		} catch (Exception $e) {
            self::returnMsg(403, "查询失败，请稍候重试");
		}
    }

    
    /**
	 * 获取第三方违章查询平台的access_token
	 */
	public function driver_query($params) {
        $appsecret = config("wzcx.appsecret");
		$sign = $this->_make_sign($params, $appsecret);

		$params["sign"] = $sign;

		$url = config('wzcx.api_url') . "/v1/driver_query";
        
        $header = [
            "Authorization: " . $this->get_authorization($this->access_token)
        ];
		// echo "<pre>" . print_r($header);
		try {
            $result = $this->curl_post($url, $params, $header);
            return $result;
		} catch (Exception $e) {
            self::returnMsg(403, "查询失败，请稍候重试");
		}
    }
    
    
}

?>