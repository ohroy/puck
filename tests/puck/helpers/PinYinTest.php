<?php
/**
 * Created by rozbo at 2017/3/18 下午9:17
 */

namespace tests\puck\helpers;
use \PHPUnit\Framework\TestCase;
class PinYinTest extends TestCase{
    public function testAll() {
        $a=app()->make('pinyin')->convert('我要你永远爱我');
        $b=['wo','yao','ni','yong','yuan','ai','wo'];
        $this->assertEquals($b,$a);
    }
}