<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Transport;
use App\Services\TransportListingEditor;
use App\Services\TransportListingQuery;
use Illuminate\Http\Request;

class TransportListingController extends Controller
{
    public function index(Request $request, TransportListingQuery $listings)
    {
        return view('admin.transports.index', [
            ...$listings->paginate($request, includeCountryFilter: true),
            'indexRoute' => 'admin.transport.index',
            'showRoute' => 'admin.transport.show',
            'isMpo' => false,
        ]);
    }

    public function show(int $transport, TransportListingQuery $listings)
    {
        return view('admin.transports.show', [
            'transport' => $listings->findOrFail($transport),
            'indexRoute' => 'admin.transport.index',
            'isMpo' => false,
        ]);
    }

    public function edit(int $transport, TransportListingQuery $listings, TransportListingEditor $editor)
    {
        $transport = $listings->findOrFail($transport);

        return view('admin.transports.edit', [
            'transport' => $transport,
            'vehicleNames' => $editor->vehicleNamesFor($transport),
            'indexRoute' => 'admin.transport.index',
            'showRoute' => 'admin.transport.show',
            'updateRoute' => 'admin.transport.update',
        ]);
    }

    public function update(Request $request, int $transport, TransportListingQuery $listings, TransportListingEditor $editor)
    {
        $editor->update($listings->findOrFail($transport), $request);

        return redirect()
            ->route('admin.transport.show', $transport)
            ->with('success', 'Transport listing updated.');
    }
}
