<?php

namespace App\Helpers;

use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Uuid;
use Stancl\Tenancy\Contracts\Tenant;

class UuidGeneratorHelper
{
    public static function generateUniqueUuidForTable(string $tableName, string $columnName = 'uuid'): string
    {
        $uuid = Uuid::uuid4()->toString();
        $existingUuids = DB::table($tableName)->pluck($columnName)->toArray();
        while (in_array($uuid, $existingUuids)) {
            $uuid = Uuid::uuid4()->toString();
        }
        return $uuid;
    }
}
