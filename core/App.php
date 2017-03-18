<?php
/**
 * Created by rozbo at 2017/3/18 下午8:52
 */

namespace puck;


class App extends Container {

    public function __construct() {
        $this->initContainer();
    }

    /**
     * 初始化容器
     */
    private function initContainer() {
        static::setInstance($this);
        $this->instance('app',$this);
        $this->bind('pinyin','\puck\helpers\PinYin');
    }

}