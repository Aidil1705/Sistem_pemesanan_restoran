<?php

declare(strict_types=1);

namespace App;

use PDO;

/**
 * Base class for database-backed services.
 */
abstract class BaseModel
{
    public function __construct(protected PDO $pdo)
    {
    }
}
