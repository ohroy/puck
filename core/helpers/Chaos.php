<?php
namespace puck\helpers;
class Chaos {
	public $GbToBigArray;
	public $_pinyin;
	public $string;
	public $url='';
	public $msg='';
	public $action;
	private $prefix;
	
    function __construct() {
		$this->_pinyin=new PinYin();
	}

    /**
     * @param array $prefix
     */
    public function addPrefix(array $prefix) {
        $this->prefix=$prefix[array_rand($prefix)];
	}

    public function clearPrefix() {
        $this->prefix='';
	}
    public function get($str) {
        $this->cutting($str);
        $this->convert();
        $this->clearPrefix();
        return $this->msg;
	}

	//分离URL
	private function cutting($str) {

		if (preg_match("@(http://[^\\s]+)@i", $str, $result)) {
			$this->url=$result[1];
			$this->msg=$str=str_replace($this->url, '%%%%', $str);
		} else {
			$this->msg=$str;
		}
	}
	
	
	
	private function convert($method='msg') {
		if ($method == 'msg' || $method == 'all') {
			$this->msg=$this->setPinyin($this->msg);
			$this->msg=$this->setRepeat($this->msg);
			//$this->msg = $this->GbToBig($this->msg);
			$this->msg=$this->setBlankness($this->msg);
			
		}
		if ($method == 'url' || $method == 'all') {
			$this->url=$this->setChacha($this->url);
		}
		$this->msg=$this->prefix.str_replace('%%%%', $this->url, $this->msg);
	}
	
	/**
	 * @param string $url
	 */
	function setChacha($url) {
		$url=strtolower($url);
		$arr=array(
			'a' => array('a', 'A', 'ａ', 'Ａ', 'Α', 'А', 'α'),
			'b' => array('b', 'B', 'ｂ', 'Ｂ', 'Β', 'В', 'Ь'),
			'c' => array('c', 'C', 'ｃ', 'Ｃ', 'С', 'с'),
			'd' => array('d', 'D', 'ｄ', 'Ｄ'),
			'e' => array('e', 'E', 'ｅ', 'Ｅ', 'Ε', 'Е', 'е'),
			'f' => array('f', 'F', 'ｆ', 'Ｆ'),
			'g' => array('g', 'G', 'ｇ', 'Ｇ'),
			'h' => array('h', 'H', 'ｈ', 'Ｈ', 'Η', 'Н', 'н'),
			'i' => array('i', 'I', 'ｉ', 'Ｉ', 'Ι', 'Ⅰ'),
			'j' => array('j', 'J', 'ｊ', 'Ｊ'),
			'k' => array('k', 'K', 'ｋ', 'Ｋ', 'Κ', 'κ', 'к', 'К'),
			'l' => array('l', 'L', 'ｌ', 'Ｌ', '︱', '︳', '|'),
			'm' => array('m', 'M', 'ｍ', 'Ｍ', 'Μ', 'М', 'м'),
			'n' => array('n', 'N', 'ｎ', 'Ｎ', 'Ν', '∩'),
			'o' => array('o', 'O', 'ｏ', 'Ｏ', 'Ο', 'О'),
			'p' => array('p', 'P', 'ｐ', 'Ｐ', 'Ρ', 'Р', 'р'),
			'q' => array('q', 'Q', 'ｑ', 'Ｑ'),
			'r' => array('r', 'R', 'ｒ', 'Ｒ'),
			's' => array('s', 'S', 'ｓ', 'Ｓ'),
			't' => array('t', 'T', 'ｔ', 'Ｔ', 'Τ', 'Т', 'ㄒ'),
			'u' => array('u', 'U', 'ｕ', 'Ｕ', '∪'),
			'v' => array('v', 'V', 'ｖ', 'Ｖ', '∨', 'ν'),
			'w' => array('w', 'W', 'ｗ', 'Ｗ'),
			'x' => array('x', 'X', 'ｘ', 'Ｘ', 'Χ', 'χ', 'Х', 'х', 'Ⅹ', '×'),
			'y' => array('y', 'Y', 'ｙ', 'Ｙ', 'У'),
			'z' => array('z', 'Z', 'ｚ', 'Ｚ', 'Ζ'),
			
			'1' => array('1', '１'),
			'2' => array('2', '２'),
			'3' => array('3', '３'),
			'4' => array('4', '４'),
			'5' => array('5', '５'),
			'6' => array('6', '６'),
			'7' => array('7', '７'),
			'8' => array('8', '８'),
			'9' => array('9', '９'),
			'0' => array('0', '０'),
			
			':' => array(':', '：', '∶'),
			'/' => array('/', '／'),
			'.' => array('。', '·', '．', '、', '﹒', '，', '丶')
		
		);
		$len=strlen($url);
		$temp="\n\n";
		for ($i=0; $i < $len; $i++) {
			$t_str=substr($url, $i, 1);
			$sj=mt_rand(0, count($arr[$t_str]) - 1);
			$temp.=$arr[$t_str][$sj];
		}
		return $temp;
	}

	//随机把一个字符转为拼音

	/**
	 * @param string $str
	 */
	function setPinyin($str) {
		$py = mt_rand(0, iconv_strlen( $str, 'UTF-8' )-1);
		$t_str = iconv_substr( $str, $py, 1, 'UTF-8');
		if(mt_rand(0,10) > 5) {
		    $pinyin = " ";
		}
		$pinyin = $this->_pinyin->convert($t_str,PINYIN_UNICODE);
        $pinyin=implode(" ",$pinyin);
		if(mt_rand(0,10) > 5) {
		    $pinyin .= " ";
		}
		if($t_str != "%"){
			$str = preg_replace("'$t_str'", $pinyin, $str, 1);
		}
		return $str;
	}
	
	//随机重复一个字符

	/**
	 * @param string $str
	 */
	function setRepeat($str) {
		$len = iconv_strlen( $str, 'UTF-8' );
		$action = 0;
		$temp = '';
		for( $i=0;  $i<$len; $i++ ){
			$t_str = iconv_substr( $str, $i, 1 ,'UTF-8');
			if( mt_rand( 1, 50 ) > 48 && $action == 0) {
				if(!preg_match("@[a-z0-9%\\s]+@i", $t_str)) {
					$temp .= $t_str;
					$action = 1;
				}
			}
			$temp .= $t_str;
		}
		return $temp;
	}
	
	//随机插入不影响阅读的字符

	/**
	 * @param string $str
	 */
	function setBlankness($str) {
		$blankness=array(" ", '　', '҉', '̅̅', '̲', '̲̲', '̅', '̲̲̅̅');
		$len=iconv_strlen($str, 'UTF-8');
		$temp='';
		for ($i=0; $i < $len; $i++) {
			$t_str=iconv_substr($str, $i, 1, 'UTF-8');
			if (mt_rand(1, 50) > 48) {
				if (!preg_match("@[a-z0-9%\\s]+@i", $t_str)) {
					$temp.=$blankness[mt_rand(0, 7)];
				}
			}
			$temp.=$t_str;
		}
		return $temp;
	}
	
	//随机进行繁体中文转换
	function GbToBig($str) {
		$len=iconv_strlen($str, 'UTF-8');
		$temp='';
		for ($i=0; $i < $len; $i++) {
			$t_str=iconv_substr($str, $i, 1, 'UTF-8');
			if (mt_rand(1, 50) > 48) {
				$t_str=strtr($t_str, $this->GbToBigArray);
			}
			$temp.=$t_str;
		}
		return $temp;
	}
}


