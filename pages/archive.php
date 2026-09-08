<?php
// 设置时区
date_default_timezone_set('Asia/Shanghai');

// 引入核心文件
require_once(__DIR__ . '/../system/core.php');

// 初始化变量
$notebooks = [];
$message = '';
$has_searched = false;

// 分页设置
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) { $page = 1; }
$per_page = 10; // 每页显示10条记录
$total_notebooks = 0;
$offset = ($page - 1) * $per_page;

// 处理归档码查询
if (isset($_GET['archive_code']) && !empty($_GET['archive_code'])) {
    $archive_code = trim($_GET['archive_code']);
    $has_searched = true;
    
    try {
        $db = NotebookDB::getInstance();
        // 计算偏移量
        $offset = ($page - 1) * $per_page;
        // 获取总记录数和分页数据
        $total_notebooks = $db->countNotebooksByArchiveCode($archive_code);
        $notebooks = $db->getNotebooksByArchiveCode($archive_code, $offset, $per_page);
    } catch (Exception $e) {
        $message = "查询失败: " . $e->getMessage();
    }
}

// 计算总页数
$total_pages = ceil($total_notebooks / $per_page);
?>
<!DOCTYPE html>
<html lang="zh">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>云笔记 - 归档码查询</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* 隐藏所有滚动条 */
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

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html { scroll-behavior: smooth; }

        body {
            font-family: "SF Pro Display", "SF Pro Icons", "Helvetica Neue", "Microsoft YaHei", "Segoe UI", sans-serif;
            background-color: var(--dark);
            color: var(--light);
            line-height: 1.6;
            min-height: 100vh;
            overflow-x: hidden;
            position: relative;
        }

        /* ===== 背景光晕 ===== */
        .background {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
            overflow: hidden;
        }

        .shape {
            position: absolute;
            opacity: 0.12;
            filter: blur(70px);
            transform: translateZ(0);
        }

        .shape-1 {
            background: var(--primary);
            width: 600px;
            height: 600px;
            top: -300px;
            right: -100px;
            border-radius: 40% 60% 70% 30% / 40% 50% 60% 50%;
            animation: float 10s ease-in-out infinite alternate;
        }

        .shape-2 {
            background: var(--secondary);
            width: 500px;
            height: 500px;
            bottom: -200px;
            left: -100px;
            border-radius: 60% 40% 30% 70% / 60% 30% 70% 40%;
            animation: float 12s ease-in-out infinite alternate-reverse;
        }

        .shape-3 {
            background: var(--accent);
            width: 340px;
            height: 340px;
            top: 42%;
            left: 46%;
            opacity: 0.06;
            border-radius: 50%;
            animation: float 14s ease-in-out infinite alternate;
        }

        @keyframes float {
            0% { transform: translate(0, 0) rotate(0deg); }
            100% { transform: translate(30px, 50px) rotate(10deg); }
        }

        .container {
            max-width: 900px !important;
            width: 100%;
            margin: 0 auto;
            padding: 30px 24px 60px;
            position: relative;
            min-height: 100vh;
        }

        /* ===== 顶部导航 ===== */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
            flex-wrap: wrap;
            margin-bottom: 46px;
            padding-bottom: 18px;
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

        .logo span {
            background: linear-gradient(90deg, var(--primary), var(--secondary));
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }

        .top-nav {
            display: flex;
            gap: 10px;
            align-items: center;
        }

        .nav-link {
            color: var(--gray);
            text-decoration: none;
            font-weight: 600;
            font-size: 1em;
            transition: var(--transition);
            padding: 9px 16px;
            border-radius: 10px;
            border: 1px solid var(--card-border);
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .nav-link:hover {
            color: white;
            background: rgba(74, 107, 250, 0.15);
            border-color: rgba(74, 107, 250, 0.4);
        }

        /* ===== 页面标题区 ===== */
        .page-head {
            text-align: center;
            margin-bottom: 32px;
        }

        .page-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 7px 16px;
            border-radius: 100px;
            background: rgba(74, 107, 250, 0.12);
            border: 1px solid rgba(74, 107, 250, 0.3);
            color: #a5b4fc;
            font-size: 0.88em;
            font-weight: 600;
            margin-bottom: 18px;
        }

        .page-badge i { color: var(--accent); }

        .page-title {
            font-size: 2.4em;
            font-weight: 800;
            line-height: 1.2;
            background: linear-gradient(90deg, #ffffff, #a5b4fc);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }

        .page-subtitle {
            color: var(--gray);
            font-size: 1.02em;
            margin: 12px auto 0;
            max-width: 560px;
        }

        /* ===== 玻璃拟态查询卡片 ===== */
        .archive-card {
            position: relative;
            background: var(--card-bg);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid var(--card-border);
            border-radius: 20px;
            padding: 30px;
            margin-bottom: 34px;
            box-shadow: var(--card-shadow);
            transition: var(--transition);
            overflow: hidden;
        }

        .archive-card::before {
            content: '';
            position: absolute;
            inset: 0;
            border-radius: 20px;
            padding: 1px;
            background: linear-gradient(135deg, rgba(74, 107, 250, 0.5), transparent 40%, transparent 60%, rgba(108, 99, 255, 0.4));
            -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
            -webkit-mask-composite: xor;
            mask-composite: exclude;
            pointer-events: none;
        }

        .archive-card:hover {
            box-shadow: var(--card-shadow-hover);
        }

        .panel-hint {
            font-size: 0.9em;
            color: var(--gray);
            margin-bottom: 20px;
            display: flex;
            align-items: flex-start;
            gap: 8px;
            line-height: 1.5;
        }
        .panel-hint i { color: var(--accent); margin-top: 4px; }

        .archive-form {
            display: flex;
            gap: 12px;
            align-items: stretch;
        }

        .input-wrap {
            position: relative;
            flex: 1;
        }

        .input-wrap > i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--gray);
            pointer-events: none;
        }

        .archive-input {
            width: 100%;
            padding: 15px 15px 15px 44px;
            border: 2px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            font-size: 1em;
            outline: none;
            background: rgba(255, 255, 255, 0.05);
            color: white;
            transition: var(--transition);
        }

        .archive-input::placeholder { color: rgba(255, 255, 255, 0.3); }

        .archive-input:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(74, 107, 250, 0.25);
            background: rgba(255, 255, 255, 0.08);
        }

        .archive-button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 9px;
            background: linear-gradient(90deg, var(--primary), var(--secondary));
            color: white;
            border: none;
            border-radius: 12px;
            padding: 15px 26px;
            font-size: 1.02em;
            font-weight: 600;
            cursor: pointer;
            white-space: nowrap;
            transition: var(--transition);
        }

        .archive-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 26px rgba(74, 107, 250, 0.4);
        }

        /* ===== 结果区标题 ===== */
        .result-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            flex-wrap: wrap;
            margin-bottom: 16px;
        }

        .result-title {
            font-size: 1.15em;
            font-weight: 700;
            color: white;
            display: inline-flex;
            align-items: center;
            gap: 9px;
        }
        .result-title i { color: var(--primary); }

        .result-count {
            font-size: 0.86em;
            color: #a5b4fc;
            background: rgba(74, 107, 250, 0.12);
            border: 1px solid rgba(74, 107, 250, 0.3);
            padding: 5px 13px;
            border-radius: 100px;
            font-weight: 600;
        }

        /* ===== 笔记本卡片 ===== */
        .notebook-list {
            display: flex;
            flex-direction: column;
            gap: 14px;
        }

        .notebook-card {
            position: relative;
            display: block;
            text-decoration: none;
            background: var(--card-bg);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid var(--card-border);
            border-radius: var(--border-radius);
            padding: 20px 22px;
            transition: var(--transition);
            overflow: hidden;
        }

        .notebook-card::after {
            content: '\f054'; /* fa-chevron-right */
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            position: absolute;
            right: 22px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--gray);
            font-size: 0.85em;
            opacity: 0;
            transition: var(--transition);
        }

        .notebook-card:hover {
            transform: translateY(-4px);
            border-color: rgba(74, 107, 250, 0.45);
            background: rgba(74, 107, 250, 0.07);
            box-shadow: var(--card-shadow-hover);
        }

        .notebook-card:hover::after {
            opacity: 1;
            right: 16px;
            color: var(--accent);
        }

        .notebook-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            flex-wrap: wrap;
            margin-bottom: 14px;
        }

        .notebook-id {
            font-size: 1.1em;
            font-weight: 700;
            color: var(--light);
            display: flex;
            align-items: center;
            gap: 10px;
            min-width: 0;
            overflow-wrap: anywhere;
        }

        .notebook-id i {
            width: 34px;
            height: 34px;
            flex-shrink: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 10px;
            font-size: 0.85em;
            color: var(--primary);
            background: linear-gradient(135deg, rgba(74, 107, 250, 0.2), rgba(108, 99, 255, 0.2));
        }

        .notebook-code {
            font-size: 0.82em;
            color: #a5b4fc;
            display: inline-flex;
            align-items: center;
            gap: 7px;
            background: rgba(74, 107, 250, 0.12);
            border: 1px solid rgba(74, 107, 250, 0.3);
            padding: 5px 13px;
            border-radius: 100px;
            font-weight: 600;
            max-width: 100%;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .notebook-meta {
            color: var(--gray);
            font-size: 0.88em;
            display: flex;
            flex-wrap: wrap;
            gap: 10px 26px;
            padding-top: 12px;
            border-top: 1px solid var(--card-border);
        }

        .meta-item {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .meta-item i {
            color: var(--primary);
            opacity: 0.85;
            font-size: 0.95em;
        }

        /* ===== 空状态 ===== */
        .empty-message {
            text-align: center;
            padding: 56px 26px;
            background: var(--card-bg);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border-radius: 20px;
            color: var(--gray);
            border: 1px dashed rgba(255, 255, 255, 0.12);
        }

        .empty-icon {
            width: 74px;
            height: 74px;
            line-height: 74px;
            margin: 0 auto 20px;
            border-radius: 20px;
            font-size: 1.7em;
            color: var(--primary);
            background: linear-gradient(135deg, rgba(74, 107, 250, 0.18), rgba(108, 99, 255, 0.18));
        }

        .empty-title {
            font-size: 1.2em;
            font-weight: 700;
            color: white;
            margin-bottom: 8px;
        }

        .empty-text { font-size: 0.94em; }

        /* ===== 错误提示 ===== */
        .message {
            background: rgba(239, 68, 68, 0.1);
            color: #fca5a5;
            padding: 14px 18px;
            border-radius: 12px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            border: 1px solid rgba(239, 68, 68, 0.25);
            font-size: 0.95em;
        }

        /* ===== 分页 ===== */
        .pagination {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            margin-top: 32px;
            gap: 8px;
        }

        .pagination a, .pagination span {
            flex: 0 0 auto;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 38px;
            height: 38px;
            padding: 0 12px;
            border-radius: 11px;
            background: var(--card-bg);
            color: var(--light);
            text-decoration: none;
            transition: var(--transition);
            font-size: 0.92em;
            font-weight: 600;
            border: 1px solid var(--card-border);
        }

        .pagination a:hover {
            background: rgba(74, 107, 250, 0.15);
            border-color: rgba(74, 107, 250, 0.45);
            transform: translateY(-2px);
        }

        .pagination .current {
            background: linear-gradient(90deg, var(--primary), var(--secondary));
            color: white;
            border-color: transparent;
            box-shadow: 0 6px 18px rgba(74, 107, 250, 0.35);
        }

        .pagination .disabled {
            opacity: 0.35;
            cursor: not-allowed;
        }

        .record-info {
            font-size: 0.88em;
            color: var(--gray);
            text-align: center;
            margin-top: 16px;
        }

        /* ===== 页脚 ===== */
        .footer {
            text-align: center;
            margin-top: 60px;
            padding-top: 26px;
            border-top: 1px solid rgba(255, 255, 255, 0.06);
        }

        .footer-text {
            color: var(--gray);
            font-size: 0.88em;
        }

        /* ===== 响应式 ===== */
        @media (max-width: 768px) {
            .container {
                max-width: 100% !important;
                padding: 22px 16px 46px;
                padding-left: max(16px, env(safe-area-inset-left));
                padding-right: max(16px, env(safe-area-inset-right));
            }

            .header {
                flex-direction: column;
                align-items: center;
                gap: 16px;
                margin-bottom: 32px;
            }

            .logo { width: auto !important; justify-content: center; }
            .top-nav { flex-wrap: wrap; justify-content: center; }

            .page-title { font-size: 1.9em; }

            .archive-card { padding: 24px 20px; }

            .archive-form { flex-direction: column; }
            .archive-button { width: 100%; }

            .notebook-card::after { display: none; }
        }

        @media (max-width: 480px) {
            .container { padding: 18px 14px 40px; }
            .logo { font-size: 1.5em; }
            .logo i { width: 36px; height: 36px; }
            .nav-link { font-size: 0.9em; padding: 8px 12px; }
            .page-badge { font-size: 0.8em; padding: 6px 12px; }
            .page-title { font-size: 1.6em; }
            .page-subtitle { font-size: 0.95em; }
            .archive-card { padding: 20px 16px; border-radius: 16px; }
            .archive-card::before { border-radius: 16px; }
            .archive-input { padding: 14px 14px 14px 42px; font-size: 0.98em; }
            .notebook-card { padding: 18px 16px; }
            .notebook-header { gap: 8px; }
            .notebook-id { font-size: 1em; }
            .notebook-meta { gap: 8px 18px; font-size: 0.84em; }
            .pagination a, .pagination span {
                min-width: 33px;
                height: 33px;
                padding: 0 9px;
                font-size: 0.85em;
            }
        }
    </style>
</head>
<body>
    <!-- 背景光晕 -->
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
            <nav class="top-nav">
                <a href="<?php echo APP_BASE; ?>index.php" class="nav-link">
                    <i class="fas fa-house"></i> 返回首页
                </a>
                <a href="<?php echo APP_BASE; ?>pages/admin.php" class="nav-link">
                    <i class="fas fa-user-shield"></i> 管理员入口
                </a>
            </nav>
        </header>

        <div class="page-head">
            <div class="page-badge">
                <i class="fas fa-key"></i> 凭一个归档码，找回整组笔记本
            </div>
            <h1 class="page-title">归档码查询</h1>
            <p class="page-subtitle">归档码是笔记本的「分组标签」，输入归档码即可列出该组下的所有笔记本。</p>
        </div>

        <div class="archive-card">
            <p class="panel-hint">
                <i class="fas fa-circle-info"></i>
                在笔记本的「设置」中可为其填写归档码；多个笔记本使用同一归档码即视为一组。
            </p>
            <form method="get" class="archive-form">
                <div class="input-wrap">
                    <i class="fas fa-key"></i>
                    <input type="text"
                           name="archive_code"
                           class="archive-input"
                           placeholder="请输入笔记本的归档码"
                           value="<?php echo isset($_GET['archive_code']) ? htmlspecialchars($_GET['archive_code']) : ''; ?>"
                           required>
                </div>
                <button type="submit" class="archive-button">
                    <i class="fas fa-magnifying-glass"></i> 查找笔记本
                </button>
            </form>
        </div>

        <?php if (!empty($message)): ?>
            <div class="message">
                <i class="fas fa-exclamation-circle"></i>
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <?php if ($has_searched): ?>
            <?php if (!empty($notebooks)): ?>
                <div class="result-head">
                    <div class="result-title">
                        <i class="fas fa-folder-open"></i> 查询结果
                    </div>
                    <div class="result-count">共 <?php echo $total_notebooks; ?> 个笔记本</div>
                </div>

                <div class="notebook-list">
                <?php foreach ($notebooks as $notebook): ?>
                    <a href="<?php echo APP_BASE; ?>pages/notebook.php?id=<?php echo urlencode($notebook['id']); ?>" class="notebook-card">
                        <div class="notebook-header">
                            <div class="notebook-id">
                                <i class="fas fa-book"></i>
                                <?php echo htmlspecialchars($notebook['id']); ?>
                            </div>
                            <div class="notebook-code">
                                <i class="fas fa-key"></i>
                                <?php echo htmlspecialchars($_GET['archive_code']); ?>
                            </div>
                        </div>
                        <div class="notebook-meta">
                            <div class="meta-item">
                                <i class="fas fa-clock"></i>
                                创建于 <?php echo date('Y年m月d日', strtotime($notebook['created_at'])); ?>
                            </div>
                            <div class="meta-item">
                                <i class="fas fa-pen"></i>
                                更新于 <?php echo date('Y年m月d日', strtotime($notebook['updated_at'])); ?>
                            </div>
                        </div>
                    </a>
                <?php endforeach; ?>
                </div>

                <!-- 分页导航 -->
                <?php if ($total_pages > 1): ?>
                    <div class="pagination">
                        <?php if ($page > 1): ?>
                            <a href="?archive_code=<?php echo urlencode($_GET['archive_code']); ?>&page=1">&laquo;</a>
                            <a href="?archive_code=<?php echo urlencode($_GET['archive_code']); ?>&page=<?php echo $page - 1; ?>">&lt;</a>
                        <?php else: ?>
                            <span class="disabled">&laquo;</span>
                            <span class="disabled">&lt;</span>
                        <?php endif; ?>
                        
                        <?php
                        // 显示页码（固定窗口大小，保持翻页器宽度稳定）
                        $window = 3; // 中间始终显示的页码数量
                        if ($total_pages <= $window) {
                            $start_page = 1;
                            $end_page = $total_pages;
                        } else {
                            $start_page = max(1, $page - 1);
                            $end_page = $start_page + $window - 1;
                            if ($end_page > $total_pages) {
                                $end_page = $total_pages;
                                $start_page = $end_page - $window + 1;
                            }
                        }
                        
                        for ($i = $start_page; $i <= $end_page; $i++): ?>
                            <?php if ($i == $page): ?>
                                <span class="current"><?php echo $i; ?></span>
                            <?php else: ?>
                                <a href="?archive_code=<?php echo urlencode($_GET['archive_code']); ?>&page=<?php echo $i; ?>"><?php echo $i; ?></a>
                            <?php endif; ?>
                        <?php endfor; ?>
                        
                        <?php if ($page < $total_pages): ?>
                            <a href="?archive_code=<?php echo urlencode($_GET['archive_code']); ?>&page=<?php echo $page + 1; ?>">&gt;</a>
                            <a href="?archive_code=<?php echo urlencode($_GET['archive_code']); ?>&page=<?php echo $total_pages; ?>">&raquo;</a>
                        <?php else: ?>
                            <span class="disabled">&gt;</span>
                            <span class="disabled">&raquo;</span>
                        <?php endif; ?>
                    </div>
                    
                    <!-- 记录信息 -->
                    <div class="record-info">
                        显示 <?php echo $total_notebooks; ?> 条记录中的 
                        <?php echo ($offset + 1); ?> 到 
                        <?php echo min($offset + $per_page, $total_notebooks); ?> 条
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <div class="empty-message">
                    <div class="empty-icon"><i class="fas fa-folder-open"></i></div>
                    <div class="empty-title">未找到相关笔记本</div>
                    <p class="empty-text">没有使用该归档码的笔记本，请确认归档码是否正确。</p>
                </div>
            <?php endif; ?>
        <?php endif; ?>

        <footer class="footer">
            <p class="footer-text">© <?php echo date('Y'); ?> 云笔记 - 安全、简洁、高效的在线记事工具 - By欲儿</p>
        </footer>
    </div>

    <script>
        // 渐入动画
        document.addEventListener('DOMContentLoaded', function () {
            var elements = document.querySelectorAll('.header, .page-head, .archive-card, .result-head, .notebook-card, .empty-message, .pagination');
            elements.forEach(function (el, index) {
                el.style.opacity = '0';
                el.style.transform = 'translateY(18px)';
                el.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
                setTimeout(function () {
                    el.style.opacity = '1';
                    el.style.transform = 'translateY(0)';
                }, 70 * index);
            });

            // 自动聚焦查询框（无查询结果时）
            var input = document.querySelector('.archive-input');
            if (input && !input.value) {
                input.focus();
            }
        });
    </script>
</body>
</html>
