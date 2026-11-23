<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NewsletterSubscriber;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SubscriberController extends Controller
{
    public function index(Request $request): View
    {
        $subscribers = NewsletterSubscriber::query()
            ->when($request->query('q'), fn ($q, $t) => $q->where('email', 'like', "%{$t}%"))
            ->latest()->paginate(30)->withQueryString();

        return view('admin.subscribers.index', [
            'subscribers' => $subscribers,
            'total' => NewsletterSubscriber::query()->where('status', 'subscribed')->count(),
        ]);
    }

    public function export(): StreamedResponse
    {
        return response()->streamDownload(function () {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['email', 'subscribed_at']);
            NewsletterSubscriber::query()->where('status', 'subscribed')->orderBy('id')
                ->chunk(500, fn ($rows) => $rows->each(fn ($s) => fputcsv($out, [self::csvSafe($s->email), $s->created_at->toDateTimeString()])));
            fclose($out);
        }, 'subscribers-'.now()->format('Y-m-d').'.csv', ['Content-Type' => 'text/csv']);
    }

    public function destroy(NewsletterSubscriber $subscriber): RedirectResponse
    {
        $subscriber->delete();

        return back()->with('success', 'Subscriber removed.');
    }

    /** Prevent spreadsheet formula injection when the CSV is opened in Excel. */
    private static function csvSafe(string $value): string
    {
        return in_array($value[0] ?? '', ['=', '+', '-', '@'], true) ? "'".$value : $value;
    }
}
