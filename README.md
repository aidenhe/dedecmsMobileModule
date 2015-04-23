#织梦dedecms 移动模块安装说明
@(PHP)[织梦]

[toc]

两个版本(动态|静态)页面都是一致，git上提供UTF-8版本、一个伪静态、一个真静态，如果网站小，建议使用动态版，如果网站大，建议使用静态html版本。模板基于妹子UI控件~

GBK插件下载（目前只有静态版本）：http://pan.baidu.com/s/1dDtT0mH

* `PHPActionModule`文件夹代表动态版本，含有xml插件
* `makeMobileHtml`文件夹代表生成html静态版本，含有xml插件


![](http://img.my.csdn.net/uploads/201504/23/1429718923_4661.gif-thumb.jpg)

有问题上博客提问：http://helonghua.com/2015/03/23/dedecms-wap/

##动态版本PHPActionModule

文件夹PHPActionModule代表动态版本
目录结构：

	-9526a6769b7af50b4450ab7f26c30be6.xml
	-mobile.php
	-include/
	-templets/

**注意：**只有9526a6769b7af50b4450ab7f26c30be6.xml 这个xml插件对你有用，其他的都是源代码，用来参考。

###安装
1. 下载xml插件
2.  登陆织梦后台
	![Alt text](http://img.my.csdn.net/uploads/201504/21/1429616876_2810.png)
3. 动态版本，不需要后台手动控制，现在配置rewrite
以下是配置规则，选择符合自己主机情况去配置

-------------
* 1.apache vhost配置rewrite（!!!不是.htaccess）
* 2.apache .htaccess 配置rewrite
* 3.nginx rewrite

-------------
一、apache vhost配置rewrite（!!!不是.htaccess）
在http-vhost.conf中增加
```
<VirtualHost *:80>
	RewriteEngine on
	DocumentRoot "/***你的织梦网站根目录/"
	ServerName mobile.dede.com
	#DirectoryIndex 很重要
	DirectoryIndex mobile.php 
	RewriteCond %{DOCUMENT_ROOT}%{REQUEST_FILENAME} !-f
	RewriteCond %{DOCUMENT_ROOT}%{REQUEST_FILENAME} !-d
	RewriteRule ^/(list|article)/(.*)$ /mobile.php?action=$1&id=$2 [QSA,L]
</VirtualHost>
```
二、apache .htaccess 配置rewrite（!前提是服务器支持.htaccess）

如果你的网站有.htaccess 则在这个文件中加一段
```
RewriteCond $1 ^(list|article)
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)/(.*)$ /mobile.php?action=$1&id=$2 [QSA,L]
```
如果没有，则构建一个.htaccess 写入
```
<IfModule rewrite_module>
    RewriteEngine on
    RewriteBase /
    RewriteCond $1 ^(list|article)
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule ^(.*)/(.*)$ /mobile.php?action=$1&id=$2 [QSA,L]
</IfModule>
```

三、nginx rewrite

```
location ~* /(list|article)/ {
	if (!-f $request_filename){
		rewrite ^/(.*)/(.*)$ /mobile.php?action=$1&id=$2 last;
	}
}
```
---------------

##mobile生成静态版

与静态版本一样，下载`makeMobileHtml`文件夹下的XML 。

gbk版本下载地址：http://pan.baidu.com/s/1dDtT0mH

到织梦后台安装模块

安装完之后：出现模块控制

![](http://ww4.sinaimg.cn/large/005ItG0Rjw1erfh0ey4g4j304608z74e.jpg)

按字面意思很好理解。不做解释了

**注意：**以下说的mobile.dede.com 为你的移动端域名;www.dede.com 是你站点主域名www.dede.com;/dede/目录为织梦管理后台目录

###文件说明

* 静态文档全都生成在根目录的/m/文件夹下
* 模板文件在/templets/mobile/文件夹下
* css js image 资源文件在/m/asset/文件夹下
* php生成html的php控制器在/dede[这是默认的admin后台目录]/makeMobileHtml.php

###安装完第一步【很重要】

修改`/dede[这是默认的admin后台目录]/makeMobileHtml.php` 中
第14行`$hostName = 'http://mobile.dede.com';`

为你的mobile端域名，或者是这种`http://www.dede.com/m` 

**注意不要在末尾加**`/`

最后解析域名 `mobile.dede.com` 到根目录`/m/`文件夹下面

###其他配置

如果你的网站全站资源都是绝对路径，那已经不需要修改什么了。如果你的图片路径是`/uploads/img`
这样的相对路径、同时由启用的 二级独立域名`mobile.***.com`

 那你就需要在apache 或者nginx 中rewrite了

####apache vhost配置

增加

	RewriteEngine on
	RewriteCond $1 ^(uploads)
	RewriteRule ^/(.*)$ http://www.dede.com/$1 [L]
	
**注意：**www.dede.com 是你主站的主域名。

####.htaccess

.htaccess 注意是在 /m/目录哦，不要放错地方了~

	<IfModule rewrite_module>
    	RewriteEngine on
    	RewriteBase /
    	RewriteCond $1 ^(uploads)
    	RewriteRule ^(.*)$ http://www.dede.com/$1 [L]
	</IfModule>
	

####nginx

在mobile.dede.com 的server 配置里加，别加错了

```
location ~* /(uploads)/ {
		rewrite ^/(.*)$ http://www.dede.com/$1 last;
}
```

###添加文章的时候自动生成移动端html

![](http://ww1.sinaimg.cn/large/005ItG0Rjw1erfh0ga30fj30m8059q38.jpg)

如果想要实现上面的功能，很简单，方法我已经写好了。

打开管理后台目录【默认是/dede/】,找到`article_add.php`

直接看文档末尾，找到"成功发布文章"

如图所示添加代码：

![](http://ww1.sinaimg.cn/large/005ItG0Rjw1erfh0dtt8ej30m8045t9o.jpg)

代码：

	$mobile_update_revalue = "<table width='40%' style='border:1px dashed #cdcdcd;margin-left:20px;margin-bottom:15px' id='tgtable' align='left'><tr><td bgcolor='#EBF5C9'>&nbsp;<strong>更新mobile文档页</strong>\r\n</td></tr>\r\n<tr><td>\r\n<iframe name='stafrm' frameborder='0' id='stafrm' width='100%' height='100px' src='makeMobileHtml.php?action=article&id={$arcID}'></iframe>\r\n</td></tr>\r\n</table>";

	$mobile_update_revalue .= "<table width='40%' style='border:1px dashed #cdcdcd;margin-left:20px;margin-bottom:15px' id='tgtable' align='left'><tr><td bgcolor='#EBF5C9'>&nbsp;<strong>更新mobile列表页</strong>\r\n</td></tr>\r\n<tr><td>\r\n<iframe name='stafrm' frameborder='0' id='stafrm' width='100%' height='100px' src='makeMobileHtml.php?action=list&id={$typeid}'></iframe>\r\n</td></tr>\r\n</table>";


把原php中

`$msg = "<div style=\"line-height:36px;height:36px\">{$msg}</div>".GetUpdateTest()`

替换成

`$msg = "<div style=\"line-height:36px;height:36px\">{$msg}</div>".GetUpdateTest().$mobile_update_revalue;`

也就是连接我们上面写的字符串



