<?php

use App\Models\Size;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        foreach ([10, 30, 50, 100, 250, 500, 1000] as $millilitres) {
            Size::firstOrCreate(
                ['ml' => $millilitres],
                ['name' => $millilitres.' ml', 'is_active' => true],
            );
        }
    }

    public function down(): void
    {
        // Preserve size records because products may now reference them.
    }
};
