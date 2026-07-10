<!-- Drivers Sidebar -->
<div style="background:#fff;border-radius:12px;padding:16px;box-shadow:0 2px 8px rgba(0,0,0,0.06);">
    <h6 style="font-weight:700;margin:0 0 12px 0;font-size:14px;color:#333;">Driver Management</h6>
    <div style="display:flex;flex-direction:column;gap:8px;">
        <a href="{{ route('operator.drivers.create') }}" style="padding:10px 12px;background:#f5f5f5;border-left:4px solid #19b5b5;border-radius:4px;text-decoration:none;font-size:13px;color:#333;font-weight:600;">+ Add New Driver</a>
        <a href="{{ route('operator.drivers.index') }}" style="padding:10px 12px;background:#fff;border-left:4px solid #ccc;border-radius:4px;text-decoration:none;font-size:13px;color:#666;font-weight:600;">Manage Drivers</a>
    </div>
</div>

<style>
    .drivers-sidebar a { display:block; }
</style>
