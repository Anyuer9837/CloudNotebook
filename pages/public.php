<?php
// 设置时区
date_default_timezone_set('Asia/Shanghai');

// 引入核心文件
require_once(__DIR__ . '/../system/core.php');

// 获取笔记本ID
$id = isset($_GET['id']) ? trim($_GET['id']) : '';

// 如果没有提供ID，重定向到首页
if (empty($id)) {
    header('Location: ' . APP_BASE . 'index.php');
    exit;
}

// 获取数据库实例
$db = NotebookDB::getInstance();

// 检查笔记本是否存在
if (!$db->notebookExists($id)) {
    die('笔记本不存在');
}

// 检查笔记本是否公开
if (!$db->isPublic($id)) {
    die('该笔记本未设置为公开');
}

// 获取笔记本内容
$content = $db->getNotebookContent($id);
?>
<!DOCTYPE html>
<html lang="zh">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title><?php echo htmlspecialchars($id); ?> - 云笔记</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?php echo APP_BASE; ?>css/app.css">
    <script>window.APP_BASE = '<?php echo APP_BASE; ?>';</script>
    <script src="<?php echo APP_BASE; ?>js/highlight.min.js"></script>
    <script src="<?php echo APP_BASE; ?>js/markdown-it.min.js"></script>
    <script src="<?php echo APP_BASE; ?>js/markdown-bundle.js"></script>
    <script src="<?php echo APP_BASE; ?>js/main.js"></script>
    <style>
        /* 隐藏所有滚动条的关键样式 */
        ::-webkit-scrollbar {
            width: 0 !important;
            height: 0 !important;
            display: none !important;
        }
        * {
            scrollbar-width: none !important;
            -ms-overflow-style: none !important;
        }

        :root {
            --primary: #4a6bfa;
            --primary-dark: #3a56d4;
            --secondary: #6c63ff;
            --accent: #22d3ee;
            --dark: #12141f;
            --darker: #171a29;
            --card-bg: rgba(255, 255, 255, 0.04);
            --card-border: rgba(255, 255, 255, 0.08);
            --light: #f0f2f5;
            --gray: #8b93a7;
            --success: #10b981;
            --danger: #ef4444;
            --border-radius: 16px;
            --card-shadow: 0 10px 40px rgba(0, 0, 0, 0.25);
            --card-shadow-hover: 0 20px 60px rgba(74, 107, 250, 0.25);
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        * { box-sizing: border-box; }

        body {
            background-color: var(--dark);
            color: var(--light);
            margin: 0;
            font-family: "SF Pro Display", "SF Pro Icons", "Helvetica Neue", "Microsoft YaHei", "Segoe UI", sans-serif;
            min-height: 100vh;
            line-height: 1.6;
            overflow-x: hidden;
        }

        /* 阅读页固定舒适宽度，!important 防止 main.js 把它拉成 1500px */
        .container {
            max-width: 900px !important;
            margin: 0 auto;
            padding: 30px 20px;
            padding-left: max(20px, env(safe-area-inset-left));
            padding-right: max(20px, env(safe-area-inset-right));
            position: relative;
            z-index: 1;
            height: auto;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
            flex-wrap: wrap;
            margin-bottom: 30px;
            padding: 18px 0;
            border-bottom: 1px solid var(--card-border);
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 1.8em;
            font-weight: 700;
            color: white;
            text-decoration: none;
            width: auto;
            flex-shrink: 0;
        }
        .logo span {
            background: linear-gradient(90deg, var(--primary), var(--secondary));
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }
        .logo i {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            width: 42px;
            height: 42px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 12px;
            font-size: 0.9em;
            box-shadow: 0 6px 18px rgba(74, 107, 250, 0.4);
        }

        /* 笔记本标题徽章 */
        .note-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 6px 14px;
            border-radius: 100px;
            background: rgba(74, 107, 250, 0.12);
            border: 1px solid rgba(74, 107, 250, 0.3);
            color: #a5b4fc;
            font-size: 0.85em;
            font-weight: 600;
            max-width: 100%;
            overflow: hidden;
        }
        .note-badge span {
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .note-badge i { color: var(--accent); flex-shrink: 0; }

        .back-link {
            color: var(--gray);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 1em;
            font-weight: 600;
            transition: var(--transition);
            padding: 9px 16px;
            border-radius: 10px;
            border: 1px solid var(--card-border);
            flex-shrink: 0;
        }

        .back-link:hover {
            color: white;
            background: rgba(74, 107, 250, 0.15);
            border-color: rgba(74, 107, 250, 0.4);
        }

        /* 玻璃拟态内容卡片 */
        .content-card {
            position: relative;
            background: var(--card-bg);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid var(--card-border);
            border-radius: 20px;
            padding: 40px;
            margin-bottom: 30px;
            box-shadow: var(--card-shadow);
            min-height: calc(100vh - 220px);
            overflow: hidden; /* 关键：约束子内容，防止超框 */
        }

        .content-card::before {
            content: '';
            position: absolute;
            inset: 0;
            border-radius: 20px;
            padding: 1px;
            background: linear-gradient(135deg, rgba(74, 107, 250, 0.4), transparent 40%, transparent 60%, rgba(108, 99, 255, 0.3));
            -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
            -webkit-mask-composite: xor;
            mask-composite: exclude;
            pointer-events: none;
        }

        .markdown-content {
            color: var(--light);
            font-size: 16px;
            line-height: 1.8;
            /* 关键：长单词/长 URL 自动换行，杜绝横向超框 */
            overflow-wrap: break-word;
            word-wrap: break-word;
            word-break: break-word;
        }

        .markdown-content h1 {
            font-size: 30px;
            font-weight: 800;
            color: #ffffff;
            margin: 0 0 24px 0;
            padding-bottom: 14px;
            border-bottom: 1px solid var(--card-border);
        }

        .markdown-content h2 {
            font-size: 24px;
            color: #ffffff;
            margin: 34px 0 16px;
        }

        .markdown-content h3 {
            font-size: 20px;
            color: #ffffff;
            margin: 24px 0 14px;
        }

        .markdown-content p {
            margin: 0 0 16px 0;
            color: #c9d1d9;
        }

        .markdown-content a {
            color: #7c93ff;
            text-decoration: none;
            border-bottom: 1px solid rgba(124, 147, 255, 0.35);
            transition: var(--transition);
        }
        .markdown-content a:hover {
            color: var(--accent);
            border-bottom-color: var(--accent);
        }

        .markdown-content pre {
            background: #0d1017;
            border: 1px solid var(--card-border);
            padding: 18px;
            border-radius: 12px;
            overflow-x: auto; /* 代码块横向滚动而非撑破 */
            margin: 18px 0;
            max-width: 100%;
        }

        .markdown-content code {
            font-family: 'SF Mono', Consolas, Monaco, monospace;
            font-size: 14px;
            color: #e6e6e6;
            background: rgba(255, 255, 255, 0.08);
            padding: 2px 6px;
            border-radius: 5px;
            overflow-wrap: break-word;
            word-break: break-word;
        }

        .markdown-content pre code {
            background: transparent;
            padding: 0;
            color: #e6e6e6;
            white-space: pre;
            word-break: normal;
        }

        .markdown-content blockquote {
            border-left: 4px solid var(--primary);
            margin: 18px 0;
            padding: 10px 18px;
            background: rgba(74, 107, 250, 0.1);
            border-radius: 0 10px 10px 0;
            color: #c9d1d9;
        }

        .markdown-content ul,
        .markdown-content ol {
            margin: 16px 0;
            padding-left: 24px;
            color: #c9d1d9;
        }

        .markdown-content li {
            margin: 8px 0;
        }

        .markdown-content img {
            max-width: 100%;
            height: auto;
            border-radius: 10px;
            margin: 18px 0;
            display: block;
        }

        /* 表格外层滚动容器，防止宽表格溢出卡片 */
        .markdown-content .table-wrap {
            width: 100%;
            overflow-x: auto;
            margin: 18px 0;
        }

        .markdown-content table {
            width: 100%;
            border-collapse: collapse;
            color: #c9d1d9;
        }

        .markdown-content th,
        .markdown-content td {
            border: 1px solid var(--card-border);
            padding: 10px 14px;
            text-align: left;
        }

        .markdown-content th {
            background: rgba(255, 255, 255, 0.05);
            font-weight: 600;
            color: var(--light);
            white-space: nowrap;
        }

        .markdown-content hr {
            border: none;
            border-top: 1px solid var(--card-border);
            margin: 26px 0;
        }

        /* 动态背景光晕 */
        .background {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            z-index: 0;
            overflow: hidden;
        }

        .shape {
            position: absolute;
            border-radius: 50%;
            filter: blur(80px);
            opacity: 0.14;
        }

        .shape-1 {
            top: -200px;
            right: -150px;
            width: 560px;
            height: 560px;
            background: var(--primary);
            animation: float 11s ease-in-out infinite alternate;
        }

        .shape-2 {
            bottom: -200px;
            left: -150px;
            width: 480px;
            height: 480px;
            background: var(--secondary);
            animation: float 13s ease-in-out infinite alternate-reverse;
        }

        .shape-3 {
            top: 40%;
            left: 50%;
            width: 320px;
            height: 320px;
            background: var(--accent);
            opacity: 0.07;
            animation: float 15s ease-in-out infinite alternate;
        }

        @keyframes float {
            0% { transform: translate(0, 0); }
            100% { transform: translate(30px, 40px); }
        }

        @media (max-width: 768px) {
            .container {
                padding: 16px 14px;
                padding-left: max(14px, env(safe-area-inset-left));
                padding-right: max(14px, env(safe-area-inset-right));
            }

            .header {
                padding: 14px 0;
                margin-bottom: 22px;
            }

            .logo { font-size: 1.5em; }
            .logo i { width: 36px; height: 36px; }

            .content-card {
                padding: 24px 18px;
                border-radius: 16px;
                min-height: calc(100vh - 170px);
            }
            .content-card::before { border-radius: 16px; }

            .markdown-content { font-size: 15px; }

            .markdown-content h1 { font-size: 24px; }
            .markdown-content h2 { font-size: 20px; }
            .markdown-content h3 { font-size: 18px; }
        }

        @media (max-width: 480px) {
            .header { justify-content: center; }
            /* 覆盖 main.js 注入的 logo 固定宽度，使其内容真正居中 */
            .logo {
                width: auto !important;
                justify-content: center;
            }
            .back-link { font-size: 0.92em; padding: 8px 14px; }
        }
    </style>
</head>
<body>
    <div class="background">
        <div class="shape shape-1"></div>
        <div class="shape shape-2"></div>
        <div class="shape shape-3"></div>
    </div>

    <div class="container">
        <header class="header">
            <a href="<?php echo APP_BASE; ?>index.php" class="logo">
                <i class="fas fa-book"></i>
                <span>云笔记</span>
            </a>
            <div class="note-badge">
                <i class="fas fa-globe"></i>
                <span>公开笔记 · <?php echo htmlspecialchars($id); ?></span>
            </div>
            <a href="<?php echo APP_BASE; ?>index.php" class="back-link">
                <i class="fas fa-arrow-left"></i>
                返回首页
            </a>
        </header>

        <div class="content-card">
            <div class="markdown-content" id="content">
                <?php echo htmlspecialchars($content); ?>
            </div>
        </div>
    </div>

    <script>
        // 等待Markdown解析器初始化完成
        window.mdInitialized.then(() => {
            const content = document.getElementById('content');
            const markdown = content.textContent.trim();
            
            // 确保 markdown-it 正确初始化
            if (typeof markdownit !== 'undefined') {
                window.md = markdownit({
                    html: true,
                    breaks: true,
                    linkify: true,
                    typographer: true
                });
            }
            
            // 渲染 Markdown
            content.innerHTML = window.md.render(markdown);

            // 给表格套上可横向滚动的容器，防止宽表格溢出卡片
            content.querySelectorAll('table').forEach((table) => {
                if (table.parentElement && table.parentElement.classList.contains('table-wrap')) return;
                const wrap = document.createElement('div');
                wrap.className = 'table-wrap';
                table.parentNode.insertBefore(wrap, table);
                wrap.appendChild(table);
            });
            
            // 代码高亮
            document.querySelectorAll('pre code').forEach((block) => {
                hljs.highlightBlock(block);
            });

            // 添加渐入动画
            const elements = document.querySelectorAll('.content-card, .notebook-title');
            elements.forEach((el, index) => {
                el.style.opacity = '0';
                el.style.transform = 'translateY(20px)';
                el.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
                
                setTimeout(() => {
                    el.style.opacity = '1';
                    el.style.transform = 'translateY(0)';
                }, 100 * index);
            });
        });
    </script>
</body>
</html>
