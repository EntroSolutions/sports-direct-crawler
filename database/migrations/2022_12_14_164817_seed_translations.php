<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class SeedTranslations extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $columns = [];

        if (($open = fopen(base_path('Translated product names - Mymall.csv'), "r")) !== FALSE) {

            $i = 0;
            while (($data = fgetcsv($open, 1000, ",")) !== FALSE) {

                if ($i == 0) {
                    $columns = array_flip($data);
                } else {

                    $translations = [
                        'en' => $data[$columns['Английски']],
                        'bg' => $data[$columns['Български']],
                        'el' => $data[$columns['Гръцки']],
                        'ro' => $data[$columns['Румънски']],
                    ];

                    \App\Models\Translate::create($translations);
                }

                $i++;
            }

            fclose($open);
        }

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        \Illuminate\Support\Facades\DB::table('translations')->truncate();
    }
}
