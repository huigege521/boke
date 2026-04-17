@echo off

REM 切换到项目目录（使用相对路径）
cd /d "%~dp0"

REM 执行定时发布命令
php spark blog:publish-scheduled

REM 输出执行时间
echo 执行时间: %date% %time%

REM 记录日志
echo %date% %time% - 定时发布任务执行完成 >> publish_scheduled.log

REM 暂停以查看输出（可选，生产环境可注释掉）
REM pause
