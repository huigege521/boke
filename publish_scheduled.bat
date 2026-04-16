@echo off

REM 切换到项目目录
cd /d d:\phpstudy_pro\WWW\projects_nodejs\codeigniter-blog

REM 执行定时发布命令
php spark blog:publish-scheduled

REM 输出执行时间
echo 执行时间: %date% %time%

REM 暂停以查看输出
pause
