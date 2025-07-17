<?php

use Domain\Tenant\Models\Tenant;
use Domain\Content\Models\ContentEntry;
use Domain\Blueprint\Models\Blueprint;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Permission;
use App\Tenancy\Jobs\CreateS3Bucket;
use Domain\Tenant\Support\ApiAbilitties;

dd(phpinfo());



