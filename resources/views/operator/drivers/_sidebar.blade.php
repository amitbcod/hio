<!-- Drivers Sidebar -->
<div style="">
    <h6 style="color:#fff">Driver Management</h6>
    <div style="display:flex;flex-direction:column;gap:0px;">
        <a href="{{ route('operator.drivers.create') }}" style="padding:10px 12px;background:transparent;border-left:0px solid #19b5b5;border-radius:0px;text-decoration:none;font-size:13px;color:#fff;font-weight:600;">+ Add New Driver</a>
        <a href="{{ route('operator.drivers.index') }}" style="padding:10px 12px;background:transparent;border-left:0px solid #ccc;border-radius:0px;text-decoration:none;font-size:13px;color:#fff;font-weight:600;">Manage Drivers</a>
    </div>
</div>

<style>
    .drivers-sidebar a { display:block; }
</style>
