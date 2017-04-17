<?php
/**
 * Created by rozbo at 2017/4/17 下午6:34
 */

namespace puck\helpers;

use DiDom\Document;

class Dom extends Document {
    public function __construct() {
        $this->init();
    }

    public function init($encoding = 'UTF-8') {
        $this->document = new \DOMDocument('1.0', $encoding);
        $this->preserveWhiteSpace(false);
        return $this;
    }

    public function release() {
        unset($this->document);
        return $this;
    }
}