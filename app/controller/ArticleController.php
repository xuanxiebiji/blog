<?php
/**
 * Created by PhpStorm.
 * User: 图图
 * Date: 2018/2/12
 * Time: 下午3:47
 */
namespace controller;
use model\ArticleModel;
use framework\Page;
use framework\Upload;
class ArticleController extends Controller
{
    //定义一份成员属性，保存ArticleModel实例化后的对象
    public $article;
    public function __construct()
    {
        parent::__construct();
        $this->article = new ArticleModel();
    }

    //这是显示文章类型的方法
    public function send()
    {
        //接收博文版块的id,区分发表的博文类型，发博文
        $id = $_GET['id'];
        //调用分配数据的方法
        $this->assign('id',$id);
        //查询数据库中博文板块的类型
        $category = $this->article->table('board')->field('*')->where("cid=$id")->select();
        $this->assign('category',$category);
        $this->display();
    }

    //这里是发表博文的方法
    public function add()
    {
        //判断用户是否登录,没有登录时不允许发表
        if(empty($_SESSION['id'])){
            $msg = '你还没有登录，请登陆后再发表';
            $this->notice($msg);
            die;
        }
        //接收版块的id用于写在文章的pid字段中
        $id = $_GET['id'];
        //标题
        $title = $_POST['title'];
        //内容
        $content = $_POST['content'];
        //对标题和内容进行判断
        if(empty($title)){
            $msg = '标题不能为空,请重新输入';
            $this->notice($msg);
            die;
        }
        if(empty($content)){
            $msg = '发表的内容不能为空, 请重新发表';
            $this->notice($msg);
            die;
        }
		//获取input框name属性值  file
        $arr = array_keys($_FILES);
        $str = join('',$arr);
        //实例化对象，调用uploadFile
        $pic = new Upload();
        //这是获取放图片的文件路径
        $name = $pic->uploadFile($str);
        $name2 = $pic->newName;
        $truePath = "$name$name2";
        //将发表的文章写进数据库中
        $data['pid'] = $id;
        $data['title'] = $title;
        $data['content'] = $content;
        $data['uid'] = $_SESSION['id'];
		$data['pic'] = $truePath;
        $result = $this->article->table('article')->insert($data);
        if($result){
            $msg = '发表成功, 正在跳转详情页';
            $this->notice($msg,"index.php?c=article&a=details&id='$result'",3);
            die;
        }
    }

    //这是博文发表后，其他用户评论的方法
    public function talk()
    {
        //判断用户是否登录,给与相对的权限
        if (empty($_SESSION['id'])) {
            $msg = '抱歉你还未登录, 请登录后再评论';
            $this->notice($msg);
            die;
        }
        //评论的pid文章的id
        $id = $_GET['id'];
        //接收用户评论的内容
        $content = $_POST['content'];
        //判断评论内容不能为空
        if (empty($content)) {
            $msg = '评论的内容不能为空, 请重新评论';
            $this->notice($msg);
            die;
        }
        //在article表格中，通过pid来保存评论的内容
        $data = ['pid'=>"$id",'title'=>'','content'=>"$content",'uid'=>"$_SESSION[id]"];
        $result = $this->article->table('article')->insert($data);
        if($result){
            //查询原来评论数
            $detail = $this->article->table('article')->field('replycount')->where("id=$id")->select();
            //更新评论数,先查出原数量，然后评论额时候+1
            //原先文章浏览数量
            $agosum = $detail[0]['replycount'];
            //点击+1
            $nowsum = $agosum + 1;
            $data = ['replycount'=>"$nowsum"];
            $this->article->table('article')->where("id=$id")->update($data);
            $msg = '评论成功, 正在跳转详情页';
            $this->notice($msg,"index.php?c=article&a=details&id=$id");
            die;
        }
    }

    //这是文章详情页的方法
    public function details()
    {
        //接收文章的id，确认文章的类型
        $id = $_GET['id'];
        //文章获取文章的信息
        $detail = $this->article->table('article')->field('*')->where("id=$id")->select();
        $this->assign('detail',$detail);
        $discuss = $this->article->table('article,user')->field('article.*,user.*')->where("article.pid=$id and user.id=article.uid")->select();
        //更新浏览数,先查出原数量，然后点击额时候+1
        //原先文章浏览数量
        $agosum = $detail[0]['lookcount'];
        //点击+1
        $nowsum = $agosum + 1;
        $data = ['lookcount'=>"$nowsum"];
        $this->article->table('article')->where("id=$id")->update($data);
        $this->assign('discuss',$discuss);
        //查询点击量最多的文章，根据字段lookcount
        $manyArticle = $this->article->table('article')->field('*')->order('lookcount desc')->where("title!=''")->limit('0,6')->select();
        $this->assign('manyArticle',$manyArticle);
        //查询推荐的文章，根据字段replycount 回复的数量
        $replyArticle = $this->article->table('article,category')->field('article.*,category.*')->where("article.pid=category.cid and article.title!=''")->limit('0,6')->order('replycount desc')->select();
        $this->assign('replyArticle',$replyArticle);
        $this->display('article/details.html');
    }

    //显示全部博文列表的方法
    public function blog()
    {
        //查询版块的名称和id
        $navs = $this->article->table('category')->field('*')->select();
        $this->assign('navs',$navs);
        //查询全部博客，并且查出属于哪个版块
        //查询总共数据库总共的文章
        $blog2 = $this->article->table('article,category')->field('count('."article.id".') as count')->order('id desc')->where("article.pid=category.cid")->select();
        //总文章数
        $totalCount = $blog2[0]['count'];
        //总页码数
        $page = new Page(4,$totalCount);
        //获取url(首页、上一页、下一页、尾页)的地址
        $totalPage = $page->allPage();
        //获取查询limit的条件
        $limit = $page->limit();
        $this->assign('totalPage',$totalPage);
        $blog = $this->article->table('article,user,category')->field('article.*,user.name,category.*')->order('id desc')->where("user.id=article.uid and article.pid=category.cid and article.title!=''")->limit($limit)->select();
        $this->assign('blog',$blog);
        //查询点击量最多的文章，根据字段lookcount
        $manyArticle = $this->article->table('article')->field('*')->order('lookcount desc')->where("title!=''")->limit('0,6')->select();
        $this->assign('manyArticle',$manyArticle);
        //查询推荐的文章，根据字段replycount 回复的数量
        $replyArticle = $this->article->table('article,category')->field('article.*,category.*')->where("article.pid=category.cid and article.title!=''")->limit('0,6')->order('replycount desc')->select();
        $this->assign('replyArticle',$replyArticle);
        $this->display('article/blog.html');
    }

    //这是显示不同类型博文的方法
    public function Blogpost()
    {
        //接收大板块的id
        $id = $_GET['id'];
        $this->assign('id',$id);
        //查询版块的名称和id
        $navs2 = $this->article->table('category')->field(['cid','catename'])->select();
        $this->assign('navs2',$navs2);
        //查询全部博客，并且查出属于哪个版块
        $blog3 = $this->article->table('article,category')->field('count('."article.id".') as count')->where("article.pid=category.cid and article.pid=$id and article.title!=''")->select();
        //查询总文章数
        $totalCount = $blog3[0]['count'];
        //实例化一个对象,每页显示4条
        $page = new Page(4,$totalCount);
        //获取url地址（一维数组）
        $totalPage = $page->allPage();
        //获取limit查询条件
        $limit = $page->limit();
        $blog2 = $this->article->table('article,user,category')->field('article.*,user.name,category.*')->order('id desc')->where("user.id=article.uid and article.pid=category.cid and article.pid=$id and article.title!=''")->limit($limit)->select();
        $this->assign('blog2',$blog2);
        $this->assign('totalPage',$totalPage);
        //查询点击量最多的文章，根据字段lookcount
        $manyArticle1 = $this->article->table('article')->field('*')->order('lookcount desc')->where("title!=''")->limit('0,6')->select();
        $this->assign('manyArticle1',$manyArticle1);
        //查询推荐的文章，根据字段replycount 回复的数量
        $replyArticle1 = $this->article->table('article,category')->field('article.*,category.*')->where("article.pid=category.cid and article.title!=''")->limit('0,6')->order('replycount desc')->select();
        $this->assign('replyArticle1',$replyArticle1);
        $this->display('article/blog.html');
    }

    //这是修改为精华帖的方法
    public function cream()
    {
        //获取文章的id
        $id = $_GET['id'];
        //查询文章表，获取elite字段
        $data['elite']=1;
        $result = $this->article->table('article')->where("id = $id")->update($data);
        if ($result){
            $msg = '恭喜小主，精华添加成功';
            $this->notice($msg);
            die;
        }else{
            $msg = '抱歉，精华添加失败';
            $this->notice($msg);
            die;
        }
    }

    //这里是文章高亮的方法
    public function style()
    {
        //获取文章的id
        $id = $_GET['id'];
        //查询文章表，获取elite字段
        $data['style']='red';
        $result2 = $this->article->table('article')->where("id = $id")->update($data);
        if ($result2){
            $msg = '恭喜小主，高亮成功';
            $this->notice($msg);
            die;
        }else{
            $msg = '抱歉，高亮失败';
            $this->notice($msg);
            die;
        }
    }

    //这是放入回收站的方法
    public function recycle()
    {
        //获取文章的id
        $id = $_GET['id'];
        //查询文章表，获取elite字段
        $data['isdel']='1';
        $result3 = $this->article->table('article')->where("id = $id")->update($data);
        if ($result3){
            $msg = '恭喜小主，放入回收站成功';
            $this->notice($msg);
            die;
        }else{
            $msg = '抱歉，放入回收站失败';
            $this->notice($msg);
            die;
        }
    }
}










