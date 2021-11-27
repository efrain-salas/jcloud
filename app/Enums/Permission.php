<?php

namespace App\Enums;

use Spatie\Enum\Laravel\Enum;

/**
 * @method static self OWNER()
 * @method static self SOME_USERS()
 * @method static self ALL_USERS()
 * @method static self PUBLIC()
 */
final class Permission extends Enum
{
    protected static function values(): array
    {
        return [
            'OWNER' => 0,
            'SOME_USERS' => 1,
            'ALL_USERS' => 2,
            'PUBLIC' => 3,
        ];
    }

    protected static function labels(): array
    {
        return [
            'OWNER' => 'Solo yo',
            'SOME_USERS' => 'Usuarios seleccionados',
            'ALL_USERS' => 'Todos los usuarios',
            'PUBLIC' => 'Público',
        ];
    }
}
