<?php

declare(strict_types=1);

namespace Domain\Admin\Exceptions;

use LogicException;

class CantDeleteZeroDayAdminException extends LogicException {}
