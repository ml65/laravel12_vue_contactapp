<?php

namespace App\Services;

use App\Models\Contact;

class ContactNotificationFormatter
{
    /**
     * Форматирует сообщение о создании контакта
     *
     * @param Contact $contact
     * @return string
     */
    public function formatCreated(Contact $contact): string
    {
        return $this->formatMessage($contact, 'создан');
    }
    
    /**
     * Форматирует сообщение об обновлении контакта
     *
     * @param Contact $contact
     * @return string
     */
    public function formatUpdated(Contact $contact): string
    {
        return $this->formatMessage($contact, 'обновлен');
    }
    
    /**
     * Форматирует сообщение о контакте
     *
     * @param Contact $contact
     * @param string $action
     * @return string
     */
    private function formatMessage(Contact $contact, string $action): string
    {
        $tags = $contact->tags->pluck('name')->join(', ');
        $tagsText = $tags ? "\nТеги: {$tags}" : '';

        return sprintf(
            "🔔 <b>Контакт %s</b>\n\n" .
            "Имя: %s\n" .
            "Email: %s\n" .
            "Телефон: %s%s",
            $action,
            $contact->name,
            $contact->email,
            $contact->phone ?? 'не указан',
            $tagsText
        );
    }
} 