<?php
/**
 * Created by rozbo at 2017/4/15 下午3:00
 */

namespace tests\puck\helpers;

use PHPUnit\Framework\TestCase;
use puck\tools\Str;
class CurlTest extends TestCase {

    protected function setUp(){
        $bashPath=app()->basePath();
        if(!Str::startsWith($bashPath,'/home/scrutinizer')){
            $this->markTestSkipped(
                '非`ci`模式下不做调试'
            );
        }

    }
    public function testGet() {
        $curl=app('curl');
        $curl->setTimeout(10);
        $curl->get('http://www.weather.com.cn/data/cityinfo/101010100.html');
        $this->assertFalse($curl->error);
        $tmp=json_decode($curl->response);
        $this->assertEquals("北京",$tmp->weatherinfo->city);
    }
    public function testGetByArray() {
        $curl=app('curl');
        $curl->setTimeout(10);
        $curl->get('http://ip.taobao.com/service/getIpInfo.php',['ip'=>'8.8.8.8']);
        $this->assertFalse($curl->error);
        $ret=json_decode($curl->response);
        $this->assertTrue($ret->code==0);
    }
    public function testGetByHttps() {
        $curl=app('curl');
        $curl->setTimeout(100);
        $curl->get('https://api.map.baidu.com/geocoder?location=40,118&output=json');
        $this->assertFalse($curl->error);
    }
}
