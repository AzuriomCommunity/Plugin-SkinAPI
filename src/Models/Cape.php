<?php

namespace Azuriom\Plugin\SkinApi\Models;

use Azuriom\Models\Traits\HasImage;
use Azuriom\Models\Traits\HasTablePrefix;
use Azuriom\Models\Traits\HasUser;
use Azuriom\Models\User;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $user_id
 * @property string $file
 * @property string $sha256
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Azuriom\Models\User $user
 */
class Cape extends Model
{
    use HasImage;
    use HasTablePrefix;
    use HasUser;

    /**
     * The table prefix associated with the model.
     */
    protected string $prefix = 'skin_';

    /**
     * The file where this skin is stored.
     */
    protected string $imageKey = 'file';

    /**
     * The directory where to upload the images.
     */
    protected string $imagePath = 'skins/capes';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'file', 'sha256', 'user_id',
    ];

    /**
     * Get the user this cape belongs to.
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
     * Find the cape for a given user, or return null.
     */
    public static function forUser(int $userId): ?static
    {
        return static::where('user_id', $userId)->first();
    }
}
