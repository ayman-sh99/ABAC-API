<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('role_permissions', function (Blueprint $table) {
            // Store ABAC conditions as JSON e.g. {"owner_only": true, "allowed_statuses": ["draft"]}
            // null means no conditions → plain RBAC allow
            $table->json('conditions')->nullable()->after('permission_id');
        });
    }

    public function down(): void
    {
        Schema::table('role_permissions', function (Blueprint $table) {
            $table->dropColumn('conditions');
        });
    }
};
