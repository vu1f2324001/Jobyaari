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
            ['name' => 'IT Jobs', 'slug' => 'it-jobs'],
            ['name' => 'Government Jobs', 'slug' => 'government-jobs'],
            ['name' => 'Work from Home', 'slug' => 'work-from-home'],
            ['name' => 'Marketing', 'slug' => 'marketing'],
        ];

        foreach ($categories as $category) {
            DB::table('categories')->updateOrInsert(['slug' => $category['slug']], $category);
        }

        // Admin seed (for admin panel login)
        $this->call(AdminSeeder::class);
    }
}
