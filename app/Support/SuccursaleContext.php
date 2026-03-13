<?php

namespace App\Support;

use App\Models\Succursale;
use Illuminate\Database\Eloquent\Builder;

class SuccursaleContext
{
    public static function isGerant(): bool
    {
        $user = auth()->user();
        return $user ? $user->hasRole('gerant') : false;
    }

    public static function currentIdForRead(): ?int
    {
        $user = auth()->user();
        if (!$user) {
            return null;
        }

        if (!self::isGerant()) {
            if ($user->fk_id_succursale) {
                return (int) $user->fk_id_succursale;
            }

            return (int) (Succursale::query()->value('id') ?? 0) ?: null;
        }

        $selected = session('active_succursale_id');
        return $selected ? (int) $selected : null;
    }

    public static function currentIdForWrite(): ?int
    {
        $user = auth()->user();
        if (!$user) {
            return (int) (Succursale::query()->value('id') ?? 0) ?: null;
        }

        if (!self::isGerant()) {
            if ($user->fk_id_succursale) {
                return (int) $user->fk_id_succursale;
            }

            return (int) (Succursale::query()->value('id') ?? 0) ?: null;
        }

        $selected = session('active_succursale_id');
        if ($selected) {
            return (int) $selected;
        }

        if ($user->fk_id_succursale) {
            return (int) $user->fk_id_succursale;
        }

        return (int) (Succursale::query()->value('id') ?? 0) ?: null;
    }

    public static function apply(Builder $query, string $column = 'fk_id_succursale'): Builder
    {
        $user = auth()->user();
        if (!$user) {
            return $query;
        }

        if (self::isGerant()) {
            $selected = session('active_succursale_id');
            return $selected ? $query->where($column, (int) $selected) : $query;
        }

        if (!$user->fk_id_succursale) {
            $fallback = (int) (Succursale::query()->value('id') ?? 0);
            return $fallback > 0 ? $query->where($column, $fallback) : $query->whereRaw('1=0');
        }

        return $query->where($column, (int) $user->fk_id_succursale);
    }
}

