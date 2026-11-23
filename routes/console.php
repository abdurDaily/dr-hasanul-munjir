<?php

use Illuminate\Support\Facades\Schedule;

/*
| Runs from the server cron (every minute):  php /path/to/artisan schedule:run
| Booking emails are queued; on shared hosting without a permanent worker this drains
| the queue each minute. On a VPS you can run a supervised `queue:work` instead.
*/
Schedule::command('queue:work --stop-when-empty --tries=3 --max-time=50')->everyMinute()->withoutOverlapping();
Schedule::command('queue:prune-failed --hours=720')->daily();
