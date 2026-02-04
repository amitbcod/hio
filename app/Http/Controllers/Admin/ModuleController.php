<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Module;

class ModuleController extends Controller
{
    public function index()
    {
        if (!session('admin_id')) return redirect()->route('admin.login');
        $modules = Module::orderBy('name')->get();
        return view('admin.modules.index', compact('modules'));
    }

    public function create()
    {
        if (!session('admin_id')) return redirect()->route('admin.login');
        return view('admin.modules.create');
    }

    public function store(Request $request)
    {
        if (!session('admin_id')) return redirect()->route('admin.login');

        $request->validate([
            'name' => 'required|string|max:191',
            'slug' => 'required|string|max:191|alpha_dash|unique:modules,slug',
            'description' => 'nullable|string',
        ]);

        Module::create([
            'name' => $request->name,
            'slug' => $request->slug,
            'description' => $request->description ?: null,
        ]);

        return redirect()->route('admin.modules.index')->with('success', 'Module created.');
    }

    public function edit(Module $module)
    {
        if (!session('admin_id')) return redirect()->route('admin.login');
        return view('admin.modules.edit', compact('module'));
    }

    public function update(Request $request, Module $module)
    {
        if (!session('admin_id')) return redirect()->route('admin.login');

        $request->validate([
            'name' => 'required|string|max:191',
            'slug' => 'required|string|max:191|alpha_dash|unique:modules,slug,' . $module->id,
            'description' => 'nullable|string',
        ]);

        $module->name = $request->name;
        $module->slug = $request->slug;
        $module->description = $request->description ?: null;
        $module->save();

        return redirect()->route('admin.modules.index')->with('success', 'Module updated.');
    }

    public function destroy(Module $module)
    {
        if (!session('admin_id')) return redirect()->route('admin.login');

        $module->delete();
        return redirect()->route('admin.modules.index')->with('success', 'Module deleted.');
    }
}
