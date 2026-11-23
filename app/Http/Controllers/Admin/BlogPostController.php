<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\SavesImages;
use App\Http\Controllers\Controller;
use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\BlogTag;
use App\Services\HtmlCleaner;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class BlogPostController extends Controller
{
    use SavesImages;

    private const IMAGES = ['image' => ['blog', 'blog'], 'og_image' => ['blog/og', 'og']];

    public function index(Request $request): View
    {
        $posts = BlogPost::query()
            ->with(['category:id,name', 'author:id,name'])
            ->when($request->query('q'), fn ($q, $term) => $q->where('title', 'like', "%{$term}%"))
            ->when($request->query('status'), fn ($q, $s) => $q->where('status', $s))
            ->when($request->query('category'), fn ($q, $id) => $q->where('blog_category_id', $id))
            ->latest('updated_at')->paginate(20)->withQueryString();

        return view('admin.posts.index', ['posts' => $posts, 'categories' => BlogCategory::query()->orderBy('name')->pluck('name', 'id')]);
    }

    public function create(): View
    {
        return $this->form(new BlogPost(['status' => 'draft']));
    }

    public function store(Request $request, HtmlCleaner $cleaner): RedirectResponse
    {
        $post = DB::transaction(function () use ($request, $cleaner) {
            $post = BlogPost::create([...$this->data($request, $cleaner), 'author_id' => $request->user()->id]);
            $this->syncTags($post, $request->input('tags'));

            return $post;
        });
        Cache::forget('sitemap.xml');

        return redirect()->route('admin.posts.edit', $post)->with('success', 'Post created.');
    }

    public function edit(BlogPost $post): View
    {
        return $this->form($post->load('tags'));
    }

    public function update(Request $request, BlogPost $post, HtmlCleaner $cleaner): RedirectResponse
    {
        DB::transaction(function () use ($request, $post, $cleaner) {
            $post->update($this->data($request, $cleaner, $post));
            $this->syncTags($post, $request->input('tags'));
        });
        Cache::forget('sitemap.xml');

        return back()->with('success', 'Post saved.');
    }

    public function destroy(BlogPost $post): RedirectResponse
    {
        $post->delete();
        Cache::forget('sitemap.xml');

        return redirect()->route('admin.posts.index')->with('success', 'Post moved to trash.');
    }

    private function form(BlogPost $post): View
    {
        return view('admin.posts.form', [
            'post' => $post,
            'categories' => BlogCategory::query()->orderBy('name')->pluck('name', 'id'),
            'allTags' => BlogTag::query()->orderBy('name')->pluck('name'),
        ]);
    }

    private function data(Request $request, HtmlCleaner $cleaner, ?BlogPost $post = null): array
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:200'],
            'slug' => ['nullable', 'alpha_dash', 'max:200', Rule::unique('blog_posts')->ignore($post)],
            'blog_category_id' => ['nullable', 'exists:blog_categories,id'],
            'excerpt' => ['nullable', 'string', 'max:300'],
            'body' => ['nullable', 'string', 'max:200000'],
            'image' => self::imageRule(),
            'image_alt' => ['nullable', 'string', 'max:255'],
            'status' => ['required', Rule::in(array_keys(BlogPost::STATUSES))],
            'published_at' => ['nullable', 'date'],
            'is_featured' => ['boolean'],
            'meta_title' => ['nullable', 'string', 'max:70'],
            'meta_description' => ['nullable', 'string', 'max:320'],
            'og_image' => self::imageRule(),
            'tags' => ['nullable', 'string', 'max:500'],
        ]);
        unset($data['tags']);
        $data['body'] = $cleaner->clean($data['body'] ?? null);

        if ($data['status'] === 'published' && blank(strip_tags((string) $data['body']))) {
            throw ValidationException::withMessages(['body' => 'A published post needs some content.']);
        }
        // Publishing without a date publishes now; a future date schedules the post.
        if ($data['status'] === 'published' && empty($data['published_at'])) {
            $data['published_at'] = $post?->published_at ?? now();
        }

        return $this->withImages($request, $data, self::IMAGES, $post);
    }

    private function syncTags(BlogPost $post, ?string $input): void
    {
        $ids = collect(explode(',', (string) $input))
            ->map(fn ($t) => trim($t))->filter()->unique(fn ($t) => Str::lower($t))->take(15)
            ->map(fn ($name) => BlogTag::query()->firstOrCreate(['slug' => Str::slug($name)], ['name' => $name])->id);

        $post->tags()->sync($ids->all());
    }
}
