<?php

namespace Src\Common;

class Audit
{
    public static function created(string $entity, int $id): void
    {
        Logger::info("$entity created. ID: $id");
    }

    public static function updated(string $entity, int $id): void
    {
        Logger::info("$entity updated. ID: $id");
    }

    public static function deleted(string $entity, int $id): void
    {
        Logger::info("$entity deleted. ID: $id");
    }
}
