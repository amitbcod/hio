<!-- Dashboard Header -->
<header class="dashboard-header">
    <div class="top-header-bar">
        <div class="header-left">
            <a href="<?php echo site_url('dashboard'); ?>">
                <img src="<?php echo base_url('admin/public/images/holidays-io-logo.png'); ?>" alt="HIO Logo" class="header-logo">
            </a>
        </div>
        
        <!-- Overall Completion Section - Right Side -->
        <div class="header-center">
            <div class="completion-widget">
                <div class="completion-label">
                    <i class="fas fa-chart-pie"></i>
                    <strong>Overall Completion</strong>
                </div>
                <div class="completion-details">
                    <div class="progress" style="width: 200px; height: 20px; background: #e9ecef; border-radius: 10px; margin-right: 10px;">
                        <div class="progress-bar bg-success" 
                             role="progressbar" 
                             style="width: <?php echo isset($completion_percentage) ? $completion_percentage : 0; ?>%; border-radius: 10px; font-size: 0.75rem; font-weight: 600; line-height: 20px;"
                             aria-valuenow="<?php echo isset($completion_percentage) ? $completion_percentage : 0; ?>" 
                             aria-valuemin="0" 
                             aria-valuemax="100">
                            <?php echo isset($completion_percentage) ? $completion_percentage : 0; ?>%
                        </div>
                    </div>
                    <?php 
                    $completed = isset($section_status) ? count(array_filter($section_status)) : 0;
                    $total = isset($section_status) ? count($section_status) : 7;
                    ?>
                    <span class="completion-text">
                        <?php echo $completed; ?>/<?php echo $total; ?>
                    </span>
                </div>
            </div>
        </div>
        
        <div class="header-right">
            <div class="user-info">
                <i class="fas fa-user-circle" style="font-size: 20px; color: #555; margin-right: 8px;"></i>
                <span class="user-email"><?php echo htmlspecialchars($this->session->userdata('email')); ?></span>
            </div>
            <a href="<?php echo site_url('auth/logout'); ?>" class="logout-btn" title="Logout">
                <i class="fas fa-sign-out-alt"></i>
            </a>
        </div>
    </div>
</header>

<style>
.dashboard-header {
    background-color: #f5f5f5;
    border-bottom: 1px solid #e0e0e0;
    box-shadow: 0 2px 4px rgba(0,0,0,0.08);
    position: sticky;
    top: 0;
    z-index: 1000;
}

.top-header-bar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px 30px;
    background-color: #f5f5f5;
    gap: 20px;
}

.header-left {
    flex: 0 0 auto;
}

.header-left .header-logo {
    height: 50px;
    max-width: 200px;
    object-fit: contain;
}

.header-center {
    flex: 1;
    display: flex;
    justify-content: center;
    align-items: center;
}

.completion-widget {
    background: #fff;
    padding: 8px 16px;
    border-radius: 8px;
    border: 1px solid #e0e0e0;
    box-shadow: 0 1px 3px rgba(0,0,0,0.05);
}

.completion-label {
    display: flex;
    align-items: center;
    gap: 6px;
    color: #333;
    font-size: 13px;
    margin-bottom: 6px;
}

.completion-label i {
    color: #2bb4a8;
    font-size: 14px;
}

.completion-details {
    display: flex;
    align-items: center;
    gap: 10px;
}

.completion-text {
    color: #666;
    font-size: 12px;
    white-space: nowrap;
    font-weight: 500;
}

.header-right {
    flex: 0 0 auto;
    display: flex;
    align-items: center;
    gap: 20px;
}

.user-info {
    display: flex;
    align-items: center;
}

.user-email {
    color: #555;
    font-size: 14px;
    font-weight: 400;
}

.logout-btn {
    background-color: #fff;
    border: 1px solid #ddd;
    border-radius: 4px;
    padding: 8px 12px;
    color: #555;
    text-decoration: none;
    transition: all 0.3s;
    display: flex;
    align-items: center;
}

.logout-btn:hover {
    background-color: #dc3545;
    color: white;
    border-color: #dc3545;
}

.logout-btn i {
    font-size: 16px;
}

@media (max-width: 992px) {
    .header-center {
        flex: 0 0 auto;
    }
    
    .completion-label strong {
        display: none;
    }
    
    .completion-label i {
        margin: 0;
    }
}

@media (max-width: 768px) {
    .top-header-bar {
        padding: 10px 15px;
        flex-wrap: wrap;
    }
    
    .header-left .header-logo {
        height: 40px;
    }
    
    .header-center {
        order: 3;
        flex: 1 1 100%;
        margin-top: 10px;
    }
    
    .completion-widget {
        width: 100%;
    }
    
    .completion-details {
        flex-direction: column;
        gap: 5px;
    }
    
    .completion-details .progress {
        width: 100% !important;
    }
    
    .user-email {
        display: none;
    }
    
    .header-right {
        gap: 10px;
    }
}
</style>
