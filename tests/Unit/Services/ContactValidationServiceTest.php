<?php

namespace Tests\Unit\Services;

use App\Models\Contact;
use App\Services\ContactValidationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class ContactValidationServiceTest extends TestCase
{
    use RefreshDatabase;

    protected ContactValidationService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new ContactValidationService();
    }

    /** @test */
    public function it_validates_contact_creation_successfully()
    {
        $data = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'phone' => '+7 (999) 123-45-67',
        ];
        $validated = $this->service->validate($data);
        $this->assertEquals($data['name'], $validated['name']);
        $this->assertEquals($data['email'], $validated['email']);
        $this->assertEquals($data['phone'], $validated['phone']);
    }

    /** @test */
    public function it_fails_if_email_is_not_unique()
    {
        Contact::factory()->create(['email' => 'test@example.com']);
        $data = [
            'name' => 'Another',
            'email' => 'test@example.com',
        ];
        $this->expectException(ValidationException::class);
        $this->service->validate($data);
    }

    /** @test */
    public function it_fails_if_required_fields_are_missing()
    {
        $data = [];
        $this->expectException(ValidationException::class);
        $this->service->validate($data);
    }

    /** @test */
    public function it_allows_update_with_same_email()
    {
        $contact = Contact::factory()->create(['email' => 'test@example.com']);
        $data = [
            'name' => 'Updated',
            'email' => 'test@example.com',
        ];
        $validated = $this->service->validate($data, $contact);
        $this->assertEquals('test@example.com', $validated['email']);
    }
} 