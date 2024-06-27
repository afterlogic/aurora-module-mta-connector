<?php

use Aurora\Modules\MtaConnector\Models\Account;
use Aurora\Modules\MtaConnector\Models\MailingList;
use Aurora\Modules\MtaConnector\Models\MailingListMember;
use Illuminate\Database\Schema\Blueprint;
use Aurora\System\Module\Manager;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Capsule\Manager as Capsule;
use Symfony\Component\Console\Output\ConsoleOutput;

class MigrateMailinglistsTables extends Migration
{
    public $schema = null;

    public $output = null;

    protected function output($msg, $error = false)
    {
        if ($error) {
            $this->output->writeln('<error>' . $msg . '</error>');
        } else {
            $this->output->writeln('<info>' . $msg . '</info>');
        }
    }

    public function __construct()
    {
        Closure::bind(
            fn ($manager) => $manager->loadModule('MtaConnector'),
            null,
            Manager::class
        )(Manager::createInstance())->addDbConnection();

        $this->schema = Capsule::schema('mta');

        $this->output = new ConsoleOutput();
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // backup awm_mailinglists table data
        $this->output('copy `awm_mailinglists` data to `awm_mailinglists_bak` table');
        $this->schema->getConnection()->statement('CREATE TABLE awm_mailinglists_bak LIKE awm_mailinglists');
        $this->schema->getConnection()->statement('INSERT awm_mailinglists_bak SELECT * FROM awm_mailinglists');

        $this->schema->rename('awm_mailinglists', 'awm_mailinglist_members');
        $this->output('rename `awm_mailinglists` to `awm_mailinglist_members` table');

        if (!$this->schema->hasTable('awm_mailinglists')) {
            $this->output('create `awm_mailinglists` table');
            $this->schema->create('awm_mailinglists', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('id_domain')->nullable()->default(null);
                $table->string('name')->default('');
                $table->index('id_domain');

            });
        } else {
            $this->output('`awm_mailinglists` table already exists', true);
        }

        $this->schema->table('awm_mailinglist_members', function (Blueprint $table) {
            $this->output('added `id_mailinglist` collumn to `awm_mailinglist_members` table');
            $table->integer('id_mailinglist')->after('id')->index();

            $this->output('drop `id_acct` collumn from `awm_mailinglist_members` table');
            $table->dropColumn('id_acct');
        });

        $deleteIds = [];
        Account::where('mailing_list', true)->each(function ($account) use (&$deleteIds) {
            $this->output('create "' . $account->email . '" record in `mailing_list` table');
            $list = MailingList::create([
                'id_domain' => $account->id_domain,
                'name' => $account->email
            ]);
            if ($list) {
                $this->output('set `id_mailinglist` collumn value = "' . $list->id . '" for "' . $list->name . '" mailing list name');
                MailingListMember::where('list_name', $list->name)->update(['id_mailinglist' => $list->id]);
                $deleteIds[] = $account->id_acct;
            } else {
                $this->output('"' . $account->email . '" record not created in `mailing_list` table', true);
            }
        });

        $this->schema->table('awm_mailinglist_members', function (Blueprint $table) {
            $this->output('drop `list_name` collumn from `awm_mailinglist_members` table');
            $table->dropColumn('list_name');
        });

        // remove mailing list rows from accounts table
        if (count($deleteIds) > 0) {
            $this->output('delete mailing lists from `awm_accounts` table, ids: [' . implode(', ', $deleteIds) . ']');
            Account::whereIn('id_acct', $deleteIds)->delete();
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $this->output('drop `awm_mailinglist_members` table');
        $this->schema->dropIfExists('awm_mailinglist_members');

        $this->output('drop `awm_mailinglists` table');
        $this->schema->dropIfExists('awm_mailinglists');

        $this->output('rename `awm_mailinglists_bak` table to `awm_mailinglists`');
        $this->schema->rename('awm_mailinglists_bak', 'awm_mailinglists');
    }
}
