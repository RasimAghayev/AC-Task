<?php

use App\Http\Controllers\Tasks\Enums\TaskPriority;
use App\Http\Controllers\Tasks\Enums\TaskRepeatTypeEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->enum('priority', array_column(TaskPriority::cases(), 'value'))
                ->default(TaskPriority::MEDIUM->value)
                ->after('status');

            $table->enum('repeat_type', array_column(TaskRepeatTypeEnum::cases(), 'value'))
                ->nullable()
                ->after('priority');

            $table->date('repeat_end_date')
                ->nullable()
                ->after('repeat_type');
            $table->json('tags')
                ->nullable()
                ->after('repeat_end_date');
            $table->index(['user_id', 'status']);
            $table->index(['priority', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'status']);
            $table->dropIndex(['priority', 'status']);
            $table->dropColumn([
                'priority',
                'repeat_type',
                'repeat_end_date',
                'tags'
            ]);
        });
    }
};