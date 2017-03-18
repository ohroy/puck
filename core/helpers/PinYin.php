<?php
/**
 * Created by IntelliJ IDEA.
 * User: rozbo
 * Date: 2017/2/28
 * Time: 下午4:50
 */

namespace puck\helpers;

use Overtrue\Pinyin\Pinyin as VendorPinyin;
class PinYin extends VendorPinyin {

    public function noun($str) {
        $pinyinArr=$this->convert($str);
        foreach ($pinyinArr as &$pinyin) {
            //首字母转大写
            $pinyin[0]=strtoupper($pinyin[0]);
        }
        return implode('', $pinyinArr);
    }
}