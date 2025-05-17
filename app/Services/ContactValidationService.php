<?php

namespace App\Services;

use App\Models\Contact;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class ContactValidationService
{
    /**
     * Валидирует данные контакта
     *
     * @param array $data Данные для валидации
     * @param Contact|null $contact Существующий контакт (для обновления)
     * @return array Валидированные данные
     * @throws ValidationException
     */
    public function validate(array $data, ?Contact $contact = null): array
    {
        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:contacts,email' . ($contact ? ",{$contact->id}" : ''),
            'phone' => 'nullable|string|max:20',
            'tags' => 'nullable|array',
            'tags.*' => 'exists:tags,id'
        ];
        
        $validator = Validator::make($data, $rules);
        
        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
        
        return $validator->validated();
    }
} 