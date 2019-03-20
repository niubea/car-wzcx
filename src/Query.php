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
use think\Cache;
use libo\car_wzcx\Token;

class Query
{
    /**
     * 访问第三方违章查询平台的AccessToken
     * @var string
     */
    public $access_token = '';
    
    /**
	 * 获取第三方违章查询平台的access_token
	 */
	public function query($params) {
        $token = new Token();
        if (!empty($this->access_token)) {
            $this->access_token = $token->get_access_token();
        }
		$sign = $this->_make_sign($params, $appsecret);

		$params["sign"] = $sign;

        $url = "http://api.car.p.cn/token";
        
        $header = [
            "Authorization" => $token->get_authorization()
        ];
		
		try {
			$result = $this->curl_post($url, $params);
			
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
    
     /* PHP CURL HTTPS POST */
     private function curl_post($url, $data, $header = []){ // 模拟提交数据函数
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
    
}

?>