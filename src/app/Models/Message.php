<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    /**
     * Поля, которые можно заполнять через массовое присваивание
     */
    protected $fillable = ['user_id', 'message'];

    /**
     * Всегда загружать информацию о пользователе
     */
    protected $with = ['user'];

    /**
     * Связь с пользователем (сообщение принадлежит пользователю)
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Трансформация даты
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
