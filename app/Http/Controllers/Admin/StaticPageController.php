<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StaticPage;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class StaticPageController extends Controller
{
    public function index()
    {
        $pages = StaticPage::orderBy('title')->get();

        return view('admin.static_pages.index', compact('pages'));
    }

    public function create()
    {
        return view('admin.static_pages.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'content_en' => 'nullable|string',
            'content_fr' => 'nullable|string',
            'is_active' => 'nullable',
        ]);

        $data['slug'] = $this->makeUniqueSlug($request->input('slug') ?: $data['title']);
        $data['is_active'] = $request->exists('is_active') ? (bool) $request->boolean('is_active') : true;

        StaticPage::create($data);

        return redirect()->route('admin.static-pages.index')->with('success', 'Page created');
    }

    public function edit(StaticPage $staticPage)
    {
        return view('admin.static_pages.edit', compact('staticPage'));
    }

    public function update(Request $request, StaticPage $staticPage)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'content_en' => 'nullable|string',
            'content_fr' => 'nullable|string',
            'is_active' => 'nullable',
        ]);

        $data['slug'] = $this->makeUniqueSlug($request->input('slug') ?: $data['title'], $staticPage->id);
        $data['is_active'] = $request->exists('is_active') ? (bool) $request->boolean('is_active') : $staticPage->is_active;

        $staticPage->update($data);

        return redirect()->route('admin.static-pages.index')->with('success', 'Page updated');
    }

    public function destroy(StaticPage $staticPage)
    {
        $staticPage->delete();

        return redirect()->route('admin.static-pages.index')->with('success', 'Page deleted');
    }

    protected function makeUniqueSlug(string $value, ?int $ignoreId = null): string
    {
        $base = Str::slug($value) ?: 'page';
        $slug = $base;
        $counter = 1;

        while (StaticPage::where('slug', $slug)->when($ignoreId, function ($query) use ($ignoreId) {
            $query->where('id', '!=', $ignoreId);
        })->exists()) {
            $slug = $base . '-' . $counter++;
        }

        return $slug;
    }
}
