<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSyncColumnsToCategoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dateTime('sync_started_at')->nullable()->default(null)->after('url');
            $table->dateTime('sync_ended_at')->nullable()->default(null)->after('url');
            $table->text('sync_message')->nullable()->default(null)->after('url');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('categories', function (Blueprint $table) {
            if(Schema::hasColumn('categories', 'sync_started_at')){
                $table->dropColumn('sync_started_at');
            }

            if(Schema::hasColumn('categories', 'sync_ended_at')){
                $table->dropColumn('sync_ended_at');
            }

            if(Schema::hasColumn('categories', 'sync_message')){
                $table->dropColumn('sync_message');
            }
        });
    }
}
