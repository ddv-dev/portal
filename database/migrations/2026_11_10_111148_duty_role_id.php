<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('permission', function (Blueprint $table) {
            $table->foreignId('duty_role_id')
                ->nullable()
                ->after('description')
                ->constrained('duty_role')
                ->nullOnDelete();
        });

        // Проставляем связь для существующих прав по имени роли
        $map = [
            'duty_manage_admin'            => 'Администратор',
            'duty_manage_doc'              => 'Врач',
            'duty_manage_anesthesiologist' => 'Анестезиолог',
            'duty_manage_oper_nurse'       => 'Операционная мед.сестра',
        ];

        foreach ($map as $code => $roleName) {
            $roleId = DB::table('duty_role')->where('name', $roleName)->value('id');
            if ($roleId) {
                DB::table('permission')
                    ->where('permission', $code)
                    ->update(['duty_role_id' => $roleId]);
            }
        }

        // duty_manage_all остаётся с duty_role_id = NULL (значит «все роли»)
    }

    public function down(): void
    {
        Schema::table('permission', function (Blueprint $table) {
            $table->dropForeign(['duty_role_id']);
            $table->dropColumn('duty_role_id');
        });
    }
};