<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/2/25 0025
 * Time: 下午 4:13
 */

namespace controller;

use model\AdminModel;
use framework\Page;
class AdminController extends Controller
{
    //定义一个成员属性，保存AdminModel实例化后的对象
    public $admin;
    public function __construct()
    {
        parent::__construct();
        $this->admin = new AdminModel();
    }

    //这是登录后台管理的方法
    public function adminlogin()
    {
        $this->display('admin/login.html');
    }

    //这是验证用户登录成功的方法
    public function Dologin()
    {
        //接收用户的信息
        $name = $_POST['username'];
        $pwd = $_POST['password'];
        $ip = $_SERVER['REMOTE_ADDR'];
        if($ip == '::1'){
            $ip = '127.0.0.1';
        }
        $result = $this->admin->table('user')->field('name')->where("name = '$name' and udertype = 1")->select();
        if (!$result) {
            $this->notice('抱歉,你不是管理员！');
            die;
        }
        //查询用户密码是否正确
        $result1 = $this->admin->table('user')->field(['id','name','password'])->where("name='$name'","password='$pwd'")->select();
        if ($result1){
            //保存一份用户名信息
            $_SESSION['username'] = $name;
            $this->notice('登陆成功,三秒后自动跳转后台首页','index.php?c=admin&a=adminindex',3);
        }else{
            $msg = '密码不正确,请重新输入';
            $this->notice($msg);
            exit;
        }
    }

    //这是登录后台首页的方法
    public function adminindex()
    {
        //获取页面加载的时间
        date_default_timezone_set('PRC');
        $time = time();
        $newtime = date('Y-m-d H:i:m',$time);
        $this->assign('newtime',$newtime);
        //查询表格中的数据(文章的数句，评论的数据)
        $user = $this->admin->table('user')->field('count(id)')->select();
        $article = $this->admin->table('article')->field('count(id)')->select();
        $replycount = $this->admin->table('article')->field('count(id)')->where('title=""')->select();
        $linkcount = $this->admin->table('link')->field('count(lid)')->select();
        $this->assign('user',$user);
        $this->assign('article',$article);
        $this->assign('replycount',$replycount);
        $this->assign('linkcount',$linkcount);

        //查询管理员的信息
        $adminuser = $this->admin->table('user')->field('count(id)')->where('udertype=1')->select();
        $this->assign('adminuser',$adminuser);
        //查询系统相关信息
        /*操作系统
         * */
        $computer=PHP_OS;
        $this->assign('computer',$computer);
        //查询服务器的相关信息
        //服务器软件
        $php = $_SERVER['SERVER_SOFTWARE'];
        $this->assign('php',$php);
        //php版本
        $phpnum = PHP_VERSION;
        $this->assign('phpnum',$phpnum);
        //PHP运行方式
        $phprun = PHP_SAPI;
        $this->assign('phprun',$phprun);
        $this->display('admin/index.html');
    }

    //这是加载后台文章管理的方法
    public function adminmessage()
    {
        //查询文章的条数
        $numarticle = $this->admin->table('article')->field('count(id)')->select();
        //分页  ()
        //得到总的文章数
        $totalcount = $numarticle[0]['count(id)'];
        //实例化对象Page  ,每页显示数
        $page = new Page(7,$totalcount);
        //获取（首页、上一页、下一页、尾页的url）
        $count = $page->allPage();
        $this->assign('count',$count);
        //获取limit条件,偏移量
        $limit = $page->limit();
        //链表查询（要知道文章属于那种类型，作者）
        $article = $this->admin->table('article,user,category')->field('article.*,user.name,category.*')->limit($limit)->where("user.id=article.uid and article.pid=category.cid")->order('id desc')->select();
        $numarticle = $this->admin->table('article')->field('count(id)')->select();
        //分配数据
        $this->assign('article',$article);
        $this->assign('numarticle',$numarticle);
        $this->display('admin/article.html');
    }

    /* 文章的删除 */
    public function messagedelete()
    {
        //接收文章的id，判断对某个文章的操作
        $messageid = $_POST['checkbox'];
        //通过函数join，用，隔开
        $wid = join(',',$messageid);
        //进行sql删除
        $result = $this->admin->table('article')->where("id in($wid)")->delete();
        //var_dump($result);
        if ($result){
            $msg = '删除成功, 正在跳转详情页';
            $this->notice($msg,"index.php?c=admin&a=adminmessage",3);
            die;
        }else{
            $msg = '删除失败！';
            $this->notice($msg,"index.php?c=admin&a=adminmessage",3);
            die;
        }
    }

    //------------------------------------------------------//

    //这是加载评论页面的方法
    public function admintalk()
    {
        //查询文章评论的条数
        $numarticle = $this->admin->table('article')->field('count(id)')->where('title=""')->select();
        $this->assign('numarticle',$numarticle);
        //分页  ()
        //得到总的文章数
        $totalcount = $numarticle[0]['count(id)'];
        //实例化对象Page  ,每页显示数
        $page = new Page(5,$totalcount);
        //获取（首页、上一页、下一页、尾页的url）
        $count = $page->allPage();
        $this->assign('count',$count);
        //获取limit条件,偏移量
        $limit = $page->limit();
        //链表查询（要知道文章属于那种类型，作者）
        $article = $this->admin->table('article,user')->field('article.*,user.name')->limit($limit)->where("article.title='' and user.id=article.uid")->order('id desc')->select();
        //var_dump($article);
        //分配数据
        $this->assign('article',$article);
        $this->display('admin/comment.html');
    }

    //文章评论的删除
    public function talkdelete()
    {
        //接收评论的id，判断对某个文章的操作
        $talkid = $_POST['checkbox'];
        //通过函数join，用，隔开
        $tid = join(',',$talkid);
        //进行sql删除
        $result = $this->admin->table('article')->where("id in($tid)")->delete();
        //var_dump($result);
        if ($result){
            $msg = '删除成功, 正在跳转详情页';
            $this->notice($msg,"index.php?c=admin&a=admintalk",3);
            die;
        }else{
            $msg = '删除失败！';
            $this->notice($msg,"index.php?c=admin&a=admintalk",3);
            die;
        }
    }

    //----------------------------------------------------------------//
    //这是加载留言页面的方法
    public function adminwords()
    {
        
        //查询留言的条数
        $numwords = $this->admin->table('words')->field('count(id)')->select();
        $this->assign('numwords',$numwords);
        //分页  ()
        //得到总的文章数
        $totalcount = $numwords[0]['count(id)'];
        //实例化对象Page  ,每页显示数
        $page = new Page(5,$totalcount);
        //获取（首页、上一页、下一页、尾页的url）
        $count = $page->allPage();
        $this->assign('count',$count);
        //获取limit条件,偏移量
        $limit = $page->limit();
        //查询留言表
        $words = $this->admin->table('words')->field('*')->limit($limit)->order('id desc')->select();
        //var_dump($words);
        //分配数据
        $this->assign('words',$words);
        $this->display('admin/words.html');
    }

    //留言的删除
    public function wordsdelete()
    {
        //接收留言的id，判断对某个文章的操作
        $wordid = $_POST['checkbox'];
        //通过函数join，用，隔开
        $wid = join(',',$wordid);
        //进行sql删除
        $result = $this->admin->table('words')->where("id in($wid)")->delete();
        //var_dump($result);
        if ($result){
            $msg = '删除成功, 正在跳转详情页';
            $this->notice($msg,"index.php?c=admin&a=adminwords",3);
            die;
        }else{
            $msg = '删除失败！';
            $this->notice($msg,"index.php?c=admin&a=adminwords",3);
            die;
        }
    }

    //------------------------------------------------------------------//
    //这是加载博客栏目页面的方法
    public function admincategory()
    {
        //查询栏目表
        $num = $this->admin->table('category')->field('count(cid)')->select();
        $this->assign('num',$num);
        $category = $this->admin->table('category')->field('*')->select();
        $this->assign('category',$category);
        $this->display('admin/category.html');
    }

    //后台添加博客栏目的方法
    public function addcategory()
    {
        //接收博客栏目的名称
        $catename = $_POST['catename'];
        if (empty($catename)){
            $msg = '栏目名称不能为空！';
            $this->notice($msg,"index.php?c=admin&a=admincategory",3);
            die;
        }
        $data['catename'] = $catename;
        //执行插入的语句
        $result = $this->admin->table('category')->insert($data);
        if ($result){
            $msg = '添加成功';
            $this->notice($msg,"index.php?c=admin&a=admincategory",3);
            die;
        }else{
            $msg = '添加失败！';
            $this->notice($msg,"index.php?c=admin&a=admincategory",3);
            die;
        }
    }

    //后台删除博客栏目的方法
    public function catedelete()
    {
        //接收博客栏目的id
        $id = $_POST['checkbox'];
        //通过join函数分隔开
        $cid = join(',',$id);
        //进行sql删除
        $result = $this->admin->table('category')->where("cid in($cid)")->delete();
        //var_dump($result);
        if ($result){
            $msg = '删除成功, 正在跳转详情页';
            $this->notice($msg,"index.php?c=admin&a=admincategory",3);
            die;
        }else{
            $msg = '删除失败！';
            $this->notice($msg,"index.php?c=admin&a=admincategory",3);
            die;
        }
    }

    //后台修改博客栏目的方法
    public function update()
    {
        $id = $_GET['cid'];
        $this->assign('id',$id);
        $cate = $this->admin->table('category')->field('cid,catename')->where("cid=$id")->select();
        $this->assign('cate',$cate);
        $this->display('admin/category-update.html');
    }

    //这是接收修改数据的方法
    public function updateInfo()
    {
        //获取隐藏的id
        $id = $_GET['cid'];
        //接收修改过来的数据
        $newname = $_POST['catename'];
        $data['catename'] = $newname;
        //执行修改的语句
        $result = $this->admin->table('category')->where("cid = $id")->update($data);
        if ($result){
            $msg = '恭喜小主修改成功, 正在跳转详情页';
            $this->notice($msg,"index.php?c=admin&a=admincategory",3);
            die;
        }else{
            $msg = '修改失败！';
            $this->notice($msg,"index.php?c=admin&a=admincategory",3);
            die;
        }
    }

    //------------------------------------------------------------//
    //这是加载博客友情链接页面的方法
    public function adminlink()
    {
        //查询链接的个数
        $numlink = $this->admin->table('link')->field('count(lid)')->select();
        $this->assign('numlink',$numlink);
        //查询链接表的链接数据
        $link = $this->admin->table('link')->field('*')->select();
        $this->assign('link',$link);
        $this->display('admin/link.html');
    }

    //这是显示添加友情链接的方法
    public function addlink()
    {
        $this->display('admin/add-link.html');
    }

    //这是接收友情链接判断的方法
    public function addlinkInfo()
    {
        //接收url信息
        $urlName = $_POST['urlname'];
        $urlWeb = $_POST['urlweb'];
        $data['urlname'] = $urlName;
        $data['url'] = $urlWeb;
        //执行sql语句
        $result = $this->admin->table('link')->insert($data);
        //var_dump($result);
        if ($result){
            $msg = '恭喜小主添加成功';
            $this->notice($msg,'index.php?c=admin&a=adminlink',3);
            die;
        }else{
            $msg = '啊！抱歉添加失败';
            $this->notice($msg,'index.php?c=admin&a=adminlink',3);
            die;
        }
    }

    //这是删除友情链接的方法
    public function linkDelete()
    {
        //接收链接的id
        $id = $_POST['checkbox'];
        //通过join函数分隔开
        $lid = join(',',$id);
        //进行sql删除
        $result = $this->admin->table('link')->where("lid in($lid)")->delete();
        //var_dump($result);
        if ($result){
            $msg = '删除成功, 正在跳转详情页';
            $this->notice($msg,"index.php?c=admin&a=adminlink",3);
            die;
        }else{
            $msg = '删除失败！';
            $this->notice($msg,"index.php?c=admin&a=adminlink",3);
            die;
        }
    }

    //这是显示修改友情链接页面的方法
    public function linkUpdate()
    {
        $id = $_GET['lid'];
        $this->assign('id',$id);
        $link = $this->admin->table('link')->field('*')->where("lid=$id")->select();
        $this->assign('link',$link);
        $this->display('admin/link-update.html');
    }

    //这是接收修改数据的方法
    public function linkUpdateInfo()
    {
        //获取隐藏的id
        $id = $_GET['lid'];
        //接收修改过来的数据
        $newname = $_POST['urlname'];
        $newurl = $_POST['urlweb'];
        $data['urlname'] = $newname;
        $data['url'] = $newurl;
        //执行修改的语句
        $result = $this->admin->table('link')->where("lid = $id")->update($data);
        if ($result){
            $msg = '恭喜小主修改成功, 正在跳转详情页';
            $this->notice($msg,"index.php?c=admin&a=adminlink",3);
            die;
        }else{
            $msg = '修改失败！';
            $this->notice($msg,"index.php?c=admin&a=adminlink",3);
            die;
        }
    }

    //--------------------------------------------------------//
    //这是加载用户信息页面的方法
    public function adminUser()
    {
        //查询用户数量
        $numuser = $this->admin->table('user')->field('count(id)')->select();
        $this->assign('numuser',$numuser);

        //得到总的文章数
        $totalcount = $numuser[0]['count(id)'];
        //实例化对象Page  ,每页显示数
        $page = new Page(5,$totalcount);
        //获取（首页、上一页、下一页、尾页的url）
        $count = $page->allPage();
        $this->assign('count',$count);
        //获取limit条件,偏移量
        $limit = $page->limit();
        //查询用户表里的数据
        $userInfo = $this->admin->table('user')->field('*')->limit($limit)->select();
        //var_dump($userInfo);
        $this->assign('userInfo',$userInfo);
        $this->display('admin/adminUser.html');
    }

    //这是删除用户信息的方法
    public function userDelete()
    {
        //接收链接的id
        $id = $_POST['checkbox'];
        //通过join函数分隔开
        $uid = join(',',$id);
        //进行sql删除
        $result = $this->admin->table('user')->where("id in($uid)")->delete();
        //var_dump($result);
        if ($result){
            $msg = '删除成功, 正在跳转详情页';
            $this->notice($msg,"index.php?c=admin&a=adminUser",3);
            die;
        }else{
            $msg = '删除失败！';
            $this->notice($msg,"index.php?c=admin&a=adminUser",3);
            die;
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
