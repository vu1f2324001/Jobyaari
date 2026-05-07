<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Slug
{
    public static function toSlug(string $value): string
    {
        $slug = Str::lower($value);
        $slug = preg_replace('/[^a-z0-9]+/i', '-', $slug) ?? $slug;
        $slug = trim($slug, '-');

        return $slug === '' ? 'item' : $slug;
    }

    /**
     * Ensures uniqueness by appending -1, -2 ... if needed.
     */
    public static function uniqueForModel(
        Model $model,
        string $slug,
        string $field = 'slug'
    ): string {
        $base = $slug;
        $i = 0;

        while (true) {
            $candidate = $i === 0 ? $base : $base.'-'.$i;

            $query = $model->newQuery()->where($field, $candidate);

            // Exclude current record (update flow)
            if ($model->exists) {
                $query->where($model->getKeyName(), '!=', $model->getKey());
            }

            if (!$query->exists()) {
                return $candidate;
            }

            $i++;
        }
    }
}
