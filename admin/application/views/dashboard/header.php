<!-- Dashboard Header -->
<header class="dashboard-header">
    <!-- <nav class="navbar navbar-dark bg-dark">
        <div class="container-fluid">
            <span class="navbar-brand mb-0 h1">
                <i class="fas fa-tachometer-alt"></i> Operator Dashboard
            </span>
            
            <div class="navbar-nav ml-auto">
                <div class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="fas fa-user-circle"></i> <?php echo htmlspecialchars($this->session->userdata('full_name')); ?>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdown">
                        <a class="dropdown-item" href="<?php echo site_url('dashboard/profile'); ?>">
                            <i class="fas fa-user"></i> My Profile
                        </a>
                        <a class="dropdown-item" href="<?php echo site_url('dashboard'); ?>">
                            <i class="fas fa-home"></i> Dashboard
                        </a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="<?php echo site_url('auth/logout'); ?>">
                            <i class="fas fa-sign-out-alt"></i> Logout
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </nav>


    <div class="header-info-row">
        <nav aria-label="breadcrumb" class="breadcrumb-nav">
            <ol class="breadcrumb mb-0" style="font-size: 0.85rem;">
                <li class="breadcrumb-item">
                    <a href="<?php echo site_url('dashboard'); ?>"><i class="fas fa-home"></i> Dashboard</a>
                </li>
                <?php if (isset($current_section) && !empty($current_section)): ?>
                    <li class="breadcrumb-item active" aria-current="page">
                        <?php echo ucfirst(str_replace('_', ' ', $current_section)); ?>
                    </li>
                <?php endif; ?>
            </ol>
        </nav>

    
        <div class="completion-header">
            <div class="completion-info">
                <small class="completion-label">Overall Completion</small>
                <div class="completion-bar">
                    <div class="progress" style="height: 12px;">
                        <div class="progress-bar progress-bar-striped" 
                             role="progressbar" 
                             style="width: <?php echo isset($completion_percentage) ? $completion_percentage : 0; ?>%"
                             aria-valuenow="<?php echo isset($completion_percentage) ? $completion_percentage : 0; ?>" 
                             aria-valuemin="0" 
                             aria-valuemax="100"></div>
                    </div>
                    <span class="completion-percent"><?php echo isset($completion_percentage) ? $completion_percentage : 0; ?>%</span>
                </div>
                <small class="completion-msg">Complete all sections to submit for approval</small>
            </div>
        </div>
    </div> -->
</header>

<style>
.dashboard-header {
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    margin-bottom: 0;
}

.dashboard-header .navbar {
    padding: 0.35rem 0;
}

.dashboard-header .navbar-brand {
    color: white !important;
    font-size: 1.1rem;
    font-weight: bold;
}

.dashboard-header .nav-link {
    color: #adb5bd !important;
    transition: color 0.3s ease;
    padding: 0.25rem 0.5rem !important;
}

.dashboard-header .nav-link:hover {
    color: white !important;
}

.header-info-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 6px 12px;
    background-color: #f8f9fa;
    border-bottom: 1px solid #dee2e6;
}

.breadcrumb-nav {
    flex: 1;
}

.breadcrumb {
    margin: 0;
    padding: 0;
}

.completion-header {
    flex: 0 0 auto;
    margin-left: 20px;
}

.completion-info {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    gap: 4px;
}

.completion-label {
    font-size: 0.75rem;
    font-weight: 600;
    color: #666;
}

.completion-bar {
    display: flex;
    align-items: center;
    gap: 8px;
}

.completion-bar .progress {
    width: 120px;
    margin: 0;
}

.completion-percent {
    font-size: 0.85rem;
    font-weight: bold;
    color: #667eea;
    min-width: 35px;
    text-align: right;
}

.completion-msg {
    font-size: 0.7rem;
    color: #999;
    text-align: right;
}

@media (max-width: 768px) {
    .header-info-row {
        flex-direction: column;
        align-items: flex-start;
    }

    .completion-header {
        margin-left: 0;
        margin-top: 6px;
        width: 100%;
    }

    .completion-info {
        align-items: flex-start;
    }

}
</style>
