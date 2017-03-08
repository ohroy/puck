<?php
namespace core;

class controller
{

    protected $viewPath = '';
    private $twig;
    private $tVar = array();
    protected $title='';
    public function __construct()
    {
        $loader = new \Twig_Loader_Filesystem(BASE_PATH . $this->viewPath);
        $twig = new \Twig_Environment($loader, array(
            'debug' => DEBUG,
            'cache' => BASE_PATH . '/cache',
        ));
        $this->twig = $twig;
        $this->initTwigFilter();
        $this->initTwigFunction();
        $this->db = \MysqliDb::getInstance();
    }
    private function initTwigFilter(){
        $filter = new \Twig_SimpleFilter('long2ip', 'long2ip');
        $this->twig->addFilter($filter);
    }
    private function initTwigFunction(){
        $function = new \Twig_SimpleFunction('I','I');
        $this->twig->addFunction($function);
    }
    protected function assign($name, $value = '')
    {
        if (is_array($name)) {
            $this->tVar = array_merge($this->tVar, $name);
        } else {
            $this->tVar[$name] = $value;
        }
    }

    protected function show($tmpPath = '')
    {
        if ($tmpPath == '') {
            if (defined("CONTROLLER_NAME") && defined("ACTION_NAME")) {
                $tmpPath = CONTROLLER_NAME . '/' . ACTION_NAME;
            } else {
                show_json($this->tVar);
            }
        }
        header('Content-Type:text/html; charset=utf-8');
        header('Cache-control: private'); // 页面缓存控制
        header('X-Powered-By:ViviAnAuthSystem');
        $this->assign('title',$this->title);
        echo $this->twig->render($tmpPath . '.' . TempExt, $this->tVar);
        die();
    }
}