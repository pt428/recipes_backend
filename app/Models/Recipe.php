<?php
//backend\app\Models\Recipe.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Str;
class Recipe extends Model
{
    use HasFactory;

 
    protected $fillable = [
        'user_id',
        'title',
        'slug',
        'description',
        'category_id',
        'difficulty',
        'prep_time_minutes',
        'cook_time_minutes',
        'servings',
        'serving_type',
        'visibility',
        'main_image_path',
        'image_path',
        'share_token',
    ];

    protected $casts = [
        'prep_time_minutes' => 'integer',
        'cook_time_minutes' => 'integer',
        'servings' => 'integer',
        'user_id' => 'integer',
    ];
    protected static function booted(): void
    {
        // Při vytváření receptu – slug + share_token (pokud není ručně nastaven)
        static::creating(function (Recipe $recipe) {
            if (empty($recipe->slug)) {
                $baseSlug = Str::slug($recipe->title);

                $slug = $baseSlug;
                $i = 1;

                // zajistit unikátnost slugu
                while (static::where('slug', $slug)->exists()) {
                    $slug = $baseSlug . '-' . $i;
                    $i++;
                }

                $recipe->slug = $slug;
            }

            if ($recipe->visibility === 'link' && empty($recipe->share_token)) {
                $recipe->share_token = Str::random(40);
            }
        });

        // Při mazání receptu – smazat obrázek
        static::deleting(function (Recipe $recipe) {
            if ($recipe->image_path && Storage::disk('public')->exists($recipe->image_path)) {
                Storage::disk('public')->delete($recipe->image_path);
            }
        });
    }
    public function scopeVisibleFor($query, ?\App\Models\User $user)
    {
        if ($user) {
            return $query->where(function ($q) use ($user) {
                $q->where('visibility', 'public')
                    ->orWhere('user_id', $user->id);
            });
        }

        return $query->where('visibility', 'public');
    }
    // Relace k uživatelům, kteří mají recept jako oblíbený
    public function favoritedBy()
    {
        return $this->belongsToMany(User::class, 'favorite_recipes')
            ->withTimestamps();
    }

    // Počet uživatelů, kteří mají recept jako oblíbený
    public function favoritesCount()
    {
        return $this->favoritedBy()->count();
    }
    public function author()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function ingredients()
    {
        return $this->hasMany(Ingredient::class);
    }

    public function steps()
    {
        return $this->hasMany(Step::class)->orderBy('order_index');
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'recipe_tag');
    }

    public function ratings()
    {
        return $this->hasMany(Rating::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class)->latest();
    }
}
