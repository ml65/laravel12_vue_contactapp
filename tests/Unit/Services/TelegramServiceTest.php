<?php

namespace Tests\Unit\Services;

use App\Services\TelegramService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class TelegramServiceTest extends TestCase
{
    /** @test */
    public function it_returns_false_if_disabled()
    {
        Config::set('telegram.enabled', false);
        $service = new TelegramService();
        $this->assertFalse($service->send('Любое сообщение'));
    }
} 