<?php
namespace framework;
class Tpl
{
    //模板文件存放的路径
    protected $viewPath;
    //缓存文件存放的路径
    protected $cachePath;
    //用来存放变量的数组
    protected $vars = [];
    //初始化成员属性
    public function __construct($viewPath = './view/',$cachePath = './cache/')
    {
        //检测模板文件是否存在或者是否具备相应的权限
        if (!empty($viewPath)) {
            if ($this->checkDir($viewPath)) {
                $this->viewPath = $viewPath;
            }
        }
        if (!empty($cachePath)) {
            if ($this->checkDir($cachePath)) {
                $this->cachePath = $cachePath;
            }
        }

    }
    //检查文件是否存在或者是否具备相应的权限
    protected function checkDir($dirPath)
    {
        if (!file_exists($dirPath)) {
            return mkdir($dirPath,0755,true);
        }
        if ((!is_readable($dirPath))||(!is_writeable($dirPath))) {
            return chmod($dirPath,0755);
        }
        return true;
    }
    //分配数据的方法 assign
    /**
     *   $name = '世阳';
     *  $this->assign('name',$name);
     */
    public function assign($name,$value)
    {
        //以键值对的形式将数据保存到$vars数组中去
        $this->vars[$name] = $value;
    }

    //封装渲染模板页面的方法   display
    public function display($viewName,$isInclude = true)
    {
        //判断模板文件是否为空
        if (empty($viewName)) {
            die('模板文件不存在');
        }
        //拼接新生成的缓存文件的名字
        $cacheName = md5($viewName).'.php';
        //拼接缓存文件完整的路径
        $cachePath = rtrim($this->cachePath,'/').'/'.$cacheName;
        //拼接模板文件完整的路径
        $viewPath = rtrim($this->viewPath,'/').'/'.$viewName;
        //判断缓存文件是否存在，若不存在，直接编译，并保存到指定的缓存路径中
        if (!file_exists($cachePath)) {
            //编译生成缓存文件
            $php = $this->compile($viewPath);
            file_put_contents($cachePath,$php);
        }
        //将生成的缓存文件包含进来
        if ($isInclude) {
            extract($this->vars);
            include $cachePath;
        }
    }
    //将模板文件进行编译的方法
    protected function compile($filePath)
    {
        //将模板文件中的内容全部读取出来
        $html = file_get_contents($filePath);
        $array = [
            //%%仅仅是一个占位符，用来表示变量的名字,$%%这种格式和html文件中的变量的格式非常像，因此采用这种形式，
            //  $\1这个里面的\1就是变量名，表示正则表达式里面的第一个子模式，也就是第一个()里面匹配到的内容。
            '{$%%}' =>'<?php echo $\1;?>',
            '{if %%}'=>'<?php if (\1):?>',
            '{/if}'=>'<?php endif;?>',
            '{else}'=>'<?php else:?>',
            '{foreach %%}'=>'<?php foreach (\1):?>',
            '{/foreach}'=>'<?php endforeach;?>',
            '{while %%}'		=> '<?php while(\1):?>',
            '{/while}'			=> '<?php endwhile;?>',
            '{for %%}'			=> '<?php for(\1):?>',
            '{/for}'			=> '<?php endfor;?>',
            '{continue}'		=> '<?php continue;?>',
            '{switch %%}'  => '<?php switch (\1): ?>',
            '{case %%}'    => '<?php case \1: ?>',
            '{break}'      => '<?php break; ?>',
            '{/switch}'    => '<?php endswitch; ?>',
            '{$%%++}'			=> '<?php $\1++;?>',
            '{$%%--}'			=> '<?php $\1--;?>',
            '{/*}'				=> '<?php /*',
            '{*/}'				=> '*/?>',
            '{section}'			=> '<?php ',
            '{/section}'		=> '?>',
            '{$%% = $%%}'		=> '<?php $\1 = $\2;?>',
            '{default}'			=> '<?php default:?>',
            '{include %%}'		=> '这的代码就是来捣乱的',
        ];
        foreach ($array as $key=>$value) {
            //将$key替换为标准的正则表达式
            $pattern = "#".str_replace('%%','(.*?)',preg_quote($key,'#'))."#";
            if (strstr($key,'include')) {
                $html = preg_replace_callback($pattern,[$this,'parseInclude'],$html);
            } else {
                //实现替换,将$value的值依次对$key进行替换
                $html = preg_replace($pattern,$value,$html);
            }
        }
        return $html;
    }
    //处理include这种情况时候的函数
    protected function parseInclude($data)
    {
        //var_dump($data);
        //将文件名两边的引号干掉
        $fileName = trim($data[1],'\'"');
        //调用display方法，将包含的模板文件编译一下
        $this->display($fileName,false);
        //生成缓存文件的名字
        $cacheName = md5($fileName).'.php';
        //拼接完整的缓存路径
        $cachePath = rtrim($this->cachePath,'/').'/'.$cacheName;
        return "<?php include '$cachePath';?>";
    }
}