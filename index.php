<?php
require_once __DIR__ . '/includes/bootstrap.php';
$db = quizai_db();
$stats = quizai_site_stats($db);
$highlights = [
    ['title' => 'Multi-modal input', 'text' => 'Drop in PDFs, topics, or prompts and turn them into quiz-ready content.'],
    ['title' => 'AI question flow', 'text' => 'Generate question sets with clean controls and a fast authoring layout.'],
    ['title' => 'Admin oversight', 'text' => 'Manage users, categories, and system settings from one dashboard.']
];

quizai_render_start('Home');
?>
<section class="hero">
    <div class="hero-grid">
        <article class="hero-card">
            <span class="tag"><span class="material-symbols-outlined">bolt</span> Design-to-app conversion</span>
            <h1>Build <span class="gradient-text">quiz workflows</span> that run.</h1>
            <p>QuizAI turns the design system into a working PHP and MySQL website with onboarding, learner flows, and admin tools.</p>
            <div class="cta-row">
                <a class="primary-button" href="signup.php">Get started</a>
                <a class="secondary-button" href="login.php">Login</a>
            </div>
            <div class="footer-space"></div>
            <div class="pill-row">
                <span class="tag">PHP</span>
                <span class="tag">MySQL</span>
                <span class="tag">HTML</span>
                <span class="tag">CSS</span>
                <span class="tag">JS / jQuery</span>
            </div>
        </article>
        <aside class="hero-card hero-aside">
            <div class="preview-card">
                <div class="eyebrow">Live system stats</div>
                <div class="stat-value"><?php echo number_format($stats['users']); ?> users</div>
                <p class="meta">with <?php echo number_format($stats['quizzes']); ?> quizzes and <?php echo number_format($stats['attempts']); ?> attempts.</p>
                <div class="footer-space"></div>
                <div class="score-badge">Runnable local app</div>
            </div>
            <div class="glass-panel">
                <div class="section-title">What this build covers</div>
                <div class="metric-stack">
                    <?php foreach ($highlights as $highlight) : ?>
                        <div>
                            <strong><?php echo quizai_h($highlight['title']); ?></strong>
                            <div class="muted"><?php echo quizai_h($highlight['text']); ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </aside>
    </div>
</section>

<section class="page-wrap">
    <div class="panel-grid">
        <div class="stat-card">
            <div class="eyebrow">Users</div>
            <div class="stat-value"><?php echo number_format($stats['users']); ?></div>
            <div class="stats-subtitle">Registered accounts in the system.</div>
        </div>
        <div class="stat-card">
            <div class="eyebrow">Quizzes</div>
            <div class="stat-value"><?php echo number_format($stats['quizzes']); ?></div>
            <div class="stats-subtitle">Generated or created quiz records.</div>
        </div>
        <div class="stat-card">
            <div class="eyebrow">Attempts</div>
            <div class="stat-value"><?php echo number_format($stats['attempts']); ?></div>
            <div class="stats-subtitle">Learner submissions across quizzes.</div>
        </div>
        <div class="stat-card">
            <div class="eyebrow">Categories</div>
            <div class="stat-value"><?php echo number_format($stats['categories']); ?></div>
            <div class="stats-subtitle">Content groups available for filtering.</div>
        </div>
    </div>
</section>
<?php quizai_render_end(); ?>