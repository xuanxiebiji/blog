<?php
namespace controller;
use framework\Tpl;
class Controller extends Tpl
{
    function __construct()
    {
        //实现对tpl类的构造方法的不完全重写
        $config = $GLOBALS['config'];
        parent::__construct($config['TPL_VIEW'],$config['TPL_CACHE']);
    }

    function display($viewName = null,$isInclude = true)
    {
        if (empty($viewName)) {
            //index/index.html
            $viewName = $_GET['c'].'/'.$_GET['a'].'.html';
        }
        //实现对tpl类的display方法的重写
        parent::display($viewName,$isInclude);
    }

    //提示信息的方法
    public function notice($msg,$url = null,$sec = 2)
    {
        //如果没有传递要跳转的url地址，默认返回到来源页
        if (empty($url)) {
            $url = $_SERVER['HTTP_REFERER'];
        }
        $this->assign('msg',$msg);
        $this->assign('url',$url);
        $this->assign('sec',$sec);
        $this->display('notice.html');
    }










}



