<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HomePageContent extends Model
{
    protected $fillable = [
        'is_published',
        'title_en',
        'title_ar',
        'title_fa',
        'title_ru',
        'description_en',
        'description_ar',
        'description_fa',
        'description_ru',
    ];

    protected $casts = [
        'is_published' => 'boolean',
    ];

    public static function current(): self
    {
        return static::query()->firstOrCreate([], [
            'is_published' => true,
            'title_en' => 'Sham VPN',
            'title_ar' => 'شام VPN',
            'title_fa' => 'شام VPN',
            'title_ru' => 'Sham VPN',
            'description_en' => 'Secure VPN access for every device.',
            'description_ar' => 'وصول VPN آمن لكل أجهزتك.',
            'description_fa' => 'دسترسی امن VPN برای همه دستگاه‌های شما.',
            'description_ru' => 'Безопасный VPN-доступ для всех ваших устройств.',
        ]);
    }
}
