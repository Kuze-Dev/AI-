<?php

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

foreach (DB::table('features')->get() as $feature) {
    $scope = str($feature->scope)->explode('|');

    if (!($scope[0] instanceof Model)) {
        continue;
    }

    $newScope = sprintf('%s|%s', app($scope[0])->getTable(), $scope[1]);

    DB::table('features')
        ->where('id', $feature->id)
        ->update([
            'scope' => $newScope
        ]);
}

echo 'done!';
