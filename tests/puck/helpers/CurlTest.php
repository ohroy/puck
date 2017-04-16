<?php
/**
 * Created by rozbo at 2017/4/15 下午3:00
 */

namespace tests\puck\helpers;

use PHPUnit\Framework\TestCase;

class CurlTest extends TestCase {


    public function testGet() {
        $curl=app('curl');
        $curl->get('http://ip.taobao.com/service/getIpInfo.php?ip=8.8.8.8');
        $this->assertFalse($curl->error);
        $ret=json_decode($curl->response);
        $this->assertTrue($ret->code==0);
    }
    public function testGetByArray() {
        $curl=app('curl');
        $curl->get('http://ip.taobao.com/service/getIpInfo.php',['ip'=>'8.8.8.8']);
        $this->assertFalse($curl->error);
        $ret=json_decode($curl->response);
        $this->assertTrue($ret->code==0);
    }
    public function testGetByHttps() {
        $curl=app('curl');
        $curl->get('http://ip.taobao.com/service/getIpInfo.php?ip=8.8.8.8');
        $this->assertFalse($curl->error);
        $ret=json_decode($curl->response);
        $this->assertTrue($ret->code==0);
    }
}
