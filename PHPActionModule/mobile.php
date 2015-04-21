<?php
require_once (dirname(__FILE__) . "/include/common.inc.php");
header("Content-Type: text/html; charset=utf-8");
require_once(dirname(__FILE__)."/include/mobile.inc.php");

$dsql = new DedeSql(false);
$cfg_templets_dir = $cfg_basedir.$cfg_templets_dir;
$channellist = '';
$newartlist = '';
$channellistnext = '';
$hostName = 'http://' . $_SERVER['HTTP_HOST'];

if(empty($action))
{
    $action = 'index';
}
elseif ($action !='list' && $action != 'index' && $action != 'article') 
{
    die('action error');
}



//顶级导航列表
$dsql->SetQuery("Select id,typename From `#@__arctype` where reid=0 And channeltype=1 And ishidden=0 And ispart<>2 order by sortrank limit 0,10");
$dsql->Execute();
$channellist = '<li><a href="' . $hostName . '">首页</a></li>';
while($row=$dsql->GetObject())
{
    $channellist .= "<li><a href='{$hostName}/list/{$row->id}'>{$row->typename}</a></li>";
}

//当前时间
$curtime = strftime("%Y-%m-%d %H:%M:%S",time());
//站点名称
$cfg_webname = ConvertStr($cfg_webname);

//主页
if($action=='index')
{

    //最新文章10篇
    $dsql->SetQuery("Select id,title,pubdate From `#@__archives` where channel=1 And arcrank = 0 order by id desc limit 0,10");
    $dsql->Execute();
    while($row=$dsql->GetObject())
    {
        $newartlist .= "<li class='am-g'><a href='{$hostName}/article/{$row->id}' class='am-list-item-hd'>".ConvertStr($row->title)."</a></li>";
    }

    //typeid=1的前10条
    $dsql->SetQuery("Select id,title,pubdate From `#@__archives` where channel=1 And arcrank = 0 And typeid=1 order by id desc limit 0,5");
    $dsql->Execute();
    while($row=$dsql->GetObject())
    {
        $newartlist2 .= "<li class='am-g'><a href='{$hostName}/article/{$row->id}' class='am-list-item-hd'>".ConvertStr($row->title)."</a></li>";
    }

    //typeid=2的前10条
    $dsql->SetQuery("Select id,title,pubdate From `#@__archives` where channel=1 And arcrank = 0 And typeid=2 order by id desc limit 0,5");
    $dsql->Execute();
    while($row=$dsql->GetObject())
    {
        $newartlist3 .= "<li class='am-g'><a href='{$hostName}/article/{$row->id}' class='am-list-item-hd'>".ConvertStr($row->title)."</a></li>";
    }

    //找4张幻灯片flag=f的文章
    $dsql->SetQuery("Select id,title,litpic From `#@__archives` where channel=1 And FIND_IN_SET('f',flag) order by id desc limit 0,4");
    $dsql->Execute();
    while($row=$dsql->GetObject())
    {
        $newartlistSlide .= "<li><a href='{$hostName}/article/{$row->id}'><img style='max-width:500px;margin: 0 auto;height:180px;' src='{$row->litpic}'><div class='am-slider-desc'>{$row->title}</div></a></li>";
    }

    //找4张最新图片flag=p的文章
    $dsql->SetQuery("Select id,title,litpic From `#@__archives` where channel=1 And FIND_IN_SET('p',flag) order by pubdate desc limit 0,4");
    $dsql->Execute();

    while($row=$dsql->GetObject())
    {
        $newartlistPic .= "<li><div class='am-gallery-item'><a href='{$hostName}/article/{$row->id}' class=><img style='width:145px;height:82px;' src='{$row->litpic}'/><h3 class='am-gallery-title'>{$row->title}</h3><div class='am-gallery-desc'>".date("m-d",$row->pubdate)."</div></a></div></li>";
    }

    $dsql->Close();
    include($cfg_templets_dir."/mobile/index.htm");
    
    exit();
}
//列表
else if ($action=='list')
{
    $id = ereg_replace("[^0-9]", '', $id);
    if(empty($id))
    {
        exit('List Error!');
    }

    require_once(dirname(__FILE__)."/include/datalistcpWap.class.php");
    $row = $dsql->GetOne("Select typename,ishidden, description,seotitle,keywords From `#@__arctype` where id='$id' ");

    if($row['ishidden']==1)
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

    while($row=$dsql->GetObject())
    {
        $channellistnext .= "<li><a href='{$hostName}/list/{$row->id}'>".ConvertStr($row->typename)."</a></li>";
    }

    //栏目内容(分页输出)
    $sids    = GetSonIds($id,1,true);
    $varlist = "cfg_webname,typename,channellist,channellistnext,cfg_templeturl";
    $dlist   = new DataListCP();
    $dlist->SetTemplet($cfg_templets_dir."/mobile/list.htm");
    $dlist->pageSize = 10;
    $dlist->SetSource("Select id,title,pubdate,click From `#@__archives` where typeid in($sids) And arcrank=0 order by id desc");
    $dlist->Display();
}
else if ($action=='article')
{
    $id = ereg_replace("[^0-9]", '', $id);
    if(empty($id))
    {
        exit('Article Error!');
    }
    //文档信息
    $query = "Select tp.typename,tp.ishidden,arc.typeid,arc.title,arc. keywords,arc. description,arc.arcrank,arc.pubdate,arc.writer,arc.click,addon.body From `#@__archives` arc 
      left join `#@__arctype` tp on tp.id=arc.typeid
      left join `#@__addonarticle` addon on addon.aid=arc.id
      where arc.id='$id' ";
    $row = $dsql->GetOne($query,MYSQL_ASSOC);
    foreach($row as $k=>$v)
    {
        $$k = $v;
    }
    unset($row);
    $pubdate = strftime("%y-%m-%d",$pubdate);
    if($arcrank!=0) exit();
    $title = ConvertStr($title);
    $keywords = ConvertStr($keywords);
    $description = ConvertStr($description);

    if($ishidden==1)
    {
        exit('article is hiddening');
    }
    $dsql->Close();

    //文章分页开始
    $newBody = split("#p#(.*)#e#",$body);
    $newBodyLength = count($newBody);

    if($newBodyLength > 1){

        if(empty($pageno) || preg_match("#[^0-9]#", $pageno))
        {
            $pageno = 1;
        }

        $pagination = articlePagination($id, $newBodyLength, $pageno);

        $body = $newBody[($pageno-1)] . $pagination;

        include($cfg_templets_dir."/mobile/article.htm");

        exit();
    
    }
    else
    {
       include($cfg_templets_dir."/mobile/article.htm");
       exit();
    }
}
else
{
    exit('error');
}

?>