# car-wzcx
车辆违章查询SDK，TP5.1以上版本可用  

  
车牌号查询调用方法：  
1. 根目录 composer require libo/car-wzcx 下载本插件包  
2. 本插件在安装的时候会给你在config目录下生成一个配置文件：wzcx.php  
3. 在此文件里修改自己的配置，填写appid和secret，数据从平台后台获取，如果没有就去创建应用，创建应用后会生成appid和secret  
4. 在需要用到查询的控制器里引入插件：use libo\car_wzcx\WZCX;  
5. 在需要用到查询的方法里，通过以下代码去进行查询：    
    try {   
        $params = [   
            "car_type"   =>  $car_type,      
            "car_number"   =>  $car_number,   
            "car_vin_number"  =>  $car_vin_number,   
            "car_engine_number"    =>  $car_engine_number   
        ];   
        $wzcx = new WZCX();   
        $order_curl_json = $wzcx->car_query($params);   
        $array_return = json_decode($order_curl_json, true);   
        $return_code = $array_return["code"];   
        
        if($return_code == 200) {   
            return self::returnMsg(200, '查询成功', $array_return["data"]);   
        } else {   
            return self::returnMsg(403, '查询失败，原因：' . $order_curl_json);   
        }   
    } catch (Exception $e) {   
        return self::returnMsg(500, $e->getMessage());   
    }   

驾驶证查询调用方法：  
1. 根目录 composer require libo/car-wzcx 下载本插件包  
2. 本插件在安装的时候会给你在config目录下生成一个配置文件：wzcx.php  
3. 在此文件里修改自己的配置，填写appid和secret，数据从平台后台获取，如果没有就去创建应用，创建应用后会生成appid和secret  
4. 在需要用到查询的控制器里引入插件：use libo\car_wzcx\WZCX;  
5. 在需要用到查询的方法里，通过以下代码去进行查询：    
   try {   
        $params = [   
            "driver_number"  =>  $driver_number,   
            "driver_file_number"    =>  $driver_file_number   
        ];   
        $wzcx = new WZCX();   
        $order_curl_json = $wzcx->driver_query($params);   
        $array_return = json_decode($order_curl_json, true);   
        $return_code = $array_return["code"];      
        
        if($return_code == 200) {   
            return self::returnMsg(200, '查询成功', $array_return["data"]);   
        } else {   
            return self::returnMsg(403, '查询失败，原因：' . $order_curl_json);   
        }   
    } catch (Exception $e) {   
        return self::returnMsg(500, $e->getMessage());   
    }   
