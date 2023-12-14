<?php

use Domain\Tenant\Models\Tenant;

tenancy()->initialize(Tenant::first());
