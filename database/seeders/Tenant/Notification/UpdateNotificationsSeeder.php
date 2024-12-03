<?php

namespace Database\Seeders\Tenant\Notification;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UpdateNotificationsSeeder extends Seeder
{
    public function run()
    {
        // Update notifications with heroicon replacements
        DB::table('notifications')->where('data', 'like', '%heroicon-o-download%')->chunkById(100, function ($notifications) {
            foreach ($notifications as $notification) {
                $updatedData = str_replace(
                    'heroicon-o-download',
                    'heroicon-o-arrow-down-tray',
                    $notification->data
                );

                DB::table('notifications')
                    ->where('id', $notification->id)
                    ->update(['data' => $updatedData]);
            }
        });

        $this->command->info('Updated heroicon names in the notifications table.');
    }
}
