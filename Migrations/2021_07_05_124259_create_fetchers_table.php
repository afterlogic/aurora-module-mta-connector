<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Capsule\Manager as Capsule;

class CreateFetchersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Capsule::schema()->create('mail_fetchers', function (Blueprint $table) {
            $table->id('Id');
            $table->integer('IdUser')->default(0);
            $table->integer('IdAccount')->default(0);
            $table->boolean('IsEnabled')->default(true);
            $table->string('IncomingServer')->default('');
            $table->integer('IncomingPort')->default(0);
            $table->integer('IncomingMailSecurity')->default(\MailSo\Net\Enumerations\ConnectionSecurityType::NONE);
            $table->string('IncomingLogin')->default('');
            $table->string('IncomingPassword')->default('');
            $table->boolean('LeaveMessagesOnServer')->default(true);
            $table->string('Folder')->default('');
            $table->boolean('IsOutgoingEnabled')->default(false);
            $table->string('Name')->default('');
            $table->string('Email')->default('');
            $table->string('OutgoingServer')->default('');
            $table->integer('OutgoingPort')->default(25);
            $table->integer('OutgoingMailSecurity')->default(\MailSo\Net\Enumerations\ConnectionSecurityType::NONE);
            $table->boolean('OutgoingUseAuth')->default(true);
            $table->boolean('UseSignature')->default(false);
            $table->string('Signature')->default('');
            $table->boolean('IsLocked')->default(false);
            $table->integer('CheckInterval')->default(0);
            $table->integer('CheckLastTime')->default(0);
            $table->timestamp(\Aurora\System\Classes\Model::CREATED_AT)->nullable();
            $table->timestamp(\Aurora\System\Classes\Model::UPDATED_AT)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Capsule::schema()->dropIfExists('mail_fetchers');
    }
}
