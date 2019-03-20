<?php
/*
 *  违章查询需要验证签名，获取令牌
 *  李波 <libo@usa.com>
 *  请注意及时更新 IP 数据库版本
 *  Code for ThinkPHP 5.1.* only
 */

namespace libo\car_wzcx;

use think\Request;
use think\Exception;
use think\facade\Config;
use think\Cache;

class Token
{
    /**
     * 访问第三方违章查询平台的AccessToken
     * @var string
     */
    public $access_token = '';
    
    /**
	 * 获取第三方违章查询平台的access_token
	 */
	public function get_access_token() {
        if (!empty($this->access_token)) {
            return $this->access_token;
        }
        $cache = config('car.pollylee.cn.appid') . '_access_token';
        $this->access_token = Cache::get($cache);
        if (!empty($this->access_token)) {
            return $this->access_token;
        }		
		$appid = config('car.pollylee.cn.appid');
		$appsecret = config('car.pollylee.cn.appsecret');
		$nonce = $this->_make_nonce();
		$now = time();
		$params = array(
			'grant_type'	=>	"client_credential",
			'appid'       =>  $appid,
			'nonce'       =>  $nonce,
			'timestamp'   =>  $now
		);
		$sign = self::_make_sign($params, $appsecret);

		$params["sign"] = $sign;

		$url = "https://api.car.pollylee.cn/token";
		
		try {
			$result = curl_post($url, $params);
			
			$array_return = json_decode($result, true);

			$this->access_token = $array_return['data']['access_token'];

			if (!empty($this->access_token)) {
				Cache::set($cache, $this->access_token, 7000);
			}
			return $this->access_token;
		} catch (Exception $e) {
			return false;
		}
    }
    
    /**
     * 获取Authorization
     */
    public function get_authorization() {
        $appid = config('car.pollylee.cn.appid');
        return $appid . ":" . self::get_access_token() . ":" . "9527";
    }

	/**
	 * 第三方违章查询平台生成签名算法
	 */
	private static function _make_sign($params = [], $appsecret) {
        unset($params['version']);
        unset($params['sign']);
        return self::_get_order_md5($params, $appsecret);
	}
	
	/**
     * 计算第三方违章查询平台的MD5签名
     */
    private static function _get_order_md5($params = [], $appsecret) {
        ksort($params);
        $params['appsecret'] = $appsecret;
        // echo '<pre>' . print_r($params);
        // echo urldecode(http_build_query($params));
        return strtolower(md5(urldecode(http_build_query($params))));
	}
	
	/**
	 * 生成 nonce
	 */
	private static function _make_nonce () {
		return md5(uniqid(microtime(true),true));
    }
    
}

?>