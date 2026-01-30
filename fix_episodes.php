<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

if (Schema::hasColumn('episodes', 'anime_slug')) {
    echo "Fixing episodes table: anime_slug -> anime_id...\n";
    
    Schema::table('episodes', function ($table) {
        if (!Schema::hasColumn('episodes', 'anime_id')) {
            echo "Adding anime_id column...\n";
            $table->integer('anime_id')->unsigned()->nullable()->after('id');
        }
    });

    echo "Updating anime_id based on anime_slug...\n";
    DB::statement("UPDATE episodes e JOIN animes a ON e.anime_slug = a.slug SET e.anime_id = a.id");

    Schema::table('episodes', function ($table) {
        echo "Dropping old foreign key and column...\n";
        try {
            $table->dropForeign('fk_episodes_anime_slug');
        } catch (\Exception $e) {
            echo "Foreign key fk_episodes_anime_slug not found, trying episodes_anime_slug_foreign...\n";
            try {
                $table->dropForeign('episodes_anime_slug_foreign');
            } catch (\Exception $e2) {
                echo "No foreign key found for anime_slug.\n";
            }
        }
        
        $table->dropColumn('anime_slug');
        // Gunakan integer biasa (signed) untuk mencocokkan tipe data id di animes
        $table->integer('anime_id')->nullable(false)->change();
        
        echo "Adding new foreign key to anime_id...\n";
        try {
            // Gunakan DB::statement untuk menonaktifkan checks sementara jika diperlukan,
            // tapi di sini kita pastikan tipe data cocok dulu.
            $table->foreign('anime_id')->references('id')->on('animes')->onDelete('cascade')->onUpdate('cascade');
        } catch (\Exception $e) {
            echo "Foreign key failed: " . $e->getMessage() . "\n";
        }
    });

    echo "Episodes table fixed successfully.\n";
} else {
    echo "Episodes table already fixed (anime_slug not found).\n";
}