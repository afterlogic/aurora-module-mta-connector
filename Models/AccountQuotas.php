<?php

namespace Aurora\Modules\MtaConnector\Models;

use Aurora\System\Classes\Model;
use Aurora\Modules\Core\Models\Tenant;

/**
 * Aurora\Modules\MtaConnector\Models\AccountQuotas
 *
 * @property string $name
 * @property integer $mail_quota_usage_bytes
 * @property integer $quota_usage_messages

 * @method static int count(string $columns = '*')
 * @method static \Illuminate\Database\Eloquent\Builder|AccountQuotas find(int|string $id, array|string $columns = ['*'])
 * @method static \Illuminate\Database\Eloquent\Builder|AccountQuotas findOrFail(int|string $id, mixed $id, Closure|array|string $columns = ['*'], Closure $callback = null)
 * @method static \Illuminate\Database\Eloquent\Builder|AccountQuotas first(array|string $columns = ['*'])
 * @method static \Illuminate\Database\Eloquent\Builder|AccountQuotas firstWhere(Closure|string|array|\Illuminate\Database\Query\Expression $column, mixed $operator = null, mixed $value = null, string $boolean = 'and')
 * @method static \Illuminate\Database\Eloquent\Builder|AccountQuotas newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AccountQuotas newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AccountQuotas query()
 * @method static \Illuminate\Database\Eloquent\Builder|AccountQuotas create(array $attributes)
 * @method static \Illuminate\Database\Eloquent\Builder|AccountQuotas where(Closure|string|array|\Illuminate\Database\Query\Expression $column, mixed $operator = null, mixed $value = null, string $boolean = 'and')
 * @method static \Illuminate\Database\Eloquent\Builder|AccountQuotas whereNotNull(string|array $columns, string $boolean = 'and')
 * @method static \Illuminate\Database\Eloquent\Builder|AccountQuotas whereIn(string $column, mixed $values, string $boolean = 'and', bool $not = false)
 * @mixin \Eloquent
 */
class AccountQuotas extends Model
{
    protected $table = 'awm_account_quotas';

    public $timestamps = false;

    protected $fillable = [
        'name',
        'mail_quota_usage_bytes',
        'quota_usage_messages'
    ];
}
