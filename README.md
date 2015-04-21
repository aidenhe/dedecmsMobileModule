#织梦dedecms 移动模块安装说明
@(PHP)[织梦]

[toc]

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
