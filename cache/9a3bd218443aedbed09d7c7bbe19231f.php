<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>首页-宣泄笔记个人博客</title>
<meta name="keywords" content="" />
<meta name="description" content="" />
<link href="public/css/base.css" rel="stylesheet">
<link href="public/css/index.css" rel="stylesheet">
<link href="public/css/media.css" rel="stylesheet">
<meta name="viewport" content="width=device-width, minimum-scale=1.0, maximum-scale=1.0">
<!--[if lt IE 9]>
<script src="public/js/modernizr.js"></script>
<![endif]-->

</head>
<body>
<div class="ibody">
  <header>
    <h1>如影随形</h1>
    <h2>影子是一个会撒谎的精灵，它在虚空中流浪和等待被发现之间;在存在与不存在之间....</h2>
    <div class="logo"><a href="/"></a></div>
    <nav id="topnav">
      <a href="index.php">首页</a>
      <a href="index.php?c=index&a=about">关于我</a>
      <a href="index.php?c=article&a=blog">博文</a>
      <a href="index.php?c=person&a=words">留言</a>
      <?php if ((empty($_SESSION['id']))):?>
      <a href="index.php?c=user&a=login">登录</a>
      <a href="index.php?c=user&a=register">注册</a>
      <?php else:?>
      <span style="color: green;">欢迎回来：</span><span style="color: red;padding-right: 10px;"><?php echo $_SESSION['name'];?></span>
      <a href="index.php?c=user&a=doOut" style="font-size: 16px;padding-right: 0;padding-left: 0;color: #fff;">退出</a>
      <a href="index.php?c=user&a=register">注册</a>
      <?php if (($_SESSION['udertype'])):?>
      <a href="index.php?c=admin&a=adminlogin" target="_blank">管理中心</a>
      <?php endif;?>
      <?php endif;?>
    </nav>
  </header>
  <article>
    <div class="banner">
      <ul class="texts">
        <p>The best life is use of willing attitude, a happy-go-lucky life. </p>
        <p>最好的生活是用心甘情愿的态度，过随遇而安的生活。</p>
      </ul>
    </div>
    <div class="bloglist">
      <h2>
        <p><span>推荐</span>文章</p>
        <?php if (!empty($category)):?>
        <a href="index.php?c=article&a=blog" style="color: #0AABE1;">全部博客</a>
        <?php foreach ($category as $value):?>
        <a href="index.php?c=article&a=Blogpost&id=<?php echo $value['cid'];?>" style="color: palevioletred;"><?php echo $value['catename'];?></a>
        <?php endforeach;?>
      <?php endif;?>
      </h2>
      <?php if (!empty($manyArticle2)):?>
      <?php foreach ($manyArticle2 as $value):?>
      <div class="blogs">
        <h3><a href="index.php?c=article&a=details&id=<?php echo $value['id'];?>"><?php echo $value['title'];?></a></h3>

        <figure><img src="<?php echo $value['pic'];?>" ></figure>
        <?php if ($value['elite']==1):?> <img src="public/images/jh.gif"><?php endif;?>
        <ul>
          <p><?php echo $value['content'];?></p>
          <a href="index.php?c=article&a=details&id=<?php echo $value['id'];?>" class="readmore">阅读全文&gt;&gt;</a>
        </ul>
        <p class="autor"><span>作者：<?php echo $value['name'];?></span><span>分类：【<a href="/"><?php echo $value['catename'];?></a>】</span><span>浏览（<a href="/"><?php echo $value['lookcount'];?></a>）</span><span>评论（<a href="/"><?php echo $value['replycount'];?></a>）</span></p>
        <div class="dateview"><?php echo $value['sendtime'];?></div>
      </div>
      <?php endforeach;?>
      <?php endif;?>
      &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
      <span style="color: #639BB0;border: 1px solid #333;margin: 8px;font-size: 18px"><a href="<?php echo $totalPage['first'];?>">首页</a></span>
      <span style="color: #639BB0;border: 1px solid #333;margin: 8px;font-size: 18px"><a href="<?php echo $totalPage['prev'];?>" hidefocus="">上一页</a></span>
      <span style="color: #639BB0;border: 1px solid #333;margin: 8px;font-size: 18px"><a href="<?php echo $totalPage['next'];?>" hidefocus="">下一页</a></span>
      <span style="color: #639BB0;border: 1px solid #333;margin: 8px;font-size: 18px"><a href="<?php echo $totalPage['end'];?>" class="next">尾页</a></span>
    </div>

  </article>
  <aside>
    <div class="avatar">
      <?php if (!empty($_SESSION['name'])):?>
      <a href="index.php?c=index&a=Upload"><span>修改头像</span><img src="<?php echo $user[0]['pic'];?>" style=""></a>
      <?php else:?>
      <a href="index.php?c=index&a=Upload"><span>修改头像</span><img src="public/images/t1.jpg" style=""></a>
      <?php endif;?>
    </div>
    <div class="topspaceinfo">
      <?php if (!empty($category)):?>
        <?php foreach ($category as $value):?>
      <a href="index.php?c=article&a=send&id=<?php echo $value['cid'];?>"><?php echo $value['catename'];?>|</a>
        <?php endforeach;?>
      <?php endif;?>
    </div>
    <div class="about_c">
      <p>网名：DanceSmile | 宣泄笔记</p>
      <p>职业：PHP架构工程师 </p>
      <p>籍贯：江西省―赣州市</p>
      <p>电话：15779595135</p>
      <p>邮箱：469058926@qq.com</p>
    </div>
    <div class="bdsharebuttonbox"><a href="#" class="bds_qzone" data-cmd="qzone" title="分享到QQ空间"></a><a href="#" class="bds_tsina" data-cmd="tsina" title="分享到新浪微博"></a><a href="#" class="bds_tqq" data-cmd="tqq" title="分享到腾讯微博"></a><a href="#" class="bds_renren" data-cmd="renren" title="分享到人人网"></a><a href="#" class="bds_weixin" data-cmd="weixin" title="分享到微信"></a><a href="#" class="bds_more" data-cmd="more"></a></div>
    <div class="tj_news">
      <h2>
        <p class="tj_t1">最新文章</p>
      </h2>
      <ul>
        <?php if (!empty($newArticle)):?>
        <?php foreach ($newArticle as $value):?>
        <li><a href="index.php?c=article&a=details&id=<?php echo $value['id'];?>" target="_blank"><?php echo $value['title'];?></a></li>
        <?php endforeach;?>
        <?php endif;?>
      </ul>
      <h2>
        <p class="tj_t2">推荐文章</p>
      </h2>
      <ul>
        <?php if (!empty($replyArticle)):?>
        <?php foreach ($replyArticle as $value):?>
        <li><a href="index.php?c=article&a=details&id=<?php echo $value['id'];?>" target="_blank"><?php echo $value['title'];?></a></li>
        <?php endforeach;?>
        <?php endif;?>
      </ul>
    </div>
    <div class="links">
      <h2>
        <p>友情链接</p>
      </h2>
      <ul>
        <?php if (!empty($link)):?>
        <?php foreach ($link as $value):?>
        <li><a href="<?php echo $value['url'];?>"><?php echo $value['urlname'];?></a></li>
        <?php endforeach;?>
        <?php endif;?>
      </ul>
    </div>
    <div class="copyright">
      <ul>
        <p> Design by <a href="/">DanceSmile</a></p>
        <p>赣ICP备11002373号-1</p>
      </ul>
    </div>
  </aside>
  <script src="public/js/silder.js"></script>
  <div class="clear"></div>
  <!-- 清除浮动 --> 
</div>
</body>
</html>
