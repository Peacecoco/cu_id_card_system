<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Permanent ID Card Generator</title>
    <link rel="stylesheet" href="../assets/css/biometric.css">
</head>

<body>
    <div class="app-shell">
        <aside class="sidebar" aria-label="Sidebar navigation">
            <div class="brand-row">
                <img
                    src="../assets/images/university-logo.png"
                    class="crest"
                    alt="University Logo"
                >
                <div class="brand-copy">
                    <strong>Covenant</strong>
                    <span>University</span>
                </div>
            </div>

            <div class="profile-box">
                <div class="profile-meta">
                    <div class="avatar" aria-label="User avatar">MA</div>
                    <div>
                        <div class="profile-name"><?php echo htmlspecialchars($currentUser['name']); ?></div>
                    </div>
                </div>
            </div>

            <nav class="sidebar-nav" aria-label="Main navigation">
                <?php foreach ($menuGroups as $group): ?>
                    <div class="nav-item <?php echo !empty($group['expanded']) ? 'open' : ''; ?> <?php echo !empty($group['active']) ? 'active' : ''; ?>">
                        <?php if (!empty($group['href'])): ?>
                            <a class="nav-label" href="<?php echo htmlspecialchars($group['href']); ?>" aria-label="<?php echo htmlspecialchars($group['label']); ?>" data-nav-toggle>
                                <span><?php echo htmlspecialchars($group['label']); ?></span>
                                <span class="caret">▾</span>
                            </a>
                        <?php else: ?>
                            <div class="nav-label" tabindex="0" aria-label="<?php echo htmlspecialchars($group['label']); ?>" data-nav-toggle>
                                <span><?php echo htmlspecialchars($group['label']); ?></span>
                                <span class="caret">▾</span>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($group['items'])): ?>
                            <div class="nav-submenu">
                                <?php foreach ($group['items'] as $item): ?>
                                    <a class="nav-subitem <?php echo !empty($item['active']) ? 'active' : ''; ?>" href="<?php echo htmlspecialchars($item['href'] ?? '#'); ?>"><?php echo htmlspecialchars($item['label']); ?></a>
                                    <?php if (!empty($item['children'])): ?>
                                        <?php foreach ($item['children'] as $child): ?>
                                            <a class="nav-subitem <?php echo !empty($child['active']) ? 'active' : ''; ?>" href="#"><?php echo htmlspecialchars($child['label']); ?></a>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </nav>
        </aside>

        <main class="content">
            <header class="topbar" aria-label="Primary header">
                
                <div class="user-panel" aria-label="User profile">
                    <div class="user-name">
                        <strong><?php echo htmlspecialchars($currentUser['name']); ?></strong>
                        <small><?php echo htmlspecialchars($currentUser['role']); ?></small>
                    </div>
                    <div class="user-icon" aria-hidden="true">◉</div>
                </div>
            </header>

            <?php if ($batchPrintMessage !== ''): ?>
                <script>window.alert(<?php echo json_encode($batchPrintMessage); ?>);</script>
            <?php endif; ?>
            <?php if ($batchPrintError !== ''): ?>
                <div class="status-message error"><?php echo htmlspecialchars($batchPrintError); ?></div>
            <?php endif; ?>

