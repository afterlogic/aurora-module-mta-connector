<?php

use Illuminate\Database\Schema\Blueprint;
use Aurora\System\Module\Manager;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Capsule\Manager as Capsule;

class DropMailingListColumnFromAccountsTable extends Migration
{
    public $schema = null;

    public function __construct()
    {
        Closure::bind(
            fn ($manager) => $manager->loadModule('MtaConnector'),
            null,
            Manager::class
        )(Manager::createInstance())->addDbConnection();

        $this->schema = Capsule::schema('mta');
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->schema->table('awm_accounts', function (Blueprint $table) {
            $table->dropColumn('mailing_list');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $this->schema->table('awm_accounts', function (Blueprint $table) {
            $table->boolean('mailing_list')->default(0);
        });
    }
}
