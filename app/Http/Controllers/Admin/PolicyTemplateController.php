<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PolicyTemplate;
use Illuminate\Support\Str;

class PolicyTemplateController extends Controller
{
    public function index(Request $request)
    {
        $service = $request->get('service', 'accommodation');
        $templates = PolicyTemplate::where('service_type', $service)->orderBy('policy_type')->orderBy('title')->get();
        return view('admin.policy_templates.index', compact('templates', 'service'));
    }

    public function create(Request $request)
    {
        $service = $request->get('service', 'accommodation');
        return view('admin.policy_templates.create', compact('service'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'service_type' => 'required|in:accommodation,activity',
            'policy_type' => 'required|string|max:255',
            'content' => 'nullable|string',
            'is_active' => 'nullable',
        ]);

        // ensure content and is_active captured correctly
        $data['content'] = $request->input('content');
        $data['is_active'] = $request->has('is_active');
        $data['created_by'] = auth()->id() ?? null;
        $data['title'] = $data['policy_type'];

        // generate unique slug
        $slugBase = Str::slug($data['title']);
        $slug = $slugBase;
        $i = 1;
        while (PolicyTemplate::where('slug', $slug)->exists()) {
            $slug = $slugBase . '-' . $i++;
        }
        $data['slug'] = $slug;

        PolicyTemplate::create($data);

        return redirect()->route('admin.policy-templates.index', ['service' => $data['service_type']])->with('success', 'Template created');
    }

    public function edit(PolicyTemplate $policyTemplate)
    {
        $template = $policyTemplate;
        return view('admin.policy_templates.edit', compact('template'));
    }

    public function update(Request $request, PolicyTemplate $policyTemplate)
    {
        $data = $request->validate([
            'service_type' => 'required|in:accommodation,activity',
            'policy_type' => 'required|string|max:255',
            'content' => 'nullable|string',
            'is_active' => 'nullable',
        ]);

        // capture content and checkbox explicitly
        $data['content'] = $request->input('content');
        $data['is_active'] = $request->has('is_active');
        $data['title'] = $data['policy_type'];

        // generate unique slug (avoid collision with other records)
        $slugBase = Str::slug($data['title']);
        $slug = $slugBase;
        $i = 1;
        while (PolicyTemplate::where('slug', $slug)->where('id', '!=', $policyTemplate->id)->exists()) {
            $slug = $slugBase . '-' . $i++;
        }
        $data['slug'] = $slug;

        $policyTemplate->update($data);

        return redirect()->route('admin.policy-templates.index', ['service' => $data['service_type']])->with('success', 'Template updated');
    }

    public function destroy(PolicyTemplate $policyTemplate)
    {
        $service = $policyTemplate->service_type;
        $policyTemplate->delete();
        return redirect()->route('admin.policy-templates.index', ['service' => $service])->with('success', 'Template deleted');
    }
}
