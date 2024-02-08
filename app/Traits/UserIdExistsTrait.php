<?php

namespace App\Traits;

trait UserIdExistsTrait
{
    public function userIdExistsInTable($tableName, $userId)
    {
        return \DB::table($tableName) // Use the DB facade to access the table
            ->where('user_id', $userId)
            ->get()
            ->count() === 1;
    }
}