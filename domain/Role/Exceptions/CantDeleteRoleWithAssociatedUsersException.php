<?php

declare(strict_types=1);

namespace Domain\Role\Exceptions;

use LogicException;

class CantDeleteRoleWithAssociatedUsersException extends LogicException {}
