<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Highlight;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/** "Why choose me" reasons on the homepage. */
class HighlightController extends Controller
{
    public function index(): View
    {
        return view('admin.highlights.index', ['highlights' => Highlight::query()->ordered()->get()]);
    }

    public function create(): View
    {
        return view('admin.highlights.form', ['highlight' => new Highlight(['is_active' => true, 'icon' => 'bi-stars', 'sort_order' => Highlight::query()->max('sort_order') + 1])]);
    }

    public function store(Request $request): RedirectResponse
    {
        Highlight::create($this->data($request));

        return redirect()->route('admin.highlights.index')->with('success', 'Saved.');
    }

    public function edit(Highlight $highlight): View
    {
        return view('admin.highlights.form', compact('highlight'));
    }

    public function update(Request $request, Highlight $highlight): RedirectResponse
    {
        $highlight->update($this->data($request));

        return redirect()->route('admin.highlights.index')->with('success', 'Saved.');
    }

    public function destroy(Highlight $highlight): RedirectResponse
    {
        $highlight->delete();

        return back()->with('success', 'Deleted.');
    }

    private function data(Request $request): array
    {
        return $request->validate([
            'icon' => ['required', Rule::in(config('icons'))],
            'title' => ['required', 'string', 'max:80'],
            'text' => ['required', 'string', 'max:300'],
            'sort_order' => ['required', 'integer', 'min:0'],
            'is_active' => ['boolean'],
        ]);
    }
}
