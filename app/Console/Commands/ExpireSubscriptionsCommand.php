<?php

namespace App\Console\Commands;

use App\Models\Subscription;
use Illuminate\Console\Command;

class ExpireSubscriptionsCommand extends Command
{
    protected $signature = 'subscriptions:expire';

    protected $description = 'فحص دوري للاشتراكات المنتهية وتحويل حالتها تلقائياً';

    public function handle(): int
    {
        $now = now();

        // 1. تحويل الفترات التجريبية المنتهية (Trial -> Expired)
        $expiredTrials = Subscription::where('status', 'trial')
            ->whereNotNull('trial_ends_at')
            ->where('trial_ends_at', '<', $now)
            ->update(['status' => 'expired']);

        // 2. تحويل الاشتراكات العادية المنتهية (Active -> Past Due)
        $expiredActives = Subscription::where('status', 'active')
            ->whereNotNull('ends_at')
            ->where('ends_at', '<', $now)
            ->update(['status' => 'past_due']);

        $this->info("تم تحديث الاشتراكات: {$expiredTrials} تجارب منتهية، و {$expiredActives} اشتراكات متأخرة.");

        return Command::SUCCESS;
    }
}
