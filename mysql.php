<?php
/**
 * Created by IntelliJ IDEA.
 * User: rozbo
 * Date: 2017/2/27
 * Time: 下午2:45
 */

namespace export;


class mysql extends \MysqliDb{

    static public function getDb($db){
        static $dbList;
        if(!isset($dbList[$db])){
            $dbList[$db]=new \MysqliDb(config('db.'.$db));
        }
        return $dbList[$db];
    }


}