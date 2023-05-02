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
        Schema::table('users', function(Blueprint $table) {
            $table->bigInteger('tenant_id')->after('id');
            $table->dropColumn('name');
            $table->string('username')->after('tenant_id');
            $table->string('first_name')->after('username');
            $table->string('last_name')->after('first_name');
            $table->boolean('active')->default(true);
            $table->dropUnique('users_email_unique');
            $table->unique(['tenant_id','username']);
            $table->unique(['tenant_id','email']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique('users_tenant_id_username_unique');
            $table->dropUnique('users_tenant_id_email_unique');
            $table->dropColumn('username');
            $table->dropColumn('first_name');
            $table->dropColumn('last_name');
            $table->dropColumn('tenant_id');
            $table->string('name');
            $table->unique('email');
        });
    }
};
