        </div><!-- End main content wrapper -->
    </div><!-- End dashboard container -->

    <!-- Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .dashboard-container {
            position: relative;
            min-height: calc(100vh - 68px);
            padding-left: 280px; /* width of sidebar */
        }

        .main-content {
            flex: 1;
            padding: 12px 20px;
            background-color: #f8f9fa;
        }

        .card {
            border: 1px solid #e0e0e0;
            border-radius: 4px;
            box-shadow: 0 1px 2px rgba(0,0,0,0.05);
            transition: all 0.3s ease;
            margin-bottom: 4px;
        }

        .card:hover {
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            transform: translateY(-2px);
        }

        .stat-card {
            background: white;
            border-left: 4px solid #667eea;
            padding: 0;
        }

        .stat-card .card-body {
            padding: 8px;
        }

        .stat-card .completion-percent {
            color: #667eea;
            font-size: 2rem;
            font-weight: bold;
        }

        .progress {
            background-color: #e0e0e0;
            border-radius: 4px;
            height: 16px !important;
        }

        .progress-bar {
            background-color: #667eea;
        }

        .section-card {
            background: white;
            text-align: center;
            border-top: 3px solid #e0e0e0;
            transition: border-color 0.3s ease;
            padding: 0;
        }

        .section-card .card-body {
            padding: 8px;
        }

        .section-card.completed {
            border-top-color: #28a745;
            background-color: #f8fff9;
        }

        .section-icon {
            font-size: 1.5rem;
            color: #667eea;
            margin-bottom: 2px;
        }

        .section-card.completed .section-icon {
            color: #28a745;
        }

        .section-card .card-title {
            color: #333;
            font-weight: 600;
            font-size: 0.8rem;
            margin-bottom: 2px;
        }

        .section-card .btn {
            text-transform: uppercase;
            font-size: 0.7rem;
            font-weight: 600;
            letter-spacing: 0.2px;
            padding: 0.2rem 0.4rem;
            margin-top: 4px !important;
        }

        .alert {
            border-radius: 4px;
            border: none;
            padding: 6px 8px;
            margin-bottom: 4px;
        }

        .alert-info {
            background-color: #e7f3ff;
            color: #004085;
        }

        .alert-info .alert-heading {
            color: #003366;
            font-weight: 600;
            font-size: 0.8rem;
        }

        .badge {
            padding: 0.3rem 0.5rem;
            font-size: 0.7rem;
            font-weight: 600;
            letter-spacing: 0.2px;
        }

        .badge-success {
            background-color: #28a745;
        }

        .badge-warning {
            background-color: #ffc107;
            color: #333;
        }

        .badge-danger {
            background-color: #dc3545;
        }

        .btn-outline-primary {
            color: #667eea;
            border-color: #667eea;
        }

        .btn-outline-primary:hover {
            background-color: #667eea;
            border-color: #667eea;
            color: white;
        }

        .card-header {
            padding: 6px 8px;
        }

        .card-body {
            padding: 8px;
        }

        @media (max-width: 768px) {
            .dashboard-container {
                flex-direction: column;
            }

            .main-content {
                padding: 10px 15px;
            }

            .section-card {
                margin-bottom: 8px;
            }
        }
    </style>

    <script>
        // Dashboard interactivity
        document.addEventListener('DOMContentLoaded', function() {
            // Collapse/expand sidebar on mobile
            const sidebarToggle = document.getElementById('sidebar-toggle');
            const sidebar = document.getElementById('sidebar');
            if (sidebarToggle && sidebar) {
                sidebarToggle.addEventListener('click', function() {
                    sidebar.classList.toggle('show');
                });
            }

            // Active menu highlighting
            const currentPath = window.location.pathname;
            const navLinks = document.querySelectorAll('.sidebar-nav a');
            navLinks.forEach(link => {
                if (link.href.includes(currentPath.split('/').pop())) {
                    link.classList.add('active');
                }
            });
        });
    </script>
</body>
</html>
