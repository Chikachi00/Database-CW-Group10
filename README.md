# Internship Assessment System — COMP1044 Group 10

## 简介

这是我们为 COMP1044 课程作业开发的一个网页端实习成绩管理系统，用来替代过去那种用 Excel 表格手动记录、手动算分的方式。系统支持两种角色登录：Admin 负责管理学生、评审员和实习分配；Assessor（讲师/导师）负责给分和写评语，系统会自动按固定权重算出最终成绩。

数据库用 MySQL，前端是 HTML + CSS + JavaScript，后端是 PHP（PDO）。

---

## 功能说明

### Admin 端

- **Dashboard**：登进去先看到统计概览，包括学生总数、评审员数、已评/待评人数、全校平均分，还有个进度条。卡片可以点击跳转到对应管理页面。
- **学生管理**：增删改查，可以按专业筛选。
- **评审员管理**：新增/编辑/删除评审员账号，编辑时密码可选填（留空则不修改）。
- **实习分配**：把学生分配给对应评审员，记录公司名称和备注。
- **成绩查看**：查看所有学生的详细分数，支持搜索、表头排序，双击行可弹出详情和评语。成绩按分段显示颜色标签（70+ 绿色、50–69 蓝色、低于 50 红色）。
- **导出 CSV**：一键把所有评估结果导出为 CSV 文件。

### Assessor 端

- **Dashboard**：显示自己负责的学生数、已完成/待完成数量，以及自己给出的平均分。
- **评分页面**：从下拉搜索中选学生，输入 8 项分数（0–100 的原始分），页面实时显示加权后的总分预览。提交前会弹出确认框，提交后不可修改。
- **结果查看**：查看自己已评学生的明细分数，支持搜索和排序。

### 通用功能

- 登录后根据角色自动跳转到对应页面，非授权页面会重定向回登录页。
- 所有数据库操作用 PDO prepared statements，防止 SQL 注入。
- 前后端都有校验：前端限制输入格式，后端再次验证分数范围和必填项。
- 删除操作有二次确认弹窗，关联数据（如已分配实习的学生）无法直接删除。
- 登录时可记住用户名（cookie，30 天有效期）。

---

## 目录结构

```
COMP1044_CW_G10/
├── Admin/
│   ├── admin_help_modal.php       # 帮助说明弹窗
│   ├── dashboard.php              # Admin 主页
│   ├── export_results.php         # CSV 导出
│   ├── manage_internships.php     # 实习分配管理
│   ├── manage_students.php        # 学生管理
│   ├── manage_users.php           # 评审员账号管理
│   └── view_all_results.php       # 全部成绩查看
├── Assessor/
│   ├── assessor_dashboard.php     # Assessor 主页
│   ├── assessor_help_modal.php    # 帮助说明弹窗
│   ├── evaluate_student.php       # 评分表单
│   └── submit_marks.php           # 提交处理 + 个人成绩查看
├── Includes/
│   └── db_connect.php             # 数据库连接
├── images/
│   └── logo.png
├── login.php
├── logout.php
├── style.css
└── README.md
```

---

## 本地运行方法

1. 安装 XAMPP（或 WAMP / MAMP），启动 Apache 和 MySQL。
2. 打开 phpMyAdmin，点 Import，选择 `COMP1044_Database.sql` 导入。这个文件里已经包含建表语句和示例数据，导入一次就行，不需要再手动添加数据。
3. 把整个项目文件夹放到 `htdocs/` 目录下。
4. 浏览器访问 `http://localhost/COMP1044_CW_G10/login.php`。

---

## 测试账号

| 角色 | 用户名 | 密码 |
|------|--------|------|
| Admin | `admin` | `admin123` |
| Assessor | `Dr_smith` | `smith123` |
| Assessor | `Prof_jones` | `jones123` |

---

## 示例数据

导入后数据库里有 6 个学生，覆盖了不同评分段和状态：

| 学生 | 专业 | 评审员 | 状态 |
|------|------|--------|------|
| Alice Wong (S2024001) | Computer Science | Dr_smith | 已评 — 92.50 |
| Bob Chen (S2024002) | Software Engineering | Dr_smith | 已评 — 74.00 |
| Charlie Davis (S2024003) | Information Technology | Dr_smith | **待评** |
| Diana Lim (S2024004) | Computer Science | Prof_jones | 已评 — 84.50 |
| Evan Taylor (S2024005) | Data Science | Prof_jones | 已评 — 58.50 |
| Frank Wilson (S2024006) | Software Engineering | Dr_smith | 已评 — 42.50 |

用 `Dr_smith` 登录时，导航栏会出现红色角标"Evaluate [1]"，因为 Charlie 还没评，方便演示实时提醒功能。

---

## 技术栈

| 层次 | 技术 |
|------|------|
| 前端 | HTML5、CSS3、原生 JavaScript |
| 后端 | PHP 7+，PDO |
| 数据库 | MySQL 8.0 |
| 服务器 | Apache（XAMPP） |

---

## 成员

- Lin Yiwei
- Deng Changhui
- Cao Shiyu
