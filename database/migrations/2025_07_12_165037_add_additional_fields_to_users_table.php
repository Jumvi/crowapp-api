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
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone')->nullable()->after('email');
            $table->string('location')->nullable()->after('phone');
            $table->string('userType')->nullable()->after('location');
            $table->decimal('investmentTotal', 15, 2)->nullable()->after('userType');
            $table->decimal('projectSupported', 8, 2)->nullable()->after('investmentTotal');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'phone',
                'location',
                'userType',
                'investmentTotal',
                'projectSupported'
            ]);
        });
    }
};
