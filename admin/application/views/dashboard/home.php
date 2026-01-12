<div class="container-fluid dashboard-container">
    <!-- Main Content -->
    <div class="main-content">
        <div class="container-fluid p-0">
            <!-- Welcome Banner -->
            <div class="row mb-1">
                <div class="col-12">
                    <div class="alert alert-info alert-dismissible fade show mb-1" role="alert" style="padding: 8px 10px;">
                        <h5 class="alert-heading mb-0" style="font-size: 0.85rem;">Welcome, <?php echo htmlspecialchars($operator_name ?? 'Operator'); ?>!</h5>
                        <p style="margin: 0; font-size: 0.8rem; margin-top: 4px;">Complete all sections below to activate your operator account and start managing your business.</p>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close" style="padding: 0;">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Stats Row -->
            <div class="row mb-2">
                <!-- Overall Completion Stat -->
                <div class="col-md-6 col-lg-4 mb-2">
                    <div class="card stat-card">
                        <div class="card-body">
                            <h6 class="card-title text-muted">Overall Completion</h6>
                            <div class="progress" style="height: 20px;">
                                <div class="progress-bar bg-success" role="progressbar" 
                                     style="width: <?php echo isset($completion_percentage) ? $completion_percentage : 0; ?>%;" 
                                     aria-valuenow="<?php echo isset($completion_percentage) ? $completion_percentage : 0; ?>" 
                                     aria-valuemin="0" aria-valuemax="100">
                                    <?php echo isset($completion_percentage) ? $completion_percentage : 0; ?>%
                                </div>
                            </div>
                            <small class="text-muted d-block mt-2">
                                <i class="fas fa-check-circle"></i>
                                <?php 
                                    $completed_count = 0;
                                    $total_steps = 8;
                                    if (isset($section_status)) {
                                        $completed_count = count(array_filter($section_status));
                                    }
                                    echo $completed_count . ' of ' . $total_steps . ' sections completed';
                                ?>
                            </small>
                        </div>
                    </div>
                </div>

                <!-- Account Status Stat -->
                <div class="col-md-6 col-lg-4 mb-2">
                    <div class="card stat-card">
                        <div class="card-body">
                            <h6 class="card-title text-muted">Account Status</h6>
                            <h4 class="card-text">
                                <span class="badge badge-<?php echo ($account_status === 'active') ? 'success' : (($account_status === 'pending') ? 'warning' : 'danger'); ?>">
                                    <?php echo ucfirst($account_status ?? 'pending'); ?>
                                </span>
                            </h4>
                            <small class="text-muted d-block mt-2">
                                <i class="fas fa-info-circle"></i>
                                <?php 
                                    if ($account_status === 'active') {
                                        echo 'Your account is active and operational';
                                    } elseif ($account_status === 'pending') {
                                        echo 'Awaiting approval. Complete all sections first.';
                                    } else {
                                        echo 'Account is currently suspended';
                                    }
                                ?>
                            </small>
                        </div>
                    </div>
                </div>

                <!-- Last Updated Stat -->
                <div class="col-md-6 col-lg-4 mb-2">
                    <div class="card stat-card">
                        <div class="card-body">
                            <h6 class="card-title text-muted">Last Updated</h6>
                            <h5 class="card-text">
                                <?php echo isset($last_updated) ? date('M d, Y', strtotime($last_updated)) : 'Never'; ?>
                            </h5>
                            <small class="text-muted d-block mt-2">
                                <i class="fas fa-clock"></i>
                                <?php 
                                    if (isset($last_updated)) {
                                        $diff = strtotime('now') - strtotime($last_updated);
                                        if ($diff < 60) {
                                            echo 'Just now';
                                        } elseif ($diff < 3600) {
                                            echo floor($diff / 60) . ' minutes ago';
                                        } elseif ($diff < 86400) {
                                            echo floor($diff / 3600) . ' hours ago';
                                        } else {
                                            echo floor($diff / 86400) . ' days ago';
                                        }
                                    }
                                ?>
                            </small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sections Grid -->
            <div class="row">
                <div class="col-12">
                    <h5 class="mb-1" style="font-size: 0.85rem;">Dashboard Sections</h5>
                </div>
            </div>

            <div class="row mb-2">
                <!-- Profile Section -->
                <div class="col-md-6 col-lg-4 mb-2">
                    <div class="card section-card <?php echo (isset($section_status['profile']) && $section_status['profile']) ? 'completed' : ''; ?>">
                        <div class="card-body">
                            <div class="section-icon">
                                <i class="fas fa-id-card"></i>
                            </div>
                            <h5 class="card-title mt-1">Profile</h5>
                            <p class="card-text small text-muted">Business profile information and contact details</p>
                            <?php if (isset($section_status['profile']) && $section_status['profile']): ?>
                                <span class="badge badge-success"><i class="fas fa-check"></i> Complete</span>
                            <?php else: ?>
                                <span class="badge badge-warning"><i class="fas fa-exclamation-circle"></i> Required</span>
                            <?php endif; ?>
                            <a href="<?php echo site_url('dashboard/profile'); ?>" class="btn btn-sm btn-outline-primary mt-1 w-100">
                                Edit <i class="fas fa-arrow-right ml-2"></i>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Legal & Compliance Section -->
                <div class="col-md-6 col-lg-4 mb-2">
                    <div class="card section-card <?php echo (isset($section_status['legal']) && $section_status['legal']) ? 'completed' : ''; ?>">
                        <div class="card-body">
                            <div class="section-icon">
                                <i class="fas fa-file-contract"></i>
                            </div>
                            <h5 class="card-title mt-1">Legal & Compliance</h5>
                            <p class="card-text small text-muted">Licenses, certifications, and legal documents</p>
                            <?php if (isset($section_status['legal']) && $section_status['legal']): ?>
                                <span class="badge badge-success"><i class="fas fa-check"></i> Complete</span>
                            <?php else: ?>
                                <span class="badge badge-warning"><i class="fas fa-exclamation-circle"></i> Required</span>
                            <?php endif; ?>
                            <a href="<?php echo site_url('dashboard/legal'); ?>" class="btn btn-sm btn-outline-primary mt-1 w-100">
                                Edit <i class="fas fa-arrow-right ml-2"></i>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- System Processes Section -->
                <div class="col-md-6 col-lg-4 mb-2">
                    <div class="card section-card <?php echo (isset($section_status['system_process']) && $section_status['system_process']) ? 'completed' : ''; ?>">
                        <div class="card-body">
                            <div class="section-icon">
                                <i class="fas fa-cogs"></i>
                            </div>
                            <h5 class="card-title mt-1">System Processes</h5>
                            <p class="card-text small text-muted">Payment gateway and system configuration</p>
                            <?php if (isset($section_status['system_process']) && $section_status['system_process']): ?>
                                <span class="badge badge-success"><i class="fas fa-check"></i> Complete</span>
                            <?php else: ?>
                                <span class="badge badge-warning"><i class="fas fa-exclamation-circle"></i> Recommended</span>
                            <?php endif; ?>
                            <a href="<?php echo site_url('dashboard/system_process'); ?>" class="btn btn-sm btn-outline-primary mt-1 w-100">
                                Edit <i class="fas fa-arrow-right ml-2"></i>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Collaboration Agreement Section -->
                <div class="col-md-6 col-lg-4 mb-2">
                    <div class="card section-card <?php echo (isset($section_status['collaboration']) && $section_status['collaboration']) ? 'completed' : ''; ?>">
                        <div class="card-body">
                            <div class="section-icon">
                                <i class="fas fa-handshake"></i>
                            </div>
                            <h5 class="card-title mt-1">Collaboration Agreement</h5>
                            <p class="card-text small text-muted">Review and sign collaboration terms</p>
                            <?php if (isset($section_status['collaboration']) && $section_status['collaboration']): ?>
                                <span class="badge badge-success"><i class="fas fa-check"></i> Complete</span>
                            <?php else: ?>
                                <span class="badge badge-warning"><i class="fas fa-exclamation-circle"></i> Recommended</span>
                            <?php endif; ?>
                            <a href="<?php echo site_url('dashboard/collaboration'); ?>" class="btn btn-sm btn-outline-primary mt-1 w-100">
                                Edit <i class="fas fa-arrow-right ml-2"></i>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Users & Staff Section -->
                <div class="col-md-6 col-lg-4 mb-2">
                    <div class="card section-card <?php echo (isset($section_status['users']) && $section_status['users']) ? 'completed' : ''; ?>">
                        <div class="card-body">
                            <div class="section-icon">
                                <i class="fas fa-users"></i>
                            </div>
                            <h5 class="card-title mt-1">Users & Staff</h5>
                            <p class="card-text small text-muted">Manage team members and permissions</p>
                            <?php if (isset($section_status['users']) && $section_status['users']): ?>
                                <span class="badge badge-success"><i class="fas fa-check"></i> Complete</span>
                            <?php else: ?>
                                <span class="badge badge-warning"><i class="fas fa-exclamation-circle"></i> Optional</span>
                            <?php endif; ?>
                            <a href="<?php echo site_url('dashboard/users'); ?>" class="btn btn-sm btn-outline-primary mt-1 w-100">
                                Edit <i class="fas fa-arrow-right ml-2"></i>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Accounting & Payouts Section -->
                <div class="col-md-6 col-lg-4 mb-2">
                    <div class="card section-card <?php echo (isset($section_status['accounting']) && $section_status['accounting']) ? 'completed' : ''; ?>">
                        <div class="card-body">
                            <div class="section-icon">
                                <i class="fas fa-calculator"></i>
                            </div>
                            <h5 class="card-title mt-1">Accounting & Payouts</h5>
                            <p class="card-text small text-muted">Bank details and payout management</p>
                            <?php if (isset($section_status['accounting']) && $section_status['accounting']): ?>
                                <span class="badge badge-success"><i class="fas fa-check"></i> Complete</span>
                            <?php else: ?>
                                <span class="badge badge-warning"><i class="fas fa-exclamation-circle"></i> Required</span>
                            <?php endif; ?>
                            <a href="<?php echo site_url('dashboard/accounting'); ?>" class="btn btn-sm btn-outline-primary mt-1 w-100">
                                Edit <i class="fas fa-arrow-right ml-2"></i>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Service Operations Section -->
                <div class="col-md-6 col-lg-4 mb-2">
                    <div class="card section-card <?php echo (isset($section_status['operations']) && $section_status['operations']) ? 'completed' : ''; ?>">
                        <div class="card-body">
                            <div class="section-icon">
                                <i class="fas fa-tasks"></i>
                            </div>
                            <h5 class="card-title mt-1">Service Operations</h5>
                            <p class="card-text small text-muted">Operating hours and service details</p>
                            <?php if (isset($section_status['operations']) && $section_status['operations']): ?>
                                <span class="badge badge-success"><i class="fas fa-check"></i> Complete</span>
                            <?php else: ?>
                                <span class="badge badge-warning"><i class="fas fa-exclamation-circle"></i> Recommended</span>
                            <?php endif; ?>
                            <a href="<?php echo site_url('dashboard/operations'); ?>" class="btn btn-sm btn-outline-primary mt-1 w-100">
                                Edit <i class="fas fa-arrow-right ml-2"></i>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Status Review Section -->
                <div class="col-md-6 col-lg-4 mb-2">
                    <div class="card section-card read-only">
                        <div class="card-body">
                            <div class="section-icon">
                                <i class="fas fa-check-circle"></i>
                            </div>
                            <h5 class="card-title mt-1">Status Review</h5>
                            <p class="card-text small text-muted">Account status and admin notes</p>
                            <span class="badge badge-info"><i class="fas fa-eye"></i> Read-only</span>
                            <a href="<?php echo site_url('dashboard/status_review'); ?>" class="btn btn-sm btn-outline-primary mt-1 w-100">
                                View <i class="fas fa-arrow-right ml-2"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Next Steps -->
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header bg-light">
                            <h6 class="mb-0">Next Steps</h6>
                        </div>
                        <div class="card-body">
                            <ol class="mb-0">
                                <li>Complete your <strong>Profile</strong> section with business information</li>
                                <li>Upload required documents in <strong>Legal & Compliance</strong> section</li>
                                <li>Configure your <strong>System Processes</strong> and payment gateway</li>
                                <li>Review and sign the <strong>Collaboration Agreement</strong></li>
                                <li>Set up <strong>Accounting</strong> details for payouts</li>
                                <li>Configure <strong>Service Operations</strong> hours and services</li>
                                <li>Review your <strong>Status</strong> and submit for approval</li>
                            </ol>
                            <p class="text-muted mt-3">
                                <i class="fas fa-info-circle"></i>
                                Once all required sections are complete, your account will be submitted for admin review and approval.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.dashboard-container {
    padding-left: 0;
    padding-right: 0;
    margin-top: 0;
}

.main-content {
    margin-left: 240px; /* space for fixed sidebar + gutter */
    padding: 12px 20px;
}

.stat-card {
    border: none;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.stat-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.15);
}

.stat-card .completion-percent {
    color: #007bff;
    font-weight: bold;
}

.section-card {
    border: none;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    transition: all 0.3s ease;
    position: relative;
}

.section-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 6px 12px rgba(0,0,0,0.15);
}

.section-card.completed {
    border-left: 4px solid #28a745;
    background-color: #f8fff9;
}

.section-card.read-only {
    border-left: 4px solid #17a2b8;
    background-color: #f8fbfc;
}

.section-icon {
    font-size: 2.5rem;
    color: #007bff;
    opacity: 0.7;
}

.section-card.completed .section-icon {
    color: #28a745;
}

.section-card.read-only .section-icon {
    color: #17a2b8;
}

@media (max-width: 768px) {
    .main-content {
        margin-left: 0;
        padding: 10px;
    }

    .section-card {
        margin-bottom: 15px;
    }
}
</style>
