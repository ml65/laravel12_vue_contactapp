<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Contact extends Model
{
    // Разрешаем массовое заполнение всех полей
    protected $fillable = [
        'name',
        'email',
        'phone',
    ];

    /**
     * Получить теги, связанные с контактом
     */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class);
    }
}
