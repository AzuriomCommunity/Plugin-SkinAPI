<?php

namespace Azuriom\Plugin\SkinApi\Models;

use Azuriom\Models\Traits\HasImage;
use Azuriom\Models\Traits\HasTablePrefix;
use Azuriom\Models\Traits\HasUser;
use Azuriom\Models\User;
use Azuriom\Plugin\SkinApi\Render\AvatarRenderer;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $user_id
 * @property string $file
 * @property string $sha256
 * @property bool $slim
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Azuriom\Models\User $user
 */
class Skin extends Model
{
    use HasImage;
    use HasTablePrefix;
    use HasUser;

    protected static function booted(): void
    {
        static::saved(function (self $skin) {
            $previousSkin = $skin->getOriginal('file');

            if ($previousSkin !== null) {
                AvatarRenderer::deleteAll($previousSkin);
            }

            $skinPath = $skin->getDisk()->path($skin->getPath());
            AvatarRenderer::renderAll($skinPath, $skin->file, $skin->slim);
        });

        static::deleted(function (self $skin) {
            AvatarRenderer::deleteAll($skin->getOriginal('file'));
        });
    }

    /**
     * The table prefix associated with the model.
     */
    protected string $prefix = 'skin_';

    /**
     * The file where this skin is stored.
     */
    protected string $imageKey = 'file';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'file', 'sha256', 'slim', 'user_id',
    ];

    /**
     * The attributes that should be cast to native types.
     */
    protected $casts = [
        'slim' => 'boolean',
    ];

    /**
     * Get the user this skin belongs to.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getPath(): string
    {
        return $this->getImagePath();
    }

    public function getDisk(): Filesystem
    {
        return $this->getImageDisk();
    }

    /**
     * Find the skin for a given user, or null.
     */
    public static function forUser(int $userId): ?static
    {
        return static::where('user_id', $userId)->first();
    }
}
