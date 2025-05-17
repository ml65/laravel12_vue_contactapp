<?php

namespace Tests\Unit\Services;

use App\Models\Contact;
use App\Models\Tag;
use App\Services\ContactNotificationFormatter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ContactNotificationFormatterTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_formats_created_message()
    {
        $contact = Contact::factory()->create([
            'name' => 'Иван',
            'email' => 'ivan@example.com',
            'phone' => '+7 (999) 123-45-67',
        ]);
        $formatter = new ContactNotificationFormatter();
        $msg = $formatter->formatCreated($contact);
        $this->assertStringContainsString('Контакт создан', $msg);
        $this->assertStringContainsString('Иван', $msg);
        $this->assertStringContainsString('ivan@example.com', $msg);
        $this->assertStringContainsString('+7 (999) 123-45-67', $msg);
    }

    #[Test]
    public function it_formats_updated_message_with_tags()
    {
        $contact = Contact::factory()->create([
            'name' => 'Петр',
            'email' => 'petr@example.com',
            'phone' => '+7 (999) 765-43-21',
        ]);
        $tag = Tag::factory()->create(['name' => 'VIP']);
        $contact->tags()->attach($tag);
        $formatter = new ContactNotificationFormatter();
        $msg = $formatter->formatUpdated($contact->fresh('tags'));
        $this->assertStringContainsString('Контакт обновлен', $msg);
        $this->assertStringContainsString('Петр', $msg);
        $this->assertStringContainsString('VIP', $msg);
    }
} 