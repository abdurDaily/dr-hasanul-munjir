<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\Treatment;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;

class SitemapController extends Controller
{
    public function sitemap(): Response
    {
        $xml = Cache::remember('sitemap.xml', now()->addHour(), function () {
            $urls = [
                [route('home'), now()],
                [route('treatments.index'), Treatment::query()->max('updated_at')],
                [route('blog.index'), BlogPost::query()->published()->max('updated_at')],
            ];
            foreach (Treatment::query()->active()->ordered()->get(['slug', 'updated_at']) as $t) {
                $urls[] = [route('treatments.show', $t->slug), $t->updated_at];
            }
            foreach (BlogCategory::query()->whereHas('posts', fn ($q) => $q->published())->get(['slug', 'updated_at']) as $c) {
                $urls[] = [route('blog.category', $c->slug), $c->updated_at];
            }
            foreach (BlogPost::query()->published()->latest('published_at')->get(['slug', 'updated_at']) as $p) {
                $urls[] = [route('blog.show', $p->slug), $p->updated_at];
            }

            return view('site.sitemap', ['urls' => $urls])->render();
        });

        return response($xml, 200, ['Content-Type' => 'application/xml']);
    }

    public function robots(): Response
    {
        $lines = app()->isProduction()
            ? ['User-agent: *', 'Disallow: /admin', 'Disallow: /booking/', '', 'Sitemap: '.route('sitemap')]
            : ['User-agent: *', 'Disallow: /']; // keep staging/local out of search engines

        return response(implode("\n", $lines)."\n", 200, ['Content-Type' => 'text/plain']);
    }
}
