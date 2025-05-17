<?php

namespace App\Contracts;

interface NotificationServiceInterface
{
    /**
     * Отправляет уведомление
     *
     * @param string $message Сообщение для отправки
     * @return bool Результат отправки
     */
    public function send(string $message): bool;
} 