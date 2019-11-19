<?php

namespace RealejoTest\Enum;

use Realejo\Enum\EnumFlagged;

class EnumFlaggedConcrete extends EnumFlagged
{
    public const EXECUTE = 1 << 0; // 1
    public const WRITE = 1 << 1; // 2
    public const READ = 1 << 2; // 4

    static protected $constDescription = [
        self::EXECUTE => ['x', 'execute'],
        self::WRITE => 'w',
        self::READ => 'r',
    ];
}
