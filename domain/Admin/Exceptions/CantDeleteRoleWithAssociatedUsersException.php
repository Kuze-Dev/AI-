<?php

namespace Domain\Admin\Exceptions;

use LogicException;

class CantDeleteRoleWithAssociatedUsersException extends LogicException
{
}
