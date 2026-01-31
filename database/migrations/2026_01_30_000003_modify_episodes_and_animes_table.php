<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Tambahkan kolom anime_id ke tabel episodes jika belum ada
        if (! Schema::hasColumn('episodes', 'anime_id')) {
            Schema::table('episodes', function (Blueprint $table) {
                $table->integer('anime_id')->nullable()->after('id');
            });
        }

        // 2. Isi anime_id berdasarkan anime_slug
        DB::statement('UPDATE episodes e JOIN animes a ON e.anime_slug = a.slug SET e.anime_id = a.id');

        // 3. Hapus Foreign Key lama dan kolom anime_slug
        Schema::table('episodes', function (Blueprint $table) {
            // Cek apakah kolom anime_slug masih ada sebelum menghapus foreign key dan kolomnya
            if (Schema::hasColumn('episodes', 'anime_slug')) {
                // Menggunakan nama constraint yang ada di schema.sql
                // Kita gunakan try-catch atau pengecekan manual karena dropForeign bisa gagal jika sudah dihapus
                try {
                    $table->dropForeign('fk_episodes_anime_slug');
                } catch (\Exception $e) {
                    // Ignore if already dropped
                }
                $table->dropColumn('anime_slug');
            }

            $table->integer('anime_id')->nullable(false)->change();

            // Tambahkan foreign key jika belum ada
            // Laravel tidak punya hasForeignKey, jadi kita bungkus dalam try-catch
            try {
                $table->foreign('anime_id')->references('id')->on('animes')->onDelete('cascade')->onUpdate('cascade');
            } catch (\Exception $e) {
                // Ignore if already exists
            }

            $table->index('anime_id');
        });

        // 3.5. Update tabel anime_genres: anime_slug -> anime_id jika belum dilakukan
        if (! Schema::hasColumn('anime_genres', 'anime_id')) {
            Schema::table('anime_genres', function (Blueprint $table) {
                $table->integer('anime_id')->unsigned()->nullable()->after('anime_slug');
            });

            DB::statement('UPDATE anime_genres ag JOIN animes a ON ag.anime_slug = a.slug SET ag.anime_id = a.id');

            Schema::table('anime_genres', function (Blueprint $table) {
                try {
                    $table->dropForeign('fk_anime_genres_anime_slug');
                } catch (\Exception $e) {
                }

                $table->dropPrimary(['anime_slug', 'genre_slug']);
                $table->dropColumn('anime_slug');
                $table->integer('anime_id')->unsigned()->nullable(false)->change();
                $table->primary(['anime_id', 'genre_slug']);
                $table->foreign('anime_id')->references('id')->on('animes')->onDelete('cascade')->onUpdate('cascade');
            });
        }

        // 4. Update slug di tabel animes: lowercase title dan hapus karakter aneh
        $animes = DB::table('animes')->get();
        foreach ($animes as $anime) {
            $newSlug = Str::slug($anime->title);

            // Cek apakah slug sudah ada (untuk menghindari duplikat jika title mirip setelah dislugify)
            $exists = DB::table('animes')->where('slug', $newSlug)->where('id', '!=', $anime->id)->exists();
            if ($exists) {
                $newSlug = $newSlug.'-'.$anime->id;
            }

            DB::table('animes')->where('id', $anime->id)->update(['slug' => $newSlug]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('episodes', function (Blueprint $table) {
            $table->string('anime_slug')->after('id');
        });

        DB::statement('UPDATE episodes e JOIN animes a ON e.anime_id = a.id SET e.anime_slug = a.slug');

        Schema::table('episodes', function (Blueprint $table) {
            $table->dropForeign(['anime_id']);
            $table->dropColumn('anime_id');
            $table->foreign('anime_slug', 'fk_episodes_anime_slug')->references('slug')->on('animes')->onDelete('cascade')->onUpdate('cascade');
        });

        if (Schema::hasColumn('anime_genres', 'anime_id')) {
            Schema::table('anime_genres', function (Blueprint $table) {
                $table->string('anime_slug')->after('anime_id');
            });

            DB::statement('UPDATE anime_genres ag JOIN animes a ON ag.anime_id = a.id SET ag.anime_slug = a.slug');

            Schema::table('anime_genres', function (Blueprint $table) {
                try {
                    $table->dropForeign(['anime_id']);
                } catch (\Exception $e) {
                }

                $table->dropPrimary(['anime_id', 'genre_slug']);
                $table->dropColumn('anime_id');
                $table->primary(['anime_slug', 'genre_slug']);
                $table->foreign('anime_slug', 'fk_anime_genres_anime_slug')->references('slug')->on('animes')->onDelete('cascade')->onUpdate('cascade');
            });
        }
    }
};
