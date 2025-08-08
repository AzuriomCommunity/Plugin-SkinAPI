<?php

namespace Azuriom\Plugin\SkinApi\Models;

use Illuminate\Database\Eloquent\Model;
use Azuriom\Models\User;

class SkinType extends Model
{
    protected $fillable = [
        'user_id',
        'is_slim'
    ];

    protected $casts = [
        'is_slim' => 'boolean'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
