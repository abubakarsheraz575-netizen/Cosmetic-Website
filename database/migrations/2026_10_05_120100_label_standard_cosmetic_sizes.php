<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('sizes')->where('ml', 30)->update(['name' => 'Small']);
        DB::table('sizes')->where('ml', 50)->update(['name' => 'Medium']);
        DB::table('sizes')->where('ml', 100)->update(['name' => 'Large']);
    }

    public function down(): void
    {
        DB::table('sizes')->where('ml', 30)->update(['name' => '30 ml']);
        DB::table('sizes')->where('ml', 50)->update(['name' => '50 ml']);
        DB::table('sizes')->where('ml', 100)->update(['name' => '100 ml']);
    }
};
