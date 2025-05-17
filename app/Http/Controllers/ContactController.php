<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use App\Services\TelegramService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ContactController extends Controller
{
    protected $telegramService;

    public function __construct(TelegramService $telegramService)
    {
        $this->telegramService = $telegramService;
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:contacts,email',
            'phone' => 'nullable|string|max:20',
            'tags' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $contact = Contact::create($request->all());

        if ($request->has('tags')) {
            $contact->tags()->sync($request->tags);
        }

        // Отправляем уведомление в Telegram
        $this->sendTelegramNotification($contact, 'created');

        return response()->json($contact->load('tags'));
    }

    public function update(Request $request, Contact $contact)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:contacts,email,' . $contact->id,
            'phone' => 'nullable|string|max:20',
            'tags' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $contact->update($request->all());

        if ($request->has('tags')) {
            $contact->tags()->sync($request->tags);
        }

        // Отправляем уведомление в Telegram
        $this->sendTelegramNotification($contact, 'updated');

        return response()->json($contact->load('tags'));
    }

    protected function sendTelegramNotification(Contact $contact, string $action)
    {
        $tags = $contact->tags->pluck('name')->join(', ');
        $tagsText = $tags ? "\nТеги: {$tags}" : '';

        $message = sprintf(
            "🔔 <b>Контакт %s</b>\n\n" .
            "Имя: %s\n" .
            "Email: %s\n" .
            "Телефон: %s%s",
            $action === 'created' ? 'создан' : 'обновлен',
            $contact->name,
            $contact->email,
            $contact->phone ?? 'не указан',
            $tagsText
        );

        $this->telegramService->sendMessage($message);
    }
} 