<?php

namespace App\Support;

use App\Models\Succursale;
use Illuminate\Database\Eloquent\Builder;

class SuccursaleContext
{
    public static function isGerant(): bool
    {
        $user = auth()->user();
        if (!$user) {
            return false;
        }

        return $user->hasAnyRole(['gerant', 'المسير']);
    }

    /**
     * Returns true if the user has access to more than one succursale
     * (gérants always can; regular users with multiple assignments too).
     */
    public static function canSwitch(): bool
    {
        $user = auth()->user();
        if (!$user) {
            return false;
        }

        if (self::isGerant()) {
            return true;
        }

        return $user->succursales()->count() > 1;
    }

    public static function currentIdForRead(): ?int
    {
        $user = auth()->user();
        if (!$user) {
            return null;
        }

        if (self::isGerant()) {
            $selected = session('active_succursale_id');
            return $selected ? (int) $selected : null;
        }

        // Multi-filiale user: use session selection if allowed
        $selected = session('active_succursale_id');
        if ($selected && $user->hasAccessToSuccursale((int) $selected)) {
            return (int) $selected;
        }

        // Fallback to primary succursale
        if ($user->fk_id_succursale) {
            return (int) $user->fk_id_succursale;
        }

        // Fallback to first assigned succursale
        $first = $user->succursales()->value('id');
        if ($first) {
            return (int) $first;
        }

        return (int) (Succursale::query()->value('id') ?? 0) ?: null;
    }

    public static function currentIdForWrite(): ?int
    {
        $user = auth()->user();
        if (!$user) {
            return (int) (Succursale::query()->value('id') ?? 0) ?: null;
        }

        if (self::isGerant()) {
            $selected = session('active_succursale_id');
            if ($selected) {
                return (int) $selected;
            }
            if ($user->fk_id_succursale) {
                return (int) $user->fk_id_succursale;
            }
            return (int) (Succursale::query()->value('id') ?? 0) ?: null;
        }

        // Multi-filiale user
        $selected = session('active_succursale_id');
        if ($selected && $user->hasAccessToSuccursale((int) $selected)) {
            return (int) $selected;
        }

        if ($user->fk_id_succursale) {
            return (int) $user->fk_id_succursale;
        }

        $first = $user->succursales()->value('id');
        if ($first) {
            return (int) $first;
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

        $currentId = self::currentIdForRead();

        if (!$currentId) {
            return $query->whereRaw('1=0');
        }

        return $query->where($column, $currentId);
    }
}
