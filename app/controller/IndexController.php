<?php
namespace controller;
use model\IndexModel;
use framework\Page;
use framework\Upload;
class IndexController extends Controller 
{
    //定义一个成员属性，保存IndexModel实例化后的对象
    public $index;
    public function __construct()
    {
        parent::__construct();
        $this->index = new IndexModel();
    }

	public function login()
	{
		echo '我是登陆的方法<br />';
	}

	public function index()
    {
        //查询版块的名称，博文的类型和id
        $category = $this->index->table('category')->field(['cid','catename'])->select();
        $this->assign('category',$category);

        //查询最新贴，根据id自增进行查找
        $newArticle = $this->index->table('article')->field('*')->order('id desc')->where("title!=''")->limit('0,6')->select();
        $this->assign('newArticle',$newArticle);

        //查询点击量最多的文章，根据字段lookcount
        $manyArticle = $this->index->table('article')->field('*')->order('lookcount desc')->where("title!=''")->limit('0,6')->select();
        $this->assign('manyArticle',$manyArticle);

        //分页
        $manyArticle1 = $this->index->table('article,category')->field('count('."article.id".') as count')->where("article.pid=category.cid and article.title!=''")->order('lookcount desc')->select();
        $totalCount =$manyArticle1[0]['count'];
        //实例化分页对象，等会调用其里面的两个方法获取url和limit
        $page = new Page(4,$totalCount);

        //获取（首页、上一页、下一页、尾页的url地址）
        $totalPage = $page->allPage();
        $this->assign('totalPage',$totalPage);
        //获取limit条件
        $limit = $page->limit();
        //查询主页面文章（点击量） 根据字段lookcount----查了两张表，因为要知道属于哪个版块下的文章
        //$manyArticle2 = $this->index->table('article,category')->field('article.*,category.*')->where("article.pid=category.cid and article.title!=''")->order('lookcount desc')->limit($limit)->select();
        $manyArticle2=$this->index->table('article,user,category')->field('article.*,user.name,category.*')->where("user.id=article.uid and article.pid=category.cid and article.title!='' and article.isdel=0")->order('sendtime desc')->limit($limit)->select();
        $this->assign('manyArticle2',$manyArticle2);
        //查询推荐文章（评论最多），(放在图文推荐里面)
        $replyArticle = $this->index->table('article,category')->field('article.*,category.*')->where("article.pid=category.cid and article.title!=''")->limit('0,6')->order('replycount desc')->select();
        $this->assign('replyArticle',$replyArticle);
        //查询友情链接
        $link = $this->index->table('link')->field('*')->select();
        $this->assign('link',$link);
        //查询头像
        @$user = $this->index->table('user')->field('*')->where("id = $_SESSION[id]")->select();
        $this->assign('user',$user);
        $this->display();
    }

    //这是显示上传头像页面的方法
    public function Upload()
    {
        //判断用户是否登录,给与相对的权限
        if (empty($_SESSION['id'])) {
            $msg = '抱歉你还未登录, 请登录后再修改头像';
            $this->notice($msg);
            die;
        }
        //把数据库中的默认头像查出来
        $pic = $this->index->table('user')->field('*')->where("id = $_SESSION[id]")->select();
        //var_dump($pic);
        $this->assign('pic',$pic);
        $this->display('index/home-tx.html');
    }


    //这是上传头像的方法
    public function Upsrc()
    {
        //获取input框name属性值  file
        $arr = array_keys($_FILES);
        $str = join('',$arr);
        //实例化对象，调用uploadFile
        $pic = new Upload();
        //这是获取放图片的文件路径
        $name = $pic->uploadFile($str);
        $name2 = $pic->newName;
        $truePath = "$name$name2";
        //保存在数据库中
        $data['pic'] = $truePath;
        //判断用户是否登录,给与相对的权限
        if (empty($_SESSION['id'])) {
            $msg = '抱歉你还未登录, 请登录后再评论';
            $this->notice($msg);
            die;
        }
        //执行修改的sql语句
        $result = $this->index->table('user')->where("id = $_SESSION[id]")->update($data);
        //var_dump($result);
        if ($result){
            $msg = '恭喜小主，修改头像成功';
            $this->notice($msg,'index.php');
            die;
        }else{
            $msg = '啊，抱歉，修改头像失败';
            $this->notice($msg);
            die;
        }
    }

    //这是显示个人资料的方法
    public function about()
    {
        //查询版块的名称，博文的类型和id
        $category = $this->index->table('category')->field(['cid','catename'])->select();
        $this->assign('category',$category);
        //把数据库中的默认头像查出来
        //查询头像
        @$user = $this->index->table('user')->field('*')->where("id = $_SESSION[id]")->select();
        $this->assign('user',$user);
        $this->display('index/about.html');
    }
}








