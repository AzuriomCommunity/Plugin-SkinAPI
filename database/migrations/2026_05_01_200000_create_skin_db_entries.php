<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $disk = Storage::disk('public');
        $now = now();

        $skinFiles = collect($disk->files('skins'))
            ->filter(fn ($f) => preg_match('/^skins\/(\d+)\.png$/', $f))
            ->mapWithKeys(fn ($f) => [(int) basename($f, '.png') => $f]);

        $userIds = DB::table('users')
            ->whereIn('id', $skinFiles->keys())
            ->pluck('id')
            ->flip();

        $skinFiles
            ->filter(fn ($f, $userId) => $userIds->has($userId))
            ->map(fn ($f, $userId) => [
                'user_id' => $userId,
                'file' => basename($f),
                'sha256' => hash_file('sha256', $disk->path($f)),
                'slim' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ])
            ->values()
            ->chunk(500)
            ->each(fn ($chunk) => DB::table('skin_skins')->insertOrIgnore($chunk->all()));

        $capeFiles = collect($disk->files('skins/capes'))
            ->filter(fn ($f) => preg_match('/^skins\/capes\/(\d+)\.png$/', $f))
            ->mapWithKeys(fn ($f) => [(int) basename($f, '.png') => $f]);

        $userIds = DB::table('users')
            ->whereIn('id', $capeFiles->keys())
            ->pluck('id')
            ->flip();

        $capeFiles
            ->filter(fn ($f, $userId) => $userIds->has($userId))
            ->map(fn ($f, $userId) => [
                'user_id' => $userId,
                'file' => basename($f),
                'sha256' => hash_file('sha256', $disk->path($f)),
                'created_at' => $now,
                'updated_at' => $now,
            ])
            ->values()
            ->chunk(500)
            ->each(fn ($chunk) => DB::table('skin_capes')->insertOrIgnore($chunk->all()));

        $disk->deleteDirectory('face');
        $disk->deleteDirectory('combo');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Ignore
    }
};
