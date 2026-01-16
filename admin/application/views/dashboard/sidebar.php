<!-- Dashboard Sidebar -->
<aside id="sidebar" class="dashboard-sidebar">
    <div class="sidebar-header">
        <div class="d-flex align-items-center">
            <div style="width:40px;height:40px;background:url(/public/images/logo.png) no-repeat center/contain;margin-right:8px"></div>
            <h6 class="mb-0">Operator Dashboard</h6>
        </div>
    </div>

    <nav class="sidebar-nav">
        <ul class="nav flex-column">
            <li class="sidebar-section-title">PROFILE CREATION</li>

            <?php
            $steps = array(
                'profile' => array('label' => 'Profile', 'number' => 1, 'url' => site_url('operator/profile')),
                'system_process' => array('label' => 'System Processes', 'number' => 2, 'url' => site_url('operator/system_process')),
                'collaboration' => array('label' => 'Collaboration Agreement', 'number' => 3, 'url' => site_url('operator/collaboration')),
                'users' => array('label' => 'Users & Staff', 'number' => 4, 'url' => site_url('operator/users')),
                'accounting' => array('label' => 'Accounting & Payouts', 'number' => 5, 'url' => site_url('operator/accounting')),
                'operations' => array('label' => 'Service Operations', 'number' => 6, 'url' => site_url('operator/operations')),
                'status_review' => array('label' => 'Status Review', 'number' => 7, 'url' => site_url('operator/status_review')),
            );

            $step_keys = array_keys($steps);
            $previous_completed = true; // First step is always enabled

            foreach ($steps as $key => $s) {
                $is_completed = isset($section_status[$key]) && $section_status[$key];
                $is_current = isset($current_section) && $current_section === $key;
                
                // Check if this step should be enabled (previous step must be completed)
                $is_enabled = $previous_completed || $is_current || $is_completed;
                
                $li_classes = 'nav-item step-item';
                if ($is_completed) $li_classes .= ' completed';
                if ($is_current) $li_classes .= ' active';
                if (!$is_enabled) $li_classes .= ' disabled';
            ?>
                <li class="<?php echo $li_classes; ?>">
                    <?php if ($is_enabled): ?>
                        <a class="nav-link" href="<?php echo $s['url']; ?>">
                            <span class="step-number"><?php echo $s['number']; ?></span>
                            <span><?php echo $s['label']; ?></span>
                            <?php if ($is_completed): ?>
                                <span class="badge badge-success badge-pill ml-auto"><i class="fas fa-check"></i></span>
                            <?php endif; ?>
                        </a>
                    <?php else: ?>
                        <a class="nav-link disabled" href="javascript:void(0);" title="Complete previous step first">
                            <span class="step-number"><?php echo $s['number']; ?></span>
                            <span><?php echo $s['label']; ?></span>
                            <i class="fas fa-lock" style="margin-left: auto; opacity: 0.5; font-size: 12px;"></i>
                        </a>
                    <?php endif; ?>
                </li>
            <?php 
                // Update for next iteration: this step must be completed for next to be enabled
                $previous_completed = $is_completed;
            } ?>

            <!-- Additional section items removed to avoid duplication; steps above handle display and enablement -->
        </ul>
    </nav>


</aside>

<style>
.dashboard-sidebar {
    position: fixed;
    left: 0;
    top: 68px; /* match header height (navbar + header-info-row) */
    width: 280px;
    height: calc(100vh - 68px);
    background-color: #2bb4a8; /* teal */
    border-right: 1px solid rgba(0,0,0,0.06);
    overflow-y: auto;
    z-index: 1000;
    padding-bottom: 80px; /* room for footer widget */
    color: white;
    display: block !important;
    visibility: visible !important;
}

.sidebar-header {
    padding: 14px 12px;
    border-bottom: 1px solid rgba(255,255,255,0.06);
    background-color: transparent;
    font-weight: 700;
    color: white;
}

.sidebar-nav {
    padding: 0.25rem 0;
}

.sidebar-nav ul {
    list-style: none;
    margin: 0;
    padding: 0;
}

.sidebar-nav .nav-link {
    color: rgba(255,255,255,0.95);
    padding: 10px 14px;
    border-left: 3px solid transparent;
    display: flex;
    align-items: center;
    justify-content: flex-start;
    transition: all 0.15s ease;
    font-size: 0.95rem;
}

.sidebar-nav .nav-link:hover {
    background-color: rgba(255,255,255,0.06);
    color: #ffffff;
    border-left-color: rgba(255,255,255,0.12);
}

.sidebar-nav .nav-link.active {
    background-color: rgba(0,0,0,0.08);
    color: #fff;
    border-left-color: #ffffff;
    font-weight: 700;
}

.sidebar-nav .nav-link i {
    min-width: 18px;
    margin-right: 10px;
    color: rgba(255,255,255,0.95);
}

.sidebar-nav .nav-link span {
    display: inline-block;
    margin-left: 0;
}

.sidebar-nav .badge {
    font-size: 0.7rem;
    padding: 0.25rem 0.5rem;
}

.sidebar-footer {
    position: absolute;
    bottom: 0;
    width: 100%;
    padding: 12px;
    background-color: transparent;
    border-top: 1px solid rgba(255,255,255,0.04);
}

.sidebar-section-title {
    padding: 10px 14px;
    font-size: 0.75rem;
    color: rgba(255,255,255,0.85);
    font-weight: 700;
    letter-spacing: 0.04em;
}

.step-item .nav-link {
    display: flex;
    align-items: center;
    padding: 8px 14px;
}

.step-number {
    display: inline-block;
    width: 26px;
    height: 26px;
    line-height: 26px;
    text-align: center;
    border-radius: 50%;
    background: rgba(255,255,255,0.12);
    color: #fff;
    font-weight: 700;
    margin-right: 10px;
}

.step-item.active .step-number {
    background: #fff;
    color: #2bb4a8;
}

.step-item.active .nav-link {
    background-color: rgba(0,0,0,0.08);
    font-weight: 700;
}

.nav-link.disabled {
    opacity: 0.55;
    pointer-events: none;
}

.step-item.completed .step-number {
    background: #28a745;
    color: #fff;
}

.step-item.disabled .step-number {
    background: rgba(255,255,255,0.06);
    color: rgba(255,255,255,0.7);
}

.completion-widget h6 {
    font-weight: 700;
    font-size: 0.85rem;
    margin-bottom: 6px;
    color: rgba(255,255,255,0.95);
}

.completion-widget .progress {
    height: 14px;
    background: rgba(255,255,255,0.08);
}

.completion-widget .progress-bar {
    line-height: 14px;
    font-size: 0.8rem;
}

/* Main content should have left margin to accommodate sidebar */
.main-content {
    margin-left: 0;
    padding-top: 6px;
}

@media (max-width: 768px) {
    .dashboard-sidebar {
        position: relative;
        width: 100%;
        height: auto;
        border-right: none;
        border-bottom: 1px solid #dee2e6;
        top: 0;
    }

    .main-content {
        margin-left: 0;
    }

    .sidebar-nav {
        display: flex;
        flex-direction: row;
        padding: 0;
        overflow-x: auto;
    }

    .sidebar-nav .nav-link {
        flex: 1;
        padding: 0.5rem;
        border-left: none;
        border-bottom: 3px solid transparent;
        white-space: nowrap;
    }

    .sidebar-nav .nav-link.active {
        border-left: none;
        border-bottom-color: #007bff;
    }

    .sidebar-footer {
        position: relative;
        width: 100%;
    }
}
</style>
