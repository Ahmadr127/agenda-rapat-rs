<?php

namespace App\Support;

class AgendaScope
{
    public const SCOPE_INTERNAL = 'internal';

    public const SCOPE_EXTERNAL = 'external';

    /**
     * Daftar scope yang tersedia beserta label tampilan.
     *
     * @return array<string, array{key: string, label: string}>
     */
    public static function scopes(): array
    {
        return [
            self::SCOPE_INTERNAL => ['key' => self::SCOPE_INTERNAL, 'label' => 'Rapat Internal'],
            self::SCOPE_EXTERNAL => ['key' => self::SCOPE_EXTERNAL, 'label' => 'Rapat Eksternal'],
        ];
    }

    public static function default(): string
    {
        $scope = (string) config('agenda.default_scope', self::SCOPE_INTERNAL);

        return in_array($scope, [self::SCOPE_INTERNAL, self::SCOPE_EXTERNAL], true)
            ? $scope
            : self::SCOPE_INTERNAL;
    }

    public static function label(string $scope): string
    {
        return $scope === self::SCOPE_EXTERNAL
            ? 'Rapat Eksternal'
            : 'Rapat Internal';
    }

    public static function other(string $scope): string
    {
        return $scope === self::SCOPE_EXTERNAL
            ? self::SCOPE_INTERNAL
            : self::SCOPE_EXTERNAL;
    }
}
