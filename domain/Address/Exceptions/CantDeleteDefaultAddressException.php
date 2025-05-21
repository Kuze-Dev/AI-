<?php

declare(strict_types=1);

namespace Domain\Address\Exceptions;

use LogicException;

class CantDeleteDefaultAddressException extends LogicException {}
