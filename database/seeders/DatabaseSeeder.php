<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            PermissionSeeder::class,
            PlanSeeder::class,
        ]);

        // إنشاء مدرس تجريبي جاهز mr-diaa
        $service = app(\App\Services\TenantRegistrationService::class);
        if (! \App\Models\Tenant::where('slug', 'mr-diaa')->exists()) {
            $service->register([
                'name' => 'الأستاذ ضياء أشرف',
                'center_name' => 'سنتر الأستاذ ضياء',
                'slug' => 'mr-diaa',
                'email' => 'mr-diaa@admin.com',
                'phone' => '01202325201',
                'password' => '123456789',
            ]);
        }
    }
}
