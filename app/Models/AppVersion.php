<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppVersion extends Model
{
    public const PLATFORMS = [
        'windows' => 'Windows',
        'android' => 'Android',
        'macos' => 'macOS',
        'linux' => 'Linux',
        'ios' => 'iOS',
    ];

    protected $fillable = [
        'platform',
        'version',
        'status',
        'is_visible',
        'file_path',
        'file_name',
        'file_size',
        'notes_en',
        'notes_ar',
        'notes_fa',
        'notes_ru',
    ];

    protected $casts = [
        'is_visible' => 'boolean',
        'file_size' => 'integer',
    ];

    public function getPlatformLabelAttribute(): string
    {
        return self::PLATFORMS[$this->platform] ?? ucfirst($this->platform);
    }

    public function getDownloadUrlAttribute(): ?string
    {
        return $this->file_path ? asset($this->file_path) : null;
    }
}
