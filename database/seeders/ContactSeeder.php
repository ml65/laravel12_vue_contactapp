<?php

namespace Database\Seeders;

use App\Models\Contact;
use App\Models\Tag;
use Illuminate\Database\Seeder;
use Faker\Factory as FakerFactory;

class ContactSeeder extends Seeder
{
    public function run(): void
    {
        $faker = FakerFactory::create('ru_RU');
        $tags = Tag::all()->pluck('id')->toArray();

        for ($i = 0; $i < 100; $i++) {
            $contact = Contact::create([
                'name' => $faker->lastName . ' ' . $faker->firstName,
                'email' => $faker->unique()->safeEmail,
                'phone' => '+79' . $faker->numberBetween(100000000, 999999999),
            ]);

            // Рандомно выбираем от 1 до 3 тегов
            $tagIds = $faker->randomElements($tags, $faker->numberBetween(1, min(3, count($tags))));
            $contact->tags()->sync($tagIds);
        }
    }
} 