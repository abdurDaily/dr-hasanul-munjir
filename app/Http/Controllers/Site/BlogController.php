<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\BlogTag;
use App\Services\Seo;
use Illuminate\Support\Str;
use Illuminate\View\View;

class BlogController extends Controller
{
    public function index(): View
    {
        return $this->listing(null, null);
    }

    public function category(string $slug): View
    {
        return $this->listing(BlogCategory::query()->where('slug', $slug)->firstOrFail(), null);
    }

    public function tag(string $slug): View
    {
        return $this->listing(null, BlogTag::query()->where('slug', $slug)->firstOrFail());
    }

    public function show(string $slug, Seo $seo): View
    {
        $post = BlogPost::query()->published()->where('slug', $slug)->with(['category', 'tags', 'author:id,name'])->firstOrFail();

        $related = BlogPost::query()->published()->whereKeyNot($post->id)
            ->with('category:id,name,slug')
            ->orderByRaw('blog_category_id <=> ? desc', [$post->blog_category_id])
            ->latest('published_at')->limit(3)->get();

        return view('site.blog.show', [
            'post' => $post,
            'related' => $related,
            'seo' => [
                'title' => ($post->meta_title ?: $post->title).' | '.setting('clinic_name'),
                'description' => $post->meta_description ?: ($post->excerpt ?: Str::limit(strip_tags((string) $post->body), 155)),
                'type' => 'article',
                'image' => $post->og_image ? image_url($post->og_image, 'og') : image_url($post->image, 'blog'),
                'image_alt' => $post->image_alt,
                'published_time' => $post->published_at->toIso8601String(),
                'schema' => [
                    $seo->articleSchema($post),
                    $seo->breadcrumbs([['Home', route('home')], ['Blog', route('blog.index')], [$post->title, route('blog.show', $post->slug)]]),
                ],
            ],
        ]);
    }

    private function listing(?BlogCategory $category, ?BlogTag $tag): View
    {
        $posts = BlogPost::query()->published()
            ->with('category:id,name,slug')
            ->when($category, fn ($q) => $q->where('blog_category_id', $category->id))
            ->when($tag, fn ($q) => $q->whereHas('tags', fn ($t) => $t->whereKey($tag->id)))
            ->latest('published_at')
            ->paginate(9);

        $categories = BlogCategory::query()
            ->whereHas('posts', fn ($q) => $q->published())
            ->orderBy('name')->get(['id', 'name', 'slug']);

        $heading = $category?->name ?? ($tag ? "#{$tag->name}" : null);

        return view('site.blog.index', [
            'posts' => $posts,
            'categories' => $categories,
            'activeCategory' => $category,
            'heading' => $heading,
            'seo' => [
                'title' => ($heading ? "{$heading} — Dental Health Blog" : 'Dental Health Blog').' | '.setting('clinic_name'),
                'description' => $category?->description ?: 'Practical advice on oral health, treatments and keeping your smile healthy from '.setting('clinic_name').'.',
                'canonical' => $posts->currentPage() > 1 ? $posts->url($posts->currentPage()) : url()->current(),
                'noindex' => $tag !== null,
            ],
        ]);
    }
}
