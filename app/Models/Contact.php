<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    // Разрешаем массовое заполнение всех полей
    protected $fillable = [
        'name',
        'email',
        'phone',
    ];
}
