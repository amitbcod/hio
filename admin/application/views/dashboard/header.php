<!-- Dashboard Header -->
<header class="dashboard-header">
    <div class="top-header-bar">
        <div class="header-left">
            <a href="<?php echo site_url('dashboard'); ?>">
                <img src="<?php echo base_url('admin/public/images/holidays-io-logo.png'); ?>" alt="HIO Logo" class="header-logo">
            </a>
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
}

.header-left .header-logo {
    height: 50px;
    max-width: 200px;
    object-fit: contain;
}

.header-right {
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

@media (max-width: 768px) {
    .top-header-bar {
        padding: 10px 15px;
    }
    
    .header-left .header-logo {
        height: 40px;
    }
    
    .user-email {
        display: none;
    }
    
    .header-right {
        gap: 10px;
    }
}
</style>
