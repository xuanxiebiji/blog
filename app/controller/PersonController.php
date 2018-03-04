<?php
/**
 * Created by PhpStorm.
 * User: 图图
 * Date: 2018/02/21
 * Time: 21:53
 */

namespace controller;
use model\PersonModel;
use framework\Page;
class PersonController extends Controller
{
    //定义一个成员属性，保存PersonModel实例化后的对象
    public $person;
    public function __construct()
    {
        parent::__construct();
        $this->person = new PersonModel();
    }


    //这是留言的方法
    public function words()
    {
        $this->display('Person/words.html');
    }

    //这是把留言内容写进数据库的方法
    public function write()
    {
        //先判断用户有没有登录
        if(empty($_SESSION['id'])){
            $msg = '抱歉你还未登录, 请登录后再留言';
            $this->notice($msg);
            die;
        }

        //接收留言者的信息   id是数据表中的uid
        $id = $_SESSION['id'];
        $name = $_POST['username'];
        $email = $_POST['email'];
        $tel = $_POST['tel'];
        $title = $_POST['title'];
        $content = $_POST['content'];
        $ip = $_SERVER['REMOTE_ADDR'];
        //对接收过来的ip进行判断
        if($ip == '::1' ){
            $ip = '127.0.0.1';
        }

        //对接收的信息进行判断
        if (empty($name)){
            $msg = '请输入用户昵称进行留言';
            $this->notice($msg);
            die;
        }
        if (empty($email)){
            $msg = '请输入邮箱进行留言';
            $this->notice($msg);
            die;
        }
        if (empty($tel)){
            $msg = '请输入联系方式进行留言';
            $this->notice($msg);
            die;
        }
        if (empty($title)){
            $msg = '请输入留言标题';
            $this->notice($msg);
            die;
        }
        if (empty($content)){
            $msg = '请输入留言内容';
            $this->notice($msg);
            die;
        }
        //匹配邮箱地址
        $str = preg_match("/\w+([-+.\']\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*/",$email);
        if(!$str){
            $msg = '你输入的邮箱格式有误，请重新输入';
            $this->notice($msg);
            die;
        }
        //匹配电话号码
        $str1 = preg_match("/^\d{7,11}/",$tel);
        if(!$str1){
            $msg = '你输入的电话号码格式有误，请重新输入';
            $this->notice($msg);
            die;
        }

        //验证数据后将数据写入数据库中
        $data['uid'] = $id;
        $data['wordname'] = $name;
        $data['email'] = $email;
        $data['tel'] = $tel;
        $data['title'] = $title;
        $data['content'] = $content;
        $data['addip'] = $ip;
        $result = $this->person->table('words')->insert($data);
        if($result){
            $msg = '留言成功';
            $this->notice($msg,'index.php?c=person&a=leave');
            die;
        }else{

            $msg = '留言失败';
            $this->notice($msg);
            die;
        }
    }

    //这里是显示留言内容的
    public function leave()
    {
        //查询总共的留言数（查id）
        $sum  =  $this->person->table('words')->field('count('."id".') as count')->select();
        //总条数
        $totalCount = $sum['0']['count'];
        //实例化一个分页对象
        $page = new Page(6,$totalCount);
        //分页里面的url地址
        $count = $page->allPage();
        //分配到模板文件里去
        $this->assign('count',$count);
        //查出limit限制条件
        $limit = $page->limit();
        //查询用户留言的所有信息
        $list = $this->person->table('words')->field('*')->order('id desc')->limit($limit)->select();
        $this->assign('list',$list);
        $this->display('Person/talk.html');

    }
}