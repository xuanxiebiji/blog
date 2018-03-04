<?php
/**
 * Created by PhpStorm.
 * User: chenbingjie
 * Date: 2018/2/8
 * Time: 上午9:51
 */
class Start
{
    //定义一个成员属性，来保存自动加载类的对象
    static public $auto;
    static function init()
    {
        self::$auto = new Psr4AutoLoad();
    }

    static function router()
    {
        $c = empty($_GET['c']) ? 'index':$_GET['c'];
        $a = empty($_GET['a']) ? 'index':$_GET['a'];

        //给$_GET默认值
        $_GET['c'] = $c;
        $_GET['a'] = $a;

        //得到类的名字
        $className = '\\controller\\'.ucfirst($c).'Controller';

        $obj = new $className();
        call_user_func([$obj,$a]);

    }
}
Start::init();





