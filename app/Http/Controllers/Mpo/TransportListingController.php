<?php

namespace App\Http\Controllers\Mpo;

use App\Http\Controllers\Controller;
use App\Models\AdminUser;
use App\Models\MpoStaffUser;
use App\Services\MpoStaffPermissionService;
use App\Services\TransportListingEditor;
use App\Services\TransportListingQuery;
use Illuminate\Http\Request;

class TransportListingController extends Controller
{
    public function index(Request $request, TransportListingQuery $listings)
    {
        [$mpo, $staff] = $this->context($request);
        abort_if(! $mpo->country_id, 403, 'The MPO has no assigned country.');

        return view('admin.transports.index', [
            ...$listings->paginate($request, (int) $mpo->country_id),
            'indexRoute' => 'mpo.transport.index',
            'showRoute' => 'mpo.transport.show',
            'isMpo' => true,
            'mpoStaffUser' => $staff,
            'canEdit' => $staff === null || app(MpoStaffPermissionService::class)->can($staff, 'transport', 'update'),
        ]);
    }

    public function show(Request $request, int $transport, TransportListingQuery $listings)
    {
        [$mpo, $staff] = $this->context($request);
        abort_if(! $mpo->country_id, 403, 'The MPO has no assigned country.');

        return view('admin.transports.show', [
            'transport' => $listings->findOrFail($transport, (int) $mpo->country_id),
            'indexRoute' => 'mpo.transport.index',
            'isMpo' => true,
            'mpoStaffUser' => $staff,
            'canEdit' => $staff === null || app(MpoStaffPermissionService::class)->can($staff, 'transport', 'update'),
        ]);
    }

    public function edit(Request $request, int $transport, TransportListingQuery $listings, TransportListingEditor $editor)
    {
        [$mpo, $staff] = $this->context($request);
        abort_if(! $mpo->country_id, 403, 'The MPO has no assigned country.');
        $transport = $listings->findOrFail($transport, (int) $mpo->country_id);

        return view('admin.transports.edit', [
            'transport' => $transport,
            'vehicleNames' => $editor->vehicleNamesFor($transport),
            'indexRoute' => 'mpo.transport.index',
            'showRoute' => 'mpo.transport.show',
            'updateRoute' => 'mpo.transport.update',
            'isMpo' => true,
            'mpoStaffUser' => $staff,
        ]);
    }

    public function update(Request $request, int $transport, TransportListingQuery $listings, TransportListingEditor $editor)
    {
        [$mpo] = $this->context($request);
        abort_if(! $mpo->country_id, 403, 'The MPO has no assigned country.');
        $editor->update($listings->findOrFail($transport, (int) $mpo->country_id), $request);

        return redirect()
            ->route('mpo.transport.show', $transport)
            ->with('success', 'Transport listing updated.');
    }

    private function context(Request $request): array
    {
        $mpo = $request->attributes->get('mpo_admin');
        $staff = $request->attributes->get('mpo_staff_user');
        abort_unless($mpo instanceof AdminUser && $mpo->admin_type === 'mpo', 403);
        abort_unless($staff === null || ($staff instanceof MpoStaffUser && (int) $staff->admin_user_id === (int) $mpo->id), 403);

        return [$mpo, $staff];
    }
}
