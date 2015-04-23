<?php
require_once(dirname(__FILE__).'/config.php');
require_once(DEDEINC . "/common.inc.php");
require_once(DEDEINC."/mobile.inc.php");

header ("Content-Type: text/html; charset=utf-8");

$dsql = new DedeSql(false);
$cfg_templets_dir = $cfg_basedir.$cfg_templets_dir;
$channellist = '';
$newartlist = '';
$channellistnext = '';

$hostName = 'http://mobile.dede.com';

if (empty($action))
{
    $action = 'index';
}
elseif ($action !='list' && $action != 'index' && $action != 'article' && $action != 'allArticle' && $action != 'allList')
{
    die('action error');
}


//顶级导航列表
$dsql->SetQuery("Select id,typename From `#@__arctype` where reid=0 And channeltype=1 And ishidden=0 And ispart<>2 order by sortrank limit 0,10");
$dsql->Execute();
$channellist = '<li><a href="' . $hostName . '">首页</a></li>';
while ($row=$dsql->GetObject())
{
    $channellist .= "<li><a href='{$hostName}/list/{$row->id}.html'>{$row->typename}</a></li>";
}

//当前时间
$curtime = strftime("%Y-%m-%d %H:%M:%S",time());
//站点名称
$cfg_webname = ConvertStr($cfg_webname);

//主页
if ($action=='index')
{

    //最新文章10篇
    $dsql->SetQuery("Select id,title,pubdate From `#@__archives` where channel=1 And arcrank = 0 order by id desc limit 0,10");
    $dsql->Execute();
    while ($row=$dsql->GetObject())
    {
        $newartlist .= "<li class='am-g'><a href='{$hostName}/article/{$row->id}.html' class='am-list-item-hd'>".ConvertStr($row->title)."</a></li>";
    }

    //typeid=1的前5条
    $dsql->SetQuery("Select id,title,pubdate From `#@__archives` where channel=1 And arcrank = 0 And typeid=1 order by id desc limit 0,5");
    $dsql->Execute();
    while ($row=$dsql->GetObject())
    {
        $newartlist2 .= "<li class='am-g'><a href='{$hostName}/article/{$row->id}.html' class='am-list-item-hd'>".ConvertStr($row->title)."</a></li>";
    }

    //typeid=2的前5条
    $dsql->SetQuery("Select id,title,pubdate From `#@__archives` where channel=1 And arcrank = 0 And typeid=2 order by id desc limit 0,5");
    $dsql->Execute();
    while ($row=$dsql->GetObject())
    {
        $newartlist3 .= "<li class='am-g'><a href='{$hostName}/article/{$row->id}.html' class='am-list-item-hd'>".ConvertStr($row->title)."</a></li>";
    }

    //找4张幻灯片flag=f的文章
    $dsql->SetQuery("Select id,title,litpic From `#@__archives` where channel=1 And FIND_IN_SET('f',flag) order by id desc limit 0,4");
    $dsql->Execute();
    while ($row=$dsql->GetObject())
    {
        $newartlistSlide .= "<li><a href='{$hostName}/article/{$row->id}.html'><img style='max-width:500px;margin: 0 auto;height:180px;' src='{$row->litpic}'><div class='am-slider-desc'>{$row->title}</div></a></li>";
    }

    //找4张最新图片flag=p的文章
    $dsql->SetQuery("Select id,title,litpic From `#@__archives` where channel=1 And FIND_IN_SET('p',flag) order by pubdate desc limit 0,4");
    $dsql->Execute();

    while ($row=$dsql->GetObject())
    {
        $newartlistPic .= "<li><div class='am-gallery-item'><a href='{$hostName}/article/{$row->id}.html' class=><img style='width:145px;height:82px;' src='{$row->litpic}'/><h3 class='am-gallery-title'>{$row->title}</h3><div class='am-gallery-desc'>".date("m-d",$row->pubdate)."</div></a></div></li>";
    }

    $dsql->Close();
    ob_start();
    include($cfg_templets_dir."/mobile/index.htm");
    $pageBody = ob_get_contents();
    ob_end_clean();
    $fp = @fopen('../m/index.html', 'w') or die('读取失败，确定有写入权限？'); 
    fwrite($fp,$pageBody);
    fclose($fp);
    exit('更新主页成功');
}
//列表生成
else if($action=='list')
{
    $id = ereg_replace("[^0-9]", '', $id);
    if (empty($id))
    {
        exit('List Error!');
    }

    require (DEDEINC."/datalistcpWap.class.php");
    $row = $dsql->GetOne("Select typename,ishidden, description,seotitle,keywords From `#@__arctype` where id='$id' ");

    if ($row['ishidden']==1)
    {
        exit('this listID is hiddening');
    }

    $typename    = ConvertStr($row['typename']);
    $keywords    = ConvertStr($row['typename']);
    $description = ConvertStr($row['description']);
    $seotitle    = ConvertStr($row['seotitle']);

    //当前栏目下级分类
    $dsql->SetQuery("Select id,typename From `#@__arctype` where reid='$id' And channeltype=1 And ishidden=0 And ispart<>2 order by sortrank");
    $dsql->Execute();

    while ($row=$dsql->GetObject())
    {
        $channellistnext .= "<li><a href='{$hostName}/list/{$row->id}.html'>".ConvertStr($row->typename)."</a></li>";
    }

    //栏目内容(分页输出)
    $sids    = GetSonIds($id,1,true);
    $varlist = "cfg_webname,typename,channellist,channellistnext,cfg_templeturl";
    $dlist   = new DataListCP();
    //设置分页url
    $dlist->purl = $hostName . '/list/' . $id;
    $dlist->SetTemplet($cfg_templets_dir."/mobile/list.htm");
    $dlist->pageSize = 10;
    $dlist->SetSource("Select id,title,pubdate,click From `#@__archives` where typeid in($sids) And arcrank=0 order by id desc");
    
    if (empty($pageno))
    {
        $pageno = 1;
    }

    ob_start();
    $dlist->Display();
    $pageBody = ob_get_contents();
    ob_end_clean();

    if ($pageno == 1)
    {
        $fp = @fopen('../m/list/' . $id . '.html', 'w') or die('读取失败，确定有写入权限？'); 
    }
    else
    {
        $fp = @fopen('../m/list/' . $id . '_' . $pageno . '.html', 'w') or die('读取失败，确定有写入权限？'); 
    }
    fwrite($fp,$pageBody);
    fclose($fp);

    $totalPage = $dlist->totalResult/$dlist->pageSize;

    if ($pageno <= $totalPage){
        ShowMsg($pageno .'---'.($pageno + 1), './makeMobileHtml.php?action=list&id='.$id.'&totalresult='.$dlist->totalResult.'&pageno=' . ($pageno+1),0,100);
    }
    else
    {
        exit('更新列表'.$id.'成功');
    }
}
//一键生成所有列表页
else if($action=='allList')
{
    $dsql->SetQuery("Select id From `#@__arctype` where channeltype=1 And ishidden=0 order by sortrank");
    $dsql->Execute();

    while ($row=$dsql->GetObject())
    {
        $makeListIframe .= "<iframe frameborder='1' width='100%' height='100px' name='stafrm' src='makeMobileHtml.php?action=list&id={$row->id}'></iframe> \r\n";
    }

    echo $makeListIframe;
    exit();
}
//文档
else if($action=='article')
{
    $id = ereg_replace("[^0-9]", '', $id);
    if (empty($id))
    {
        exit('Article Error!');
    }
    //文档信息
    $query = "Select tp.typename,tp.ishidden,arc.typeid,arc.title,arc. keywords,arc. description,arc.arcrank,arc.pubdate,arc.writer,arc.click,addon.body From `#@__archives` arc 
      left join `#@__arctype` tp on tp.id=arc.typeid
      left join `#@__addonarticle` addon on addon.aid=arc.id
      where arc.id='$id' ";
    $row = $dsql->GetOne($query,MYSQL_ASSOC);
    foreach ($row as $k=>$v)
    {
        $$k = $v;
    }
    unset($row);
    $pubdate = strftime("%y-%m-%d",$pubdate);
    if($arcrank!=0) exit();
    $title = ConvertStr($title);
    $keywords = ConvertStr($keywords);
    $description = ConvertStr($description);

    if ($ishidden==1)
    {
        exit('article is hiddening');
    }
    $dsql->Close();

    //文章分页开始
    $newBody = split("#p#(.*)#e#",$body);
    $newBodyLength = count($newBody);

    if($newBodyLength > 1){

        foreach ($newBody as $key => $val){
            $pagination = articlePagination($id, $newBodyLength, $key+1);
            $body = $newBody[$key] . $pagination;
            ob_start();
            include($cfg_templets_dir."/mobile/article.htm");
            $pageBody = ob_get_contents();
            ob_end_clean();
            if ($key == 0)
            {
              $fp = @fopen('../m/article/'.$id.'.html', 'w') or die('读取失败，确定有写入权限？'); 
            }
            else
            {
              $fp = @fopen('../m/article/'.$id. '_'. ($key+1) . '.html', 'w') or die('读取失败，确定有写入权限？'); 
            }
            fwrite($fp,$pageBody);
            fclose($fp);
            unset($pageBody);
        }
        exit('更新文章mobile成功');
    }
    else
    {
        ob_start();
        include($cfg_templets_dir."/mobile/article.htm");
        $pageBody = ob_get_contents();
        ob_end_clean();
        $fp = @fopen('../m/article/'.$id.'.html', 'w') or die('读取失败，确定有写入权限？'); 
        fwrite($fp,$pageBody);
        fclose($fp);
        exit('更新文章mobile成功');
    }

}
else if($action=='allArticle')
{
    //获取最新文章的id（最大id文章数）
    $total = $dsql->GetOne("SELECT id FROM `#@__arctiny`  order by id desc limit 0,1 " );

    if(empty($id) || preg_match("#[^0-9]#", $id))
    {
        $id = 1;
    }
    //一次http请求生成20篇文章

    for($i=$id;$i<($id + 20);$i++){
        //文档信息
        $query = "Select tp.typename,tp.ishidden,arc.typeid,arc.title,arc. keywords,arc. description,arc.arcrank,arc.pubdate,arc.writer,arc.click,addon.body From `#@__archives` arc 
          left join `#@__arctype` tp on tp.id=arc.typeid
          left join `#@__addonarticle` addon on addon.aid=arc.id
          where arc.id='$i' ";
        $row = $dsql->GetOne($query,MYSQL_ASSOC);
        foreach ($row as $k=>$v)
        {
            $$k = $v;
        }
        unset($row);
        $pubdate = strftime("%y-%m-%d",$pubdate);
        $title = ConvertStr($title);
        $keywords = ConvertStr($keywords);
        $description = ConvertStr($description);
        $dsql->Close();

        //判断文章分页开始
        $newBody = split("#p#(.*)#e#",$body);
        $newBodyLength = count($newBody);

        if($newBodyLength > 1){
            foreach ($newBody as $key => $val){
                $pagination = articlePagination($i, $newBodyLength, $key+1);
                $body = $newBody[$key] . $pagination;
                ob_start();
                include($cfg_templets_dir."/mobile/article.htm");
                $pageBody = ob_get_contents();
                ob_end_clean();
                if ($key == 0)
                {
                  $fp = @fopen('../m/article/'.$i.'.html', 'w') or die('读取失败，确定有写入权限？'); 
                }
                else
                {
                  $fp = @fopen('../m/article/'.$i. '_'. ($key+1) . '.html', 'w') or die('读取失败，确定有写入权限？'); 
                }
                fwrite($fp,$pageBody);
                fclose($fp);
                unset($pageBody);
            }
        }
        else
        {
            ob_start();
            include($cfg_templets_dir."/mobile/article.htm");
            $pageBody = ob_get_contents();
            ob_end_clean();
            $fp = @fopen('../m/article/'.$i.'.html', 'w') or die('读取失败，确定有写入权限？'); 
            fwrite($fp,$pageBody);
            fclose($fp);
        }
        //进入下一次循环之前unset $pageBody
        unset($pageBody);
    }
    //判断是否小于文章总数
    if ($id <= $total['id']){
        ShowMsg($id .'---'.($id+20), './makeMobileHtml.php?action=allArticle&id=' . ($id+20),0,300);
    }
    else
    {
        exit('文章已经全部更新完了~~~~~');
    }

}
else
{
    die('ERROR');
}

?>