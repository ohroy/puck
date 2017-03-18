<?php
/**
 * Created by rozbo at 2017/3/18 下午9:13
 */

namespace tests\puck\tools;

use \PHPUnit\Framework\TestCase;
use puck\tools\Str;


class StrTest extends TestCase{
    public function testAll() {
        $this->assertTrue(Str::contains("abc","b"));
        $this->assertTrue(Str::endsWith("abc","c"));
    }
}
