<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('classes', 'homeroom_user_id')) {
            Schema::table('classes', function (Blueprint $table) {
                $table->foreignId('homeroom_user_id')
                    ->nullable()
                    ->constrained('users')
                    ->nullOnDelete();
            });
        }

        // Ambil pasangan kelas dan user wali kelas
        $homerooms = DB::table('classes as c')
            ->join('teachers as t', 't.id', '=', 'c.homeroom_teacher_id')
            ->join('users as u', function ($join) {
                $join->on('u.phone_number', '=', 't.phone')
                    ->where('u.role', '=', 'admin_walas');
            })
            ->whereNull('c.homeroom_user_id')
            ->select('c.id as class_id', 'u.id as user_id')
            ->get();

        // Perbarui homeroom_user_id setiap kelas
        foreach ($homerooms as $homeroom) {
            DB::table('classes')
                ->where('id', $homeroom->class_id)
                ->update([
                    'homeroom_user_id' => $homeroom->user_id,
                ]);
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('classes', 'homeroom_user_id')) {
            Schema::table('classes', function (Blueprint $table) {
                $table->dropConstrainedForeignId('homeroom_user_id');
            });
        }
    }
};