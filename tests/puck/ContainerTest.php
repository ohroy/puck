<?php
/**
 * Created by rozbo at 2017/4/19 下午5:39
 */

namespace tests\puck;

use PHPUnit\Framework\TestCase;
use puck\Container;

class ContainerTest extends TestCase {
    private $container;

    public function setUp() {
        $this->container = Container::getInstance();
    }

    public function testRegexBind() {
        $this->container->regexBind('#^(\w+)_model$#', "\\tests\\app\\models\\\\$1");
        $model = $this->container->make("test_model");
        $this->assertTrue($model instanceof \tests\app\models\Test);
        $this->assertTrue($model->flag);
    }

    public function testSnakeRegexBind() {
        $this->container->regexBind('#^(\w+)_model$#', "\\tests\\app\\models\\\\$1");
        $model = $this->container->make("user_test_model");
        $this->assertTrue($model instanceof \tests\app\models\UserTest);
        $this->assertTrue($model->flag);
    }
}
