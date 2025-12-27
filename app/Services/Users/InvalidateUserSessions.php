<?php

namespace App\Services\Users;

use App\Models\User;
use Illuminate\Support\Facades\DB;

class InvalidateUserSessions
{
    public function execute(User $user): void
    {
        if (config('session.driver') !== 'database') {
            return;
        }

        $table = config('session.table', 'sessions');

        DB::table($table)->where('user_id', $user->id)->delete();
    }
}
