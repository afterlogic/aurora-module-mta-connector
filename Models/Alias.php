<?php

namespace Aurora\Modules\MtaConnector\Models;

use Aurora\System\Classes\Model;

/**
 * Aurora\Modules\MtaConnector\Models\Alias
 *
 * @property integer $id
 * @property integer $id_acct
 * @property string $alias_name
 * @property string $alias_domain
 * @property string $alias_to
 *
 * @method static int count(string $columns = '*')
 * @method static \Illuminate\Database\Eloquent\Builder|Alias find(int|string $id, array|string $columns = ['*'])
 * @method static \Illuminate\Database\Eloquent\Builder|Alias findOrFail(int|string $id, mixed $id, Closure|array|string $columns = ['*'], Closure $callback = null)
 * @method static \Illuminate\Database\Eloquent\Builder|Alias first(array|string $columns = ['*'])
 * @method static \Illuminate\Database\Eloquent\Builder|Alias firstWhere(Closure|string|array|\Illuminate\Database\Query\Expression $column, mixed $operator = null, mixed $value = null, string $boolean = 'and')
 * @method static \Illuminate\Database\Eloquent\Builder|Alias newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Alias newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Alias query()
 * @method static \Illuminate\Database\Eloquent\Builder|Alias create(array $attributes)
 * @method static \Illuminate\Database\Eloquent\Builder|Alias where(Closure|string|array|\Illuminate\Database\Query\Expression $column, mixed $operator = null, mixed $value = null, string $boolean = 'and')
 * @method static \Illuminate\Database\Eloquent\Builder|Alias whereNotNull(string|array $columns, string $boolean = 'and')
 * @method static \Illuminate\Database\Eloquent\Builder|Alias whereIn(string $column, mixed $values, string $boolean = 'and', bool $not = false)
 * @mixin \Eloquent
 */
class Alias extends Model
{
    protected $table = 'awm_mailaliases';

    protected $foreignModel = Account::class;
    protected $foreignModelIdColumn = 'id_acct'; // Column that refers to an external table

    public $timestamps = false;

    protected $fillable = [
        'id',
        'id_acct',
        'alias_name',
        'alias_domain',
        'alias_to'
    ];
}
