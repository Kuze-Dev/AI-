<?php

declare(strict_types=1);

return Rector\Config\RectorConfig::configure()
    ->withParallel(maxNumberOfProcess: 6)
    ->withPhpSets()
    ->withPaths([
        __DIR__.'/app',
        __DIR__.'/database',
        __DIR__.'/domain',
        __DIR__.'/resources',
        __DIR__.'/routes',
        __DIR__.'/support',
        __DIR__.'/tests',
    ])
//    ->withSkip([
// //        Rector\Php81\Rector\Array_\FirstClassCallableRector::class => [
// //            __DIR__.'/app/Http/Controllers/Backend/Dev/ArtisanController.php',
// //            __DIR__.'/app/Http/Controllers/Backend/Dev/QueueDatabaseMonitoringController.php',
// //            __DIR__.'/routes',
// //        ],
//        //        Rector\Php81\Rector\Property\ReadOnlyPropertyRector::class => [
//        //            __DIR__.'/domain/Http/Actions/Order/ConfirmOrderComputationAction.php',
//        //            __DIR__.'/domain/Http/Actions/Order/PurchaseOrderComputationAction.php',
//        //        ],
//    ])
    ->withSets([
        RectorLaravel\Set\LaravelSetList::LARAVEL_110,
        RectorLaravel\Set\LaravelSetList::LARAVEL_ARRAY_STR_FUNCTION_TO_STATIC_CALL,
        //        LaravelSetList::LARAVEL_FACADE_ALIASES_TO_FULL_NAMES,
    ])
    ->withRules([
        Spatie\Ray\Rector\RemoveRayCallRector::class,
        RectorLaravel\Rector\Class_\AddExtendsAnnotationToModelFactoriesRector::class,
        //        RectorLaravel\Rector\ClassMethod\AddGenericReturnTypeToRelationsRector::class,
        RectorLaravel\Rector\ClassMethod\AddParentBootToModelClassMethodRector::class,
        RectorLaravel\Rector\ClassMethod\AddParentRegisterToEventServiceProviderRector::class,
        //        RectorLaravel\Rector\Class_\AnonymousMigrationsRector::class,
        RectorLaravel\Rector\Expr\AppEnvironmentComparisonToParameterRector::class,
        RectorLaravel\Rector\MethodCall\AssertStatusToAssertMethodRector::class,
        RectorLaravel\Rector\StaticCall\DispatchToHelperFunctionsRector::class,
        RectorLaravel\Rector\MethodCall\EloquentOrderByToLatestOrOldestRector::class,
        RectorLaravel\Rector\PropertyFetch\OptionalToNullsafeOperatorRector::class,
        RectorLaravel\Rector\FuncCall\RemoveDumpDataDeadCodeRector::class,
        RectorLaravel\Rector\Expr\SubStrToStartsWithOrEndsWithStaticMethodCallRector\SubStrToStartsWithOrEndsWithStaticMethodCallRector::class,
        RectorLaravel\Rector\MethodCall\UseComponentPropertyWithinCommandsRector::class,
    ])
    ->withCache(
        cacheDirectory: 'build/rector',
        cacheClass: Rector\Caching\ValueObject\Storage\FileCacheStorage::class,
    );
