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
use think\facade\Cache;

class Token
{
    /**
     * 访问第三方违章查询平台的AccessToken
     * @var string
     */
	public $access_token = '';
	
    /**
     * 用于加密数据时的切割
     * @var string
     */
    public $authfix = 'NIUBEA.COM ';
    
    /**
	 * 获取第三方违章查询平台的access_token
	 */
	protected function get_access_token() {
        if (!empty($this->access_token)) {
            return $this->access_token;
		}
		$cache = config('wzcx.appid') . '_access_token';
		//echo $cache;die;
		$this->access_token = cache($cache);
        if (!empty($this->access_token)) {
            return $this->access_token;
        }		
		$appid = config('wzcx.appid');
		$appsecret = config('wzcx.appsecret');
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

		
		$url = config('wzcx.api_url') . "/v1/token";
		
		// echo "<pre>access_token:" . ($this->access_token)."**end";
		try {
			$result = curl_post($url, $params);

			// echo $result;die;
			
			$array_return = json_decode($result, true);

			if($array_return ["code"] != 200) {
				self::returnMsg(401, "access_token获取失败，原因：" . $array_return["message"]);
			}

			$this->access_token = $array_return['data']['access_token'];


			if (!empty($this->access_token)) {
				cache($cache, $this->access_token, 7000);
				return $this->access_token;
			}
			// throw new Exception("access_token取不了");
			return false;
		} catch (Exception $e) {
			self::returnMsg(401, "access_token获取失败");
		}
    }
    
    /**
     * 获取Authorization
     */
    public function get_authorization($token) {
		$appid = config('wzcx.appid');
		// echo $token . "iamtoken";
        return $this->authfix . base64_encode($appid . ":" . $this->access_token . ":" . "9527");
    }

	/**
	 * 第三方违章查询平台生成签名算法
	 */
	protected static function _make_sign($params = [], $appsecret) {
        unset($params['version']);
        unset($params['sign']);
        return self::_get_order_md5($params, $appsecret);
	}
	
	/**
     * 计算第三方违章查询平台的MD5签名
     */
    protected static function _get_order_md5($params = [], $appsecret) {
        ksort($params);
        $params['appsecret'] = $appsecret;
        // echo '<pre>' . print_r($params);
        // echo urldecode(http_build_query($params))."|||||||||||";
        return strtolower(md5(urldecode(http_build_query($params))));
	}
	
	/**
	 * 生成 nonce
	 */
	protected static function _make_nonce () {
		return md5(uniqid(microtime(true),true));
	}
	
     /* PHP CURL HTTPS POST */
     protected function curl_post($url, $data, $header = []){ // 模拟提交数据函数
        $curl = curl_init(); // 启动一个CURL会话
        curl_setopt($curl, CURLOPT_URL, $url); // 要访问的地址
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0); // 对认证证书来源的检查
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0); // 从证书中检查SSL加密算法是否存在
        curl_setopt($curl, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']); // 模拟用户使用的浏览器
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1); // 使用自动跳转
        curl_setopt($curl, CURLOPT_AUTOREFERER, 1); // 自动设置Referer
        curl_setopt($curl, CURLOPT_POST, 1); // 发送一个常规的Post请求
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data); // Post提交的数据包
        curl_setopt($curl, CURLOPT_TIMEOUT, 30); // 设置超时限制防止死循环
        curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
        // 返回 response_header, 该选项非常重要,如果不为 true, 只会获得响应的正文
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1); // 获取的信息以文件流的形式返回
        $tmpInfo = curl_exec($curl); // 执行操作
        if (curl_errno($curl)) {
            return 'Errno'.curl_error($curl);//捕抓异常
        }
        curl_close($curl); // 关闭CURL会话
        return $tmpInfo; // 返回数据，json格式
	}
	
	/**
	 *  格式化输出
	 */
	protected function json_cn($data) {
		return json_encode($data, JSON_UNESCAPED_UNICODE);
	}
    
	/**
	 * 返回成功
	 */
	public static function returnMsg($code = 200, $message, $data = [], $header = [], $error = '', $die = true)
	{	
        if($error == "") $error = $message;
		// http_response_code($code);    //设置返回头部
        $return['code'] = (int)$code;
        $return['message'] = $message;          //中文的提示信息
        $return['error'] = $error;                  //错误代号
        $return['data'] = is_array($data) ? $data : ['info'=>$data];
        // 发送头部信息
        foreach ($header as $name => $val) {
            if (is_null($val)) {
                header($name);
            } else {
                header($name . ':' . $val);
            }
        }
		echo (json_encode($return, JSON_UNESCAPED_UNICODE));
		die;
	}
}

?>