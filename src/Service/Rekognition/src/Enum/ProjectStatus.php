<?php

namespace AsyncAws\Rekognition\Enum;

/**
 * The current status of the delete project operation.
 */
final class ProjectStatus
{
    public const CREATED = 'CREATED';
    public const CREATING = 'CREATING';
    public const DELETING = 'DELETING';

    public static function exists(string $value): bool
    {
        return isset([
            self::CREATED => true,
            self::CREATING => true,
            self::DELETING => true,
        ][$value]);
    }
}
