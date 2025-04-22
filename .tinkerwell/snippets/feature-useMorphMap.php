<?php

$featureModel = new class extends \Illuminate\Database\Eloquent\Model {
    protected $fillable = ['scope'];

    public function getTable()
    {
        return config('pennant.stores.database.table');
    }
};

foreach ($featureModel->get() as $feature) {
    $scope = str($feature->scope)->explode('|');

    if (!class_exists($scope[0])) {
        continue;
    }

    if (!(new $scope[0]) instanceof \Illuminate\Database\Eloquent\Model) {
        continue;
    }

    $newScope = sprintf('%s|%s', app($scope[0])->getTable(), $scope[1]);

    $existFeature = $featureModel
        ->where([
            'name' => $feature->name,
            'scope' => $newScope
        ])
        ->delete();

    $feature->update([
        'scope' => $newScope
    ]);
}

echo 'done!';
