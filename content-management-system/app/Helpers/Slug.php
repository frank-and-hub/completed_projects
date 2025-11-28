<?php

namespace App\Helpers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Slug
{

    protected function getRelatedSlugs($slug, $id = 0, $model)
    {
        return $model::select('slug')->where('slug', 'like', $slug . '%')
            ->where('id', '<>', $id)
            ->get();
    }

    public static function create($title, $model, $id = 0)
    {
        // Normalize the title
        $slug = Str::slug($title);

        // Get any that could possibly be related.
        // This cuts the queries down by doing it once.

        $q = $model::select('slug')->where('slug', 'like', $slug . '%')->where('id', '<>', $id);

        if (in_array("Illuminate\Database\Eloquent\SoftDeletes", class_uses($model))) {
            $q->withTrashed();
        }

        $allSlugs = $q->get();

        // If we haven't used it before then we are all good.
        if (!$allSlugs->contains('slug', $slug)) {
            return $slug;
        }

        // Just append numbers like a savage until we find not used.
        for ($i = 1; $i <= 1000; $i++) {
            $newSlug = $slug . '-' . $i;
            if (!$allSlugs->contains('slug', $newSlug)) {
                return $newSlug;
            }
        }

        throw new \Exception('Can not create a unique slug');
    }
}
