<?php
/**
 * Created by PhpStorm.
 * User: 啦啦啦
 * Date: 2018/2/8
 * Time: 下午1:44
 */
namespace controller;
use framework\Code;
use model\UserModel;
class UserController extends Controller
{
    //定义一个成员属性，保存userModel实例化后的对象
    public $user;
    public function __construct()
    {
      parent::__construct();
      $this->user = new UserModel();
    }

    public function register()
    {
        $this->display();
    }
    //将用户的信息注册到数据库中的方法
    public function doRegister()
    {
        //接收用户的信息
        $name = $_POST['username'];
        $pwd = $_POST['password'];
        $repwd = $_POST['repassword'];
        $email = $_POST['email'];
        $verify = $_POST['yzm'];
        //var_dump($_POST);
        //判断用户信息的规范
        if (empty($name)){
            $msg = '用户名不能为空';
            $this->notice($msg,null,3);
            exit;
        }
        if (empty($pwd)){
            $msg = '密码不能为空';
            $this->notice($msg,null,3);
            exit;
        }
        if (strlen($name) < 3){
            $msg = '用户名长度不少于3位';
            $this->notice($msg,null,3);
            exit;
        }
        if (strlen($pwd) < 6){
            $msg = '密码设置长度不少于6位';
            $this->notice($msg,null,3);
            exit;
        }
        if (strcmp($pwd,$repwd)){
            $msg = '两次密码不一致';
            $this->notice($msg);
            exit;
        }
        if (strcasecmp($verify,$_SESSION['code'])) {
            $msg = '验证码输入错误';
            $this->notice($msg,null,3);
            exit;
        }
        //更好的用户体验，查询一下数据库中是否有用户重名的情况
        $result = $this->user->table('user')->where("name='$name'")->field('id')->select();
        if($result){
            $msg = '用户已被注册,请更换用户名';
            $this->notice($msg);
            exit;
        }
        $ip = $_SERVER['REMOTE_ADDR'];
        //将IP地址进行转换
        if($ip == '::1'){
            $ip = '127.0.0.1';
        }
        $data['name'] = $name;
        $data['password'] = md5($pwd);
        $data['email'] = $email;
        $data['regip'] = $ip;
        $result1 =  $this->user->table('user')->insert($data);
        if ($result1) {
            $this->notice('注册成功','index.php');
        }else {
            $this->notice('注册失败',null,3);
        }
    }
    //定义verify的方法，用于显示验证码
    public function verify()
    {
        $code = new Code();
        $code->outImage();
        //将验证码保存到session中去
        $_SESSION['code'] = $code->code;
    }
    //用户登陆的方法
    public function login()
    {
        $this->display();
    }
    //验证用户是否登录成功的方法
    public function doLogin()
    {
        //接收登录的用户名信息
        $name = $_POST['username'];
        $pwd = md5($_POST['password']);
        $verify = $_POST['verify'];
        //判断验证码信息
        if (strcasecmp($verify,$_SESSION['code'])){
            $this->notice('验证码不对，请重新输入');
            die;
        }
        $result2 = $this->user->where("name = '$name'"and "password = '$pwd'")->select();
        if (empty($result2)) {
            $this->notice('此用户不存在');
            die;
        }
        //查询用户密码是否正确
        $result3 = $this->user->table('user')->field(['id','name','password','udertype'])->where("name='$name'","password='$pwd'")->select();
        //var_dump($result3);

        if ($result3){
            //保存一份用户名信息
            $_SESSION['name'] = $name;
            $_SESSION['id'] = $result3[0]['id'];
            $_SESSION['udertype'] = $result3[0]['udertype'];
            $this->notice('登陆成功,三秒后自动跳转首页','index.php');
        }else{
            $msg = '密码不正确,请重新输入';
            $this->notice($msg);
            exit;
        }

    }

    //验证用户是否退出的方法
    public function doOut()
    {
        if(isset($_SESSION['id'])){
            session_unset();
            session_destroy();
            $this->notice('退出成功，正在跳转首页','index.php');

        }
    }
}