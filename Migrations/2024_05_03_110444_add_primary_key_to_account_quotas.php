<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Capsule\Manager as Capsule;

class AddPrimaryKeyToAccountQuotas extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Capsule::connection()->statement(
            "ALTER TABLE `awm_account_quotas` ADD `id` INT NOT NULL PRIMARY KEY AUTO_INCREMENT;"
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Capsule::connection()->statement(
            "ALTER TABLE user_customer_permission DROP PRIMARY KEY;"
        );
    }
}
