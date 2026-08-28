<?php

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class MaterialCategory extends Model
{
    use HasFactory;

    protected $table = 'material_categories';

    protected $fillable = [
        'name',
        'slug',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function materials(): HasMany
    {
        return $this->hasMany(MaterialCatalogue::class, 'category', 'slug');
    }

    /**
     * Return associative array of [slug => name] for active categories,
     * ensuring fallback defaults if table hasn't been seeded yet.
     */
    public static function getActiveList(): array
    {
        try {
            $categories = static::where('is_active', true)->orderBy('name')->pluck('name', 'slug')->toArray();
            if (!empty($categories)) {
                return $categories;
            }
        } catch (\Throwable $e) {
            // fallback if table does not exist
        }

        return [
            'cement' => 'Cement & Binders',
            'steel' => 'Steel & Reinforcements',
            'aggregate' => 'Aggregates & Sand',
            'timber' => 'Timber & Formwork',
            'block' => 'Blocks & Bricks',
            'finishing' => 'Finishing & Tiles',
            'plumbing' => 'Plumbing & Pipes',
            'electrical' => 'Electrical & Conduits',
            'equipment_consumable' => 'Equipment Consumables (Diesel/Oil)',
            'other' => 'Other Materials',
        ];
    }

    /**
     * Generate a unique slug for a given name.
     */
    public static function generateUniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $baseSlug = Str::slug($name, '_');
        if (empty($baseSlug)) {
            $baseSlug = 'category_' . Str::lower(Str::random(5));
        }

        $slug = $baseSlug;
        $counter = 1;

        while (static::where('slug', $slug)->when($ignoreId, fn($q) => $q->where('id', '!=', $ignoreId))->exists()) {
            $slug = $baseSlug . '_' . $counter;
            $counter++;
        }

        return $slug;
    }
}
