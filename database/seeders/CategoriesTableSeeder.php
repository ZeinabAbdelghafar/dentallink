<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategoriesTableSeeder extends Seeder
{
    public function run()
    {
        $categories = ['Units', 'Handpieces', 'Instruments', 'Supplies', 'Lab Equipment'];
        
        foreach ($categories as $category) {
            Category::create([
                'name' => $category,
                'slug' => \Str::slug($category)
            ]);
        }
    }
}