<?php
// 设置时区
date_default_timezone_set('Asia/Shanghai');

// 引入核心文件
require_once('system/core.php');

// 预填充归档码查询框（若从 archive.php 跳转回来）
$prefill_archive = isset($_GET['archive_code']) ? htmlspecialchars($_GET['archive_code']) : '';
// 决定默认激活的标签页
$active_tab = !empty($prefill_archive) ? 'archive' : 'note';
?>
<!DOCTYPE html>
<html lang="zh">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>云笔记 - 安全、简洁的在线记事本</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
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
            color: white;
            line-height: 1.6;
            position: relative;
            min-height: 100vh;
            overflow-x: hidden;
        }

        /* 背景几何元素 */
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
            width: 360px;
            height: 360px;
            top: 40%;
            left: 45%;
            opacity: 0.06;
            border-radius: 50%;
            animation: float 14s ease-in-out infinite alternate;
        }

        @keyframes float {
            0% { transform: translate(0, 0) rotate(0deg); }
            100% { transform: translate(30px, 50px) rotate(10deg); }
        }

        .container {
            max-width: 1200px !important;
            margin: 0 auto;
            padding: 30px 24px;
            position: relative;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 60px;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 1.8em;
            font-weight: 700;
            color: white;
            text-decoration: none;
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
            gap: 12px;
            align-items: center;
        }

        .nav-link, .admin-link {
            color: var(--gray);
            text-decoration: none;
            font-weight: 600;
            font-size: 1.05em;
            transition: var(--transition);
            padding: 8px 15px;
            border-radius: 10px;
            display: inline-flex;
            align-items: center;
            gap: 7px;
        }

        .nav-link:hover, .admin-link:hover {
            color: white;
            background-color: rgba(255, 255, 255, 0.08);
        }

        .hero {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 90px;
            gap: 60px;
        }

        .hero-content {
            flex: 1;
        }

        .hero-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 7px 16px;
            border-radius: 100px;
            background: rgba(74, 107, 250, 0.12);
            border: 1px solid rgba(74, 107, 250, 0.3);
            color: #a5b4fc;
            font-size: 0.9em;
            font-weight: 600;
            margin-bottom: 24px;
        }

        .hero-badge i { color: var(--accent); }

        .hero-title {
            font-size: 3.6em;
            font-weight: 800;
            line-height: 1.15;
            margin-bottom: 22px;
            background: linear-gradient(90deg, #ffffff, #a5b4fc);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }

        .hero-subtitle {
            font-size: 1.2em;
            color: var(--gray);
            margin-bottom: 34px;
            max-width: 520px;
        }

        .hero-stats {
            display: flex;
            gap: 40px;
        }

        .stat-item { display: flex; flex-direction: column; }
        .stat-num {
            font-size: 1.8em;
            font-weight: 800;
            background: linear-gradient(90deg, var(--primary), var(--accent));
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }
        .stat-label { color: var(--gray); font-size: 0.9em; }

        /* 玻璃拟态卡片 */
        .card {
            position: relative;
            background: var(--card-bg);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid var(--card-border);
            border-radius: 20px;
            padding: 34px;
            box-shadow: var(--card-shadow);
            transition: var(--transition);
            flex: 1;
            max-width: 440px;
            overflow: hidden;
        }

        .card::before {
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

        .card:hover {
            box-shadow: var(--card-shadow-hover);
            transform: translateY(-5px);
        }

        /* Tab 切换 */
        .tabs {
            display: flex;
            gap: 6px;
            background: rgba(0, 0, 0, 0.25);
            padding: 5px;
            border-radius: 12px;
            margin-bottom: 26px;
        }

        .tab-btn {
            flex: 1;
            padding: 11px 10px;
            border: none;
            background: transparent;
            color: var(--gray);
            font-size: 0.98em;
            font-weight: 600;
            cursor: pointer;
            border-radius: 9px;
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 7px;
        }

        .tab-btn:hover { color: white; }

        .tab-btn.active {
            background: linear-gradient(90deg, var(--primary), var(--secondary));
            color: white;
            box-shadow: 0 6px 18px rgba(74, 107, 250, 0.35);
        }

        .tab-panel { display: none; animation: fadeIn 0.35s ease; }
        .tab-panel.active { display: block; }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(6px); }
            to { opacity: 1; transform: translateY(0); }
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
        .panel-hint i { color: var(--accent); margin-top: 3px; }

        .form-group {
            margin-bottom: 22px;
        }

        .form-label {
            display: block;
            margin-bottom: 10px;
            font-weight: 600;
            color: var(--light);
            font-size: 1.05em;
        }

        .input-wrap { position: relative; }

        .input-wrap > i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--gray);
            pointer-events: none;
        }

        .form-input {
            width: 100%;
            padding: 15px 15px 15px 44px;
            border: 2px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            background-color: rgba(255, 255, 255, 0.05);
            color: white;
            font-size: 1em;
            transition: var(--transition);
        }

        .form-input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(74, 107, 250, 0.25);
            background-color: rgba(255, 255, 255, 0.08);
        }

        .form-input::placeholder {
            color: rgba(255, 255, 255, 0.3);
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 9px;
            background: linear-gradient(90deg, var(--primary), var(--secondary));
            color: white;
            border: none;
            padding: 15px 20px;
            border-radius: 12px;
            font-size: 1.05em;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            text-align: center;
            text-decoration: none;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 26px rgba(74, 107, 250, 0.4);
        }

        .btn-full {
            width: 100%;
        }

        .section-head {
            text-align: center;
            margin-bottom: 50px;
        }

        .section-tag {
            color: var(--primary);
            font-weight: 700;
            letter-spacing: 2px;
            font-size: 0.85em;
            text-transform: uppercase;
        }

        .section-title {
            font-size: 2.3em;
            font-weight: 800;
            margin-top: 8px;
            background: linear-gradient(90deg, #ffffff, #a5b4fc);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }

        .features {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 24px;
            margin-bottom: 100px;
        }

        .feature-card {
            background: var(--card-bg);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border-radius: var(--border-radius);
            padding: 32px 26px;
            transition: var(--transition);
            text-align: center;
            border: 1px solid var(--card-border);
        }

        .feature-card:hover {
            transform: translateY(-8px);
            box-shadow: var(--card-shadow);
            border-color: rgba(74, 107, 250, 0.4);
            background: rgba(74, 107, 250, 0.06);
        }

        .feature-icon {
            font-size: 1.7em;
            width: 68px;
            height: 68px;
            line-height: 68px;
            text-align: center;
            border-radius: 18px;
            background: linear-gradient(135deg, rgba(74, 107, 250, 0.2), rgba(108, 99, 255, 0.2));
            margin: 0 auto 22px;
            color: var(--primary);
        }

        .feature-card:nth-child(4) .feature-icon { color: var(--accent); }

        .feature-title {
            font-size: 1.25em;
            margin-bottom: 12px;
            color: white;
        }

        .feature-text {
            color: var(--gray);
            font-size: 0.95em;
            line-height: 1.6;
        }

        /* 归档码说明区 */
        .archive-guide {
            background: linear-gradient(135deg, rgba(74, 107, 250, 0.08), rgba(108, 99, 255, 0.05));
            border: 1px solid var(--card-border);
            border-radius: 24px;
            padding: 50px 40px;
            margin-bottom: 100px;
            text-align: center;
        }

        .archive-guide .section-title { font-size: 2em; }
        .archive-guide > p {
            color: var(--gray);
            max-width: 620px;
            margin: 14px auto 40px;
        }

        .steps {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 26px;
            margin-bottom: 36px;
        }

        .step {
            position: relative;
            padding: 26px 22px;
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid var(--card-border);
            border-radius: var(--border-radius);
            text-align: left;
        }

        .step-num {
            width: 38px;
            height: 38px;
            border-radius: 10px;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            margin-bottom: 16px;
            box-shadow: 0 6px 16px rgba(74, 107, 250, 0.35);
        }

        .step h4 { font-size: 1.1em; margin-bottom: 8px; }
        .step p { color: var(--gray); font-size: 0.92em; }

        .footer {
            text-align: center;
            margin-top: 50px;
            padding-top: 30px;
            border-top: 1px solid rgba(255, 255, 255, 0.06);
        }

        .footer-text {
            color: var(--gray);
            font-size: 0.9em;
        }

        .footer-links {
            margin-top: 20px;
            display: flex;
            justify-content: center;
            gap: 20px;
        }

        .footer-link {
            color: var(--gray);
            text-decoration: none;
            font-size: 0.9em;
            transition: var(--transition);
        }

        .footer-link:hover {
            color: white;
        }

        @media (max-width: 992px) {
            .hero {
                flex-direction: column;
                gap: 40px;
                margin-bottom: 60px;
            }
            .hero-title {
                font-size: 2.8em;
            }
            .hero-subtitle { max-width: none; }
            .card { max-width: none; width: 100%; }
        }

        @media (max-width: 768px) {
            .container {
                padding: 22px 16px;
                padding-left: max(16px, env(safe-area-inset-left));
                padding-right: max(16px, env(safe-area-inset-right));
            }
            .header {
                flex-direction: column;
                align-items: center;
                gap: 18px;
                margin-bottom: 40px;
            }
            /* 覆盖 main.js 注入的 logo 固定宽度，使其内容真正居中 */
            .logo {
                width: auto !important;
                justify-content: center;
            }
            .top-nav {
                flex-wrap: wrap;
                justify-content: center;
                gap: 8px;
            }
            .nav-link, .admin-link { font-size: 0.95em; padding: 7px 12px; }
            .hero { gap: 32px; margin-bottom: 56px; }
            .hero-title {
                font-size: 2.2em;
            }
            .hero-title br { display: none; }
            .hero-subtitle { font-size: 1.08em; margin-bottom: 28px; }
            .hero-stats { gap: 22px; }
            .stat-num { font-size: 1.5em; }
            .card { padding: 26px 20px; }
            .section-title { font-size: 1.9em; }
            .features {
                grid-template-columns: 1fr;
            }
            .archive-guide { padding: 36px 22px; margin-bottom: 70px; }
            .archive-guide .section-title { font-size: 1.7em; }
        }

        /* 手机竖屏细化适配 */
        @media (max-width: 480px) {
            .container { padding: 18px 14px; }
            .logo { font-size: 1.5em; }
            .logo i { width: 36px; height: 36px; }
            .top-nav { gap: 6px; }
            .nav-link, .admin-link { font-size: 0.88em; padding: 6px 10px; }
            .hero-badge {
                font-size: 0.8em;
                padding: 6px 12px;
                white-space: normal;
                text-align: center;
                line-height: 1.4;
            }
            .hero-title { font-size: 1.85em; }
            .hero-subtitle { font-size: 1em; }
            /* 让统计项在极窄屏均分换行，避免溢出 */
            .hero-stats {
                flex-wrap: wrap;
                gap: 16px 24px;
            }
            .stat-num { font-size: 1.3em; }
            .stat-label { font-size: 0.82em; }
            .card { padding: 22px 16px; border-radius: 16px; }
            .card::before { border-radius: 16px; }
            /* Tab 按钮在窄屏可换行，图标与文字不再挤在一行 */
            .tabs { flex-wrap: wrap; }
            .tab-btn { font-size: 0.9em; padding: 10px 8px; }
            .form-input { padding: 14px 14px 14px 42px; font-size: 0.98em; }
            .btn { padding: 14px 18px; font-size: 1em; }
            .section-title { font-size: 1.6em; }
            .archive-guide { padding: 30px 18px; border-radius: 18px; }
            .archive-guide .section-title { font-size: 1.5em; }
            .archive-guide > p { font-size: 0.95em; }
            .steps { gap: 18px; }
            .step { padding: 22px 18px; }
            .footer-links { flex-wrap: wrap; gap: 14px; }
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
            <a href="index.php" class="logo">
                <i class="fas fa-book"></i>
                <span>云笔记</span>
            </a>
            <nav class="top-nav">
                <a href="<?php echo APP_BASE; ?>pages/archive.php" class="nav-link">归档码查询</a>
                <a href="#features" class="nav-link">云笔记介绍</a>
          <a href="<?php echo APP_BASE; ?>pages/admin.php" class="nav-link">管理员入口</a>
            </nav>
        </header>

        <section class="hero" id="start">
            <div class="hero-content">
                <div class="hero-badge">
                    <i class="fas fa-bolt"></i> 无需注册 · 即刻使用 · 免费开源
                </div>
                <h1 class="hero-title">安全、简洁的<br>在线笔记本</h1>
                <p class="hero-subtitle">随时随地记录您的想法，支持 Markdown 实时预览，密码保护确保数据安全。还能用「归档码」把多个笔记本归为一组，一键找回。</p>
                <div class="hero-stats">
                    <div class="stat-item">
                        <span class="stat-num">Markdown</span>
                        <span class="stat-label">实时预览</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-num">密码</span>
                        <span class="stat-label">加密保护</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-num">归档码</span>
                        <span class="stat-label">分组管理</span>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="tabs" role="tablist">
                    <button type="button" class="tab-btn <?php echo $active_tab === 'note' ? 'active' : ''; ?>" data-tab="note">
                        <i class="fas fa-pen-to-square"></i> 进入笔记本
                    </button>
                    <button type="button" class="tab-btn <?php echo $active_tab === 'archive' ? 'active' : ''; ?>" data-tab="archive">
                        <i class="fas fa-key"></i> 归档码查询
                    </button>
                </div>

                <!-- 进入笔记本 -->
                <div class="tab-panel <?php echo $active_tab === 'note' ? 'active' : ''; ?>" data-panel="note">
                    <p class="panel-hint">
                        <i class="fas fa-circle-info"></i>
                        输入笔记本 ID 即可进入；若 ID 不存在，将引导你创建一个新的加密笔记本。
                    </p>
                    <form id="noteForm" action="<?php echo APP_BASE; ?>pages/notebook.php" method="get">
                        <div class="form-group">
                            <label for="noteId" class="form-label">笔记本 ID</label>
                            <div class="input-wrap">
                                <i class="fas fa-hashtag"></i>
                                <input type="text" id="noteId" name="id" class="form-input" placeholder="输入笔记本 ID，新 ID 将创建新笔记本" required>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-full"><i class="fas fa-arrow-right-to-bracket"></i> 进入笔记本</button>
                    </form>
                </div>

                <!-- 归档码查询 -->
                <div class="tab-panel <?php echo $active_tab === 'archive' ? 'active' : ''; ?>" data-panel="archive">
                    <p class="panel-hint">
                        <i class="fas fa-circle-info"></i>
                        归档码是笔记本的「分组标签」。输入归档码，即可列出该组下的所有笔记本，快速找回。
                    </p>
                    <form action="<?php echo APP_BASE; ?>pages/archive.php" method="get">
                        <div class="form-group">
                            <label for="archiveCode" class="form-label">归档码</label>
                            <div class="input-wrap">
                                <i class="fas fa-key"></i>
                                <input type="text" id="archiveCode" name="archive_code" class="form-input" placeholder="输入归档码，查找同组的所有笔记本" value="<?php echo $prefill_archive; ?>" required>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-full"><i class="fas fa-magnifying-glass"></i> 查找笔记本</button>
                    </form>
                </div>
            </div>
        </section>

        <section id="features">
            <div class="section-head">
                <div class="section-tag">Features</div>
                <h2 class="section-title">功能特点</h2>
            </div>
            <div class="features">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-lock"></i>
                    </div>
                    <h3 class="feature-title">安全保密</h3>
                    <p class="feature-text">所有笔记本均采用密码保护，密码经哈希加密存储，确保您的信息安全无忧。</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-code"></i>
                    </div>
                    <h3 class="feature-title">Markdown 支持</h3>
                    <p class="feature-text">完整支持 Markdown 格式与实时预览，让您的笔记排版更美观、阅读更舒适。</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-bolt"></i>
                    </div>
                    <h3 class="feature-title">快速访问</h3>
                    <p class="feature-text">简单的 ID 系统，无需记住复杂网址，随时随地轻松访问您的重要笔记。</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-folder-tree"></i>
                    </div>
                    <h3 class="feature-title">归档码归类</h3>
                    <p class="feature-text">为多个笔记本设置同一归档码，即可将它们归为一组，凭归档码一键查出全部。</p>
                </div>
            </div>
        </section>

        <section id="archive-guide" class="archive-guide">
            <div class="section-tag">Archive Code</div>
            <h2 class="section-title">归档码怎么用？</h2>
            <p>归档码就像给笔记本贴的「分组标签」。给同一批笔记本设置相同的归档码，之后只需记住这一个码，就能找回整组笔记本，再也不怕忘记单个 ID。</p>
            <div class="steps">
                <div class="step">
                    <div class="step-num">1</div>
                    <h4>创建笔记本</h4>
                    <p>在上方「进入笔记本」中输入 ID 并设置密码，创建你的加密笔记本。</p>
                </div>
                <div class="step">
                    <div class="step-num">2</div>
                    <h4>设置归档码</h4>
                    <p>在笔记本的设置中为它填写一个归档码，把相关的多个笔记本用同一个码归为一组。</p>
                </div>
                <div class="step">
                    <div class="step-num">3</div>
                    <h4>凭码找回</h4>
                    <p>在上方「归档码查询」或下方按钮输入归档码，即可列出该组下所有笔记本。</p>
                </div>
            </div>
            <a href="<?php echo APP_BASE; ?>pages/archive.php" class="btn"><i class="fas fa-key"></i> 前往归档码查询</a>
        </section>

        <footer class="footer">
            <p class="footer-text">© <?php echo date('Y'); ?> 云笔记 - 安全、简洁、高效的在线记事工具 - By欲儿</p>
            <div class="footer-links">
                <a href="#" class="footer-link">使用条款</a>
                <a href="#" class="footer-link">隐私政策</a>
                <a href="#" class="footer-link">联系我们</a>
            </div>
        </footer>
    </div>

    <script>
        // 首页标签页切换
        (function () {
            var btns = document.querySelectorAll('.tab-btn');
            var panels = document.querySelectorAll('.tab-panel');
            btns.forEach(function (btn) {
                btn.addEventListener('click', function () {
                    var tab = btn.getAttribute('data-tab');
                    btns.forEach(function (b) { b.classList.remove('active'); });
                    panels.forEach(function (p) { p.classList.remove('active'); });
                    btn.classList.add('active');
                    var panel = document.querySelector('.tab-panel[data-panel="' + tab + '"]');
                    if (panel) {
                        panel.classList.add('active');
                        var input = panel.querySelector('.form-input');
                        if (input) { setTimeout(function () { input.focus(); }, 50); }
                    }
                });
            });
        })();
    </script>
    <script>window.APP_BASE = '<?php echo APP_BASE; ?>';</script>
    <script src="<?php echo APP_BASE; ?>js/main.js"></script>
</body>
</html>
