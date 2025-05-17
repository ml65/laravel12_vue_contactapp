<?php
namespace App\Http\Controllers\Api;

use App\Contracts\NotificationServiceInterface;
use App\Http\Controllers\Controller;
use App\Models\Contact;
use App\Services\ContactNotificationFormatter;
use App\Services\ContactValidationService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

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
    private $notificationService;
    private $validationService;
    private $notificationFormatter;

    public function __construct(
        NotificationServiceInterface $notificationService,
        ContactValidationService $validationService,
        ContactNotificationFormatter $notificationFormatter
    ) {
        $this->notificationService = $notificationService;
        $this->validationService = $validationService;
        $this->notificationFormatter = $notificationFormatter;
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
        try {
            $data = $this->validationService->validate($request->all());
            $contact = Contact::create($data);
            
            if ($request->has('tags')) {
                $contact->tags()->sync($request->tags);
            }

            $message = $this->notificationFormatter->formatCreated($contact);
            $this->notificationService->send($message);

            return response()->json($contact->load('tags'), 201);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        }
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
        try {
            $data = $this->validationService->validate($request->all(), $contact);
            $contact->update($data);
            
            if ($request->has('tags')) {
                $contact->tags()->sync($request->tags);
            }

            $message = $this->notificationFormatter->formatUpdated($contact);
            $this->notificationService->send($message);

            return response()->json($contact->load('tags'));
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        }
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
}