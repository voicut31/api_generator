<?php

declare(strict_types=1);

namespace ApiGenerator\Exception;

use Exception;

/**
 * Class ModuleNotFoundException
 * Thrown when a requested module/table is not found in the API structure
 *
 * @package ApiGenerator\Exception
 */
class ModuleNotFoundException extends Exception
{
}

