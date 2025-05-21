<?php

declare(strict_types=1);

namespace Domain\ServiceOrder\Exceptions;

use LogicException;

class ServiceOrderStatusClosedNotAllowedException extends LogicException {}
