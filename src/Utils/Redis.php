<?php
/**
 * Created by PhpStorm.
 * User: yus
 * Date: 13/07/18
 * Time: 0:00
 */

namespace App\Utils;

define('_REDIS_SCHEME_','tcp');
define('_REDIS_SERVER_', '127.0.0.1');
define('_REDIS_PORT_',6379);
define('_REDIS_HASH_','6');


class Redis
{


    public function redis($db=null)
    {
        try {
            if($db==null){
                $db = _REDIS_HASH_;
            }
            return new \Predis\Client([
                'scheme'   => _REDIS_SCHEME_,
                'host'     => _REDIS_SERVER_,
                'port'     => _REDIS_PORT_,
                'database' => $db,
            ]);
        } catch (\Exception $e) {
            return null;
        }
    }


}