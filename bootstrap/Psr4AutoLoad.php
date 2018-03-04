<?php
/**
 * Created by PhpStorm.
 * User: chenbingjie
 * Date: 2018/2/8
 * Time: 上午9:50
 */

class Psr4AutoLoad
{
    //定义一个空数组，专门保存映射关系
    // ['controller'=>'app/controller','model'=>'app/model']
    public $maps = [];
    public function __construct()
    {
        //将自己定义的自动加载的函数进行激活，若未激活，自动加载的功能不能实现。当实例化这个类的时候，会自动将函数激活。
        spl_autoload_register([$this,"myautoload"]);
    }
    //这是自己定义的实现自动加载的函数
    function myautoload($className)
    {
        //var_dump($className);   //‘controller/IndexController’
        //将完整的类名进行分割，得到命名空间名和类名,在完整的类名中得到目录分隔符最后一次出现的位置
        $pos = strrpos($className,'\\');
        //var_dump($pos);====>10

        //提取命名空间的名字   返回的是  ‘controller’
        $namespace = substr($className,0,$pos);
        //提取类名
        $relClass = substr($className,$pos + 1);
        //查找关系映射的函数是mapLoad   参数：第一个是命名空间名，第二个是真实类名
        $this->mapLoad($namespace,$relClass);

    }
    //查找关系映射的函数是mapLoad   参数：第一个是命名空间名，第二个是真实类名
    protected function mapLoad($namespace,$relClass)
    {
        if (array_key_exists($namespace,$this->maps)) {

            //获取真实的文件路径
            $namespace = $this->maps[$namespace];
        }
        //如果不存在映射关系，自己拼接路径,将命名空间中的斜线给统一一下
        $namespace = rtrim(str_replace('\\/','/',$namespace),'/').'/';

        //拼接完整的文件路径
        $filePath = $namespace.$relClass.'.php';

        //将要实例化的类所在的文件给加载进来
        if (!$this->require($filePath)) {
            die('此文件不存在');
        }
    }
    //封装加载文件的方法
    protected function require($filePath)
    {
        if (file_exists($filePath)) {
            include $filePath;
            return true;
        }
        return false;
    }
    //关系映射就是来解决命名空间和文件保存的真实路径不一致的情况
    //第一个参数是：命名空间名     第二个参数是文件真实的路径

    public function addMaps($namespace,$path)
    {
        if (array_key_exists($namespace,$this->maps)) {
            die('此命名空间已经映射过了');
        }
        //若没有映射过，需要将命名空间和真实的路径添加到映射关系的数组中去。
        $this->maps[$namespace] = $path;
    }
}