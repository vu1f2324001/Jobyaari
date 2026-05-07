<?php

namespace Database\Seeders;


use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Default Categories add kara
        $categories = [
            ['name' => 'Admit Card', 'slug' => 'admit-card'],
            ['name' => 'Answer Key', 'slug' => 'answer-key'],
            ['name' => 'Calender', 'slug' => 'calender'],
            ['name' => 'EXAM DATE', 'slug' => 'exam-date'],
            ['name' => 'Information', 'slug' => 'information'],
            ['name' => 'Jobs', 'slug' => 'jobs'],
            ['name' => 'Result', 'slug' => 'result'],
        ];

        foreach ($categories as $category) {
            DB::table('categories')->updateOrInsert(['slug' => $category['slug']], $category);
        }

        // Admin seed (for admin panel login)
        $this->call(AdminSeeder::class);
    }
}
