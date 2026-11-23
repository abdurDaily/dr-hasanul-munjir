<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Faq;
use App\Models\FaqCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FaqController extends Controller
{
    public function index(Request $request): View
    {
        $faqs = Faq::query()->with('category:id,name')
            ->when($request->query('category'), fn ($q, $id) => $q->where('faq_category_id', $id))
            ->ordered()->paginate(30)->withQueryString();

        return view('admin.faqs.index', ['faqs' => $faqs, 'categories' => FaqCategory::query()->orderBy('sort_order')->pluck('name', 'id')]);
    }

    public function create(): View
    {
        return $this->form(new Faq(['is_active' => true, 'sort_order' => Faq::query()->max('sort_order') + 1]));
    }

    public function store(Request $request): RedirectResponse
    {
        Faq::create($this->data($request));

        return redirect()->route('admin.faqs.index')->with('success', 'FAQ added.');
    }

    public function edit(Faq $faq): View
    {
        return $this->form($faq);
    }

    public function update(Request $request, Faq $faq): RedirectResponse
    {
        $faq->update($this->data($request));

        return redirect()->route('admin.faqs.index')->with('success', 'FAQ saved.');
    }

    public function destroy(Faq $faq): RedirectResponse
    {
        $faq->delete();

        return back()->with('success', 'FAQ deleted.');
    }

    private function form(Faq $faq): View
    {
        return view('admin.faqs.form', ['faq' => $faq, 'categories' => FaqCategory::query()->orderBy('sort_order')->pluck('name', 'id')]);
    }

    private function data(Request $request): array
    {
        return $request->validate([
            'question' => ['required', 'string', 'max:255'],
            'answer' => ['required', 'string', 'max:3000'],
            'faq_category_id' => ['nullable', 'exists:faq_categories,id'],
            'sort_order' => ['required', 'integer', 'min:0'],
            'is_active' => ['boolean'],
        ]);
    }
}
