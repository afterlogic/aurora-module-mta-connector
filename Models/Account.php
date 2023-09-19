<?php

namespace Aurora\Modules\MtaConnector\Models;

use Aurora\System\Classes\Model;
use Aurora\Modules\Core\Models\User;

/**
 * Aurora\Modules\MtaConnector\Models\Account
 *
 * @property integer $id_acct
 * @property integer $id_user
 * @property integer $id_domain
 * @property bool $deleted
 * @property integer $mail_quota_kb
 * @property string $email
 * @property string $password
 * @property string $mailing_list
 *
 * @method static int count(string $columns = '*')
 * @method static \Illuminate\Database\Eloquent\Builder|Account find(int|string $id, array|string $columns = ['*'])
 * @method static \Illuminate\Database\Eloquent\Builder|Account findOrFail(int|string $id, mixed $id, Closure|array|string $columns = ['*'], Closure $callback = null)
 * @method static \Illuminate\Database\Eloquent\Builder|Account first(array|string $columns = ['*'])
 * @method static \Illuminate\Database\Eloquent\Builder|Account firstWhere(Closure|string|array|\Illuminate\Database\Query\Expression $column, mixed $operator = null, mixed $value = null, string $boolean = 'and')
 * @method static \Illuminate\Database\Eloquent\Builder|Account newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Account newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Account query()
 * @method static \Illuminate\Database\Eloquent\Builder|Account create(array $attributes)
 * @method static \Illuminate\Database\Eloquent\Builder|Account where(Closure|string|array|\Illuminate\Database\Query\Expression $column, mixed $operator = null, mixed $value = null, string $boolean = 'and')
 * @method static \Illuminate\Database\Eloquent\Builder|Account whereNotNull(string|array $columns, string $boolean = 'and')
 * @method static \Illuminate\Database\Eloquent\Builder|Account whereIn(string $column, mixed $values, string $boolean = 'and', bool $not = false)
 * @mixin \Eloquent
 */
class Account extends Model
{
    protected $table = 'awm_accounts';

    protected $primaryKey = 'id_acct';
    protected $foreignModel = User::class;
    protected $foreignModelIdColumn = 'id_user'; // Column that refers to an external table

    public $timestamps = false;

    protected $fillable = [
        'id_acct',
        'id_user',
        'id_domain',
        'deleted',
        'mail_quota_kb',
        'email',
        'password',
        'mailing_list'
    ];
}
