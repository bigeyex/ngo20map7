<?php
    
    import('@.Classes.Wechat');
    
    class CachedWechat extends Wechat{
        protected static $redis = null;
        
        static function getRedisInstance(){
            if(class_exists('Redis')){
                if(self::$redis !== null){
                    return self::$redis;
                }
                else{
                    $redis = new Redis();
                    $redis->connect('127.0.0.1',6379);
                    self::$redis = $redis;
                    return $redis;
                }
            }
            else{
                return false;
            }
        }
        
        protected function setCache($cachename,$value,$expired){
            if(class_exists('Redis')){
                $redis = self::getRedisInstance();
                $cacheKey = C('APP_NAME') . $cachename;
                $redis->set($cacheKey, $value);
                $now = time(NULL);
                $redis->expireAt($cacheKey, $now+$expired);
            }
        		return false;            		
        	}
        
        	/**
        	 * 获取缓存，按需重载
        	 * @param string $cachename
        	 * @return mixed
        	 */
        	protected function getCache($cachename){
        		//TODO: get cache implementation
        		if(class_exists('Redis')){
            		$redis = self::getRedisInstance();
                $cacheKey = C('APP_NAME') . $cachename;
                $value = $redis->get($cacheKey);
                if($value === FALSE){
                    return false;
                }
                else{
                    return $value;
                }
            }
            else{
                return false;
            }
        		
        	}
        
        	/**
        	 * 清除缓存，按需重载
        	 * @param string $cachename
        	 * @return boolean
        	 */
        	protected function removeCache($cachename){
        		//TODO: remove cache implementation
        		$redis = self::getRedisInstance();
            $cacheKey = C('APP_NAME') . $cachename;
            $redis->delete($cacheKey);
        		return false;
        	}
    }