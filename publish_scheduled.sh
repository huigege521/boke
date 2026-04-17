#!/bin/bash

# 切换到脚本所在目录（使用相对路径）
cd "$(dirname "$0")"

# 执行定时发布命令
php spark blog:publish-scheduled

# 输出执行时间并记录日志
echo "执行时间: $(date)" >> publish_scheduled.log

# 检查执行结果
if [ $? -eq 0 ]; then
    echo "$(date) - 定时发布任务执行成功" >> publish_scheduled.log
else
    echo "$(date) - 定时发布任务执行失败" >> publish_scheduled.log
    exit 1
fi
