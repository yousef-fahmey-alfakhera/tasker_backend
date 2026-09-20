<?php

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
        $tableNames = config('permission.table_names');
        $permissionTable = $tableNames['permissions'] ?? 'permissions';

        Schema::table($permissionTable, function (Blueprint $table) {
            $table->string('name_ar')->nullable()->after('name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tableNames = config('permission.table_names');
        $permissionTable = $tableNames['permissions'] ?? 'permissions';

        Schema::table($permissionTable, function (Blueprint $table) {
            $table->dropColumn('name_ar');
        });
    }
};
