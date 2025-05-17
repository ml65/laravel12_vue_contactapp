<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use App\Services\TelegramService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * @OA\Info(
 *     version="1.0.0",
 *     title="API документация системы управления контактами",
 *     description="API для управления контактами",
 *     @OA\Contact(
 *         email="admin@example.com"
 *     )
 * )
 */
class ContactController extends Controller
{
    protected $telegramService;

    public function __construct(TelegramService $telegramService)
    {
        $this->telegramService = $telegramService;
    }

    /**
     * @OA\Get(
     *     path="/api/contacts",
     *     summary="Получить список контактов",
     *     tags={"Контакты"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Успешный ответ",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 type="object",
     *                 @OA\Property(property="id", type="integer"),
     *                 @OA\Property(property="name", type="string"),
     *                 @OA\Property(property="email", type="string"),
     *                 @OA\Property(property="phone", type="string"),
     *                 @OA\Property(property="tags", type="array", @OA\Items(type="object"))
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Не авторизован"
     *     )
     * )
     */
    public function index()
    {
        $fc = fopen('/tmp/contacts.txt', 'a');
        $contacts = Contact::with('tags')->get();
        fwrite($fc, var_export($contacts, true));
        return response()->json(Contact::with('tags')->get());
    }

    /**
     * @OA\Post(
     *     path="/api/contacts",
     *     summary="Создать новый контакт",
     *     tags={"Контакты"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "email"},
     *             @OA\Property(property="name", type="string"),
     *             @OA\Property(property="email", type="string", format="email"),
     *             @OA\Property(property="phone", type="string"),
     *             @OA\Property(property="tags", type="array", @OA\Items(type="integer"))
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Контакт успешно создан"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Ошибка валидации"
     *     )
     * )
     */
    public function store(Request $request)
    {
        $fc = fopen('/tmp/contacts.txt', 'a');
        fwrite($fc, var_export($request->all(), true));
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:contacts',
            'phone' => 'nullable|string|max:20',
            'tags' => 'nullable|array',
            'tags.*' => 'exists:tags,id'
        ]);

        fwrite($fc, var_export($validator->errors(), true));

        if ($validator->fails()) {
            fwrite($fc, var_export($validator->errors(), true));
            return response()->json($validator->errors(), 422);
        }

        $contact = Contact::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
        ]);
        fwrite($fc, var_export($contact, true));
        if ($request->has('tags')) {
            fwrite($fc, var_export($request->tags, true));
            $contact->tags()->sync($request->tags);
        }
        fwrite($fc, var_export($contact->load('tags'), true));

        // Отправляем уведомление в Telegram
        $this->sendTelegramNotification($contact, 'created');

        return response()->json($contact->load('tags'), 201);
    }

    /**
     * @OA\Get(
     *     path="/api/contacts/{id}",
     *     summary="Получить контакт по ID",
     *     tags={"Контакты"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID контакта",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Успешный ответ"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Контакт не найден"
     *     )
     * )
     */
    public function show(Contact $contact)
    {
        return response()->json($contact->load('tags'));
    }

    /**
     * @OA\Put(
     *     path="/api/contacts/{id}",
     *     summary="Обновить контакт",
     *     tags={"Контакты"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID контакта",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "email"},
     *             @OA\Property(property="name", type="string"),
     *             @OA\Property(property="email", type="string", format="email"),
     *             @OA\Property(property="phone", type="string"),
     *             @OA\Property(property="tags", type="array", @OA\Items(type="integer"))
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Контакт успешно обновлен"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Ошибка валидации"
     *     )
     * )
     */
    public function update(Request $request, Contact $contact)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:contacts,email,' . $contact->id,
            'phone' => 'nullable|string|max:20',
            'tags' => 'nullable|array',
            'tags.*' => 'exists:tags,id'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $contact->update([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
        ]);
        if ($request->has('tags')) {
            $contact->tags()->sync($request->tags);
        }

        // Отправляем уведомление в Telegram
        $this->sendTelegramNotification($contact, 'updated');

        return response()->json($contact->load('tags'));
    }

    /**
     * @OA\Delete(
     *     path="/api/contacts/{id}",
     *     summary="Удалить контакт",
     *     tags={"Контакты"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID контакта",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Контакт успешно удален"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Контакт не найден"
     *     )
     * )
     */
    public function destroy(Contact $contact)
    {
        $contact->delete();
        return response()->json(null, 204);
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