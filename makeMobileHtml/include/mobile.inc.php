<?php
if(!defined('DEDEINC')) exit('Request Error!');

require_once(DEDEINC.'/channelunit.class.php');


/**
*描述：处理特殊字符
*@param string $str 
*@return string $str
*/
function ConvertStr($str)
{
    $str = str_replace("&amp;","##amp;",$str);
    $str = str_replace("&","&amp;",$str);
    $str = ereg_replace("[\"><']","",$str);
    $str = str_replace("##amp;","&amp;",$str);
    return $str;
}

/**
*描述：文章分页获取分页条
*@param string $aid 文章id
*@param string $newBodyLength 分页长度，分成几页
*@param string $pageno 当前第几页
*@return string $pagination  分页html
*/
function articlePagination($aid, $newBodyLength, $pageno = 1)
{
    $pagination = '<ul class="am-pagination am-pagination-centered">';

    for($k=1;$k<=$newBodyLength;$k++)
    {
        if ($k == $pageno)
        {
            $pagination .= '<li class="am-active"><a href="#">' . $k . '</a></li>';
        }
        else
        {
            if ($k == '1')
            {
                $pagination .= '<li><a href="' . $aid . '.html">' . $k . '</a></li>';
            }
            else
            {
                $pagination .= '<li><a href="' . $aid .'_' . $k . '.html">' . $k . '</a></li>';
            }

        }
    }
    $pagination .= '</ul>';

    return $pagination;
}

?>
