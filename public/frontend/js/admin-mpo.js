(() => {
    const layout = document.querySelector('.admin-mpo-layout');
    if (!layout) return;

    const sidebar = layout.querySelector('.admin-mpo-sidebar');
    const toggle = layout.querySelector('[data-admin-mpo-sidebar-toggle]');
    if (!sidebar || !toggle) return;

    const closeSidebar = () => {
        layout.classList.remove('admin-mpo-sidebar-open');
        toggle.setAttribute('aria-expanded', 'false');
    };

    toggle.addEventListener('click', () => {
        const isOpen = layout.classList.toggle('admin-mpo-sidebar-open');
        toggle.setAttribute('aria-expanded', String(isOpen));
    });

    document.addEventListener('click', (event) => {
        if (
            layout.classList.contains('admin-mpo-sidebar-open')
            && !sidebar.contains(event.target)
            && !toggle.contains(event.target)
        ) {
            closeSidebar();
        }
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') closeSidebar();
    });

    sidebar.addEventListener('click', (event) => {
        if (event.target.closest('a')) closeSidebar();
    });

    window.addEventListener('resize', () => {
        if (window.innerWidth > 960) closeSidebar();
    });
})();
