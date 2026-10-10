<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $this->targetSizes()->update(['is_active' => false]);
    }

    public function down(): void
    {
        $this->targetSizes()->update(['is_active' => true]);
    }

    private function targetSizes(): Builder
    {
        return DB::table('sizes')->where(function ($query) {
            $query->where(function ($query) {
                $query->where('ml', 22)->where('name', 'small');
            })->orWhere(function ($query) {
                $query->where('ml', 30)->where('name', 'Small');
            })->orWhere(function ($query) {
                $query->where('ml', 100)->where('name', 'Large');
            });
        });
    }
};
