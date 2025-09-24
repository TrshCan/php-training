<?php
class RedisClient {
    private static $instance = null;

    public static function get() {
        if (self::$instance === null) {
            $redisHost = getenv('REDIS_HOST') ?: 'redis-15536.c340.ap-northeast-2-1.ec2.redns.redis-cloud.com';
            $redisPort = getenv('REDIS_PORT') ?: 15536;
            $redisPass = getenv('REDIS_PASS') ?: 'Zv2RPw3F12R1jOZX1FAXqDflCbUnvAkj';

            $redis = new Redis();

            // TLS connect
            if (!$redis->connect($redisHost, $redisPort, 2.5, null, 0, 0, ['ssl' => ['verify_peer' => false]])) {
                throw new Exception("Failed to connect to Redis Cloud");
            }

            if (!$redis->auth($redisPass)) {
                throw new Exception("Redis authentication failed");
            }

            self::$instance = $redis;
        }

        return self::$instance;
    }
}
