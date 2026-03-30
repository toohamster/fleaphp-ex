# CLAUDE.md

此文件指导 Claude Code 在此仓库中工作。

## 操作规范

- **操作语言：中文**
- **修改任何代码前必须先给方案，用户确认后再执行，禁止擅自修改代码**
- **每次开始新任务前，必须重新阅读本规则**
- **修改文件内容时必须逐个文件阅读和编辑，禁止使用正则批量替换**
- **记忆文件位置：`.claude/memory/memory.md`（项目目录下）**
- `SPEC.md` 是框架规格说明书，作为开发任务参考基准
- FLEA/ 目录代码有变更时，更新 `SPEC.md` 保持同步
- FLEA/ 目录有重要改动后，更新 `CHANGES.md`
- demo/ 目录有重要改动后，更新 `demo/APP_CHANGES.md`
- 每次代码改动完成后，将 git commit 说明追加到 `GIT_COMMIT.md`
- 代码改动完成后等待用户 review，确认后再执行 git commit
- 明确需求后再操作，不确定先问，不猜测
- 只做用户明确要求的事，完成后立即停止
- 发起 MR 和打 Tag 使用 GitHub API（`curl`），不使用 `gh` CLI

## Setup

```bash
cp demo/.env.example demo/.env
mysql -u root -p < demo/blog.sql
php74 ~/bin/composer.phar install
php bin/flea-cli --project-dir=demo
# 访问 http://127.0.0.1:8081/index.php
```

数据库默认：Host=`127.0.0.1:3306`, DB=`blog`, User=`root`, Password=`11111111`

PHP 版本：**7.4.32**（命令：`php74`）

## 项目概览

- 框架代码：`src/FLEA/`，演示应用：`demo/App/`
- 详细架构和配置说明见 `SPEC.md`
- **无测试套件和 linter**
