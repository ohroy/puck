<?php
/**
 * Created by rozbo at 2017/3/18 下午9:07
 */

namespace tests\puck;

use \PHPUnit\Framework\TestCase;
use puck\App;


class AppTest extends TestCase {
    public function testApp() {
        $flag=app() instanceof App;
        $this->assertTrue($flag);
    }
}
