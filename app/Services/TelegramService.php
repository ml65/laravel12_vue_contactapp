<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class TelegramService
{
    protected $botToken;
    protected $chatIds;
    protected $enabled;
    protected $logFile;

    public function __construct()
    {
        $this->botToken = config('telegram.bot_token');
        $this->chatIds = config('telegram.chat_ids');
        $this->enabled = config('telegram.enabled');
        
        // Используем storage_path вместо /tmp
        $this->logFile = storage_path('logs/telegram.log');
        
        // Создаем директорию для логов, если она не существует
        if (!file_exists(storage_path('logs'))) {
            mkdir(storage_path('logs'), 0755, true);
        }
        
        $this->log("TelegramService initialized", [
            'enabled' => $this->enabled,
            'bot_token_exists' => !empty($this->botToken),
            'chat_ids_count' => count($this->chatIds),
            'log_file' => $this->logFile
        ]);
    }

    public function sendMessage(string $message): bool
    {
        $this->log("Attempting to send message", ['message_length' => strlen($message)]);

        if (!$this->enabled) {
            $this->log("Telegram notifications are disabled");
            return false;
        }

        if (empty($this->botToken)) {
            $this->log("Bot token is empty");
            return false;
        }

        if (empty($this->chatIds)) {
            $this->log("No chat IDs configured");
            return false;
        }

        $success = true;
        foreach ($this->chatIds as $chatId) {
            try {
                $this->log("Sending message to chat", ['chat_id' => $chatId]);
                
                $response = Http::post("https://api.telegram.org/bot{$this->botToken}/sendMessage", [
                    'chat_id' => trim($chatId),
                    'text' => $message,
                    'parse_mode' => 'HTML'
                ]);

                if (!$response->successful()) {
                    $this->log("Failed to send message", [
                        'chat_id' => $chatId,
                        'status' => $response->status(),
                        'response' => $response->json()
                    ]);
                    $success = false;
                } else {
                    $this->log("Message sent successfully", [
                        'chat_id' => $chatId,
                        'response' => $response->json()
                    ]);
                }
            } catch (\Exception $e) {
                $this->log("Exception while sending message", [
                    'chat_id' => $chatId,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                $success = false;
            }
        }

        return $success;
    }

    protected function log(string $message, array $context = []): void
    {
        try {
            $logMessage = date('Y-m-d H:i:s') . " - " . $message;
            if (!empty($context)) {
                $logMessage .= " - Context: " . json_encode($context, JSON_UNESCAPED_UNICODE);
            }
            
            // Записываем в файл логов
            if (is_writable(dirname($this->logFile))) {
                file_put_contents($this->logFile, $logMessage . PHP_EOL, FILE_APPEND);
            } else {
                Log::error('Cannot write to telegram log file', [
                    'path' => $this->logFile,
                    'permissions' => decoct(fileperms(dirname($this->logFile)))
                ]);
            }
            
            // Записываем в канал telegram
            Log::channel('telegram')->info($message, $context);
            
            // Дублируем в основной лог для отладки
            Log::info($message, $context);
        } catch (\Exception $e) {
            Log::error('Error writing to telegram log', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
} 