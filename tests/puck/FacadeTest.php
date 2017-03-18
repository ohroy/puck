<?php
/**
 * Created by rozbo at 2017/3/17 下午12:10
 */

namespace tests\puck;

use \PHPUnit\Framework\TestCase;
use puck\Facade;
use puck\helpers\Pinyin;


class FacadeTest extends TestCase {

    public function testBind() {
        dump(static::class);
        Facade::bind('pinyin', Pinyin::class);
        $a=Facade::make('pinyin')->convert('我要你永远爱我');
        $b=['wo','yao','ni','yong','yuan','ai','wo'];
        $this->assertEquals($b,$a);
    }
}
