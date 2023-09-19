<?php

namespace Aurora\Modules\MtaConnector\Models;

use Aurora\System\Classes\Model;
use Aurora\Modules\Core\Models\Tenant;

/**
 * Aurora\Modules\MtaConnector\Models\Domain
 *
 * @property integer $id_domain
 * @property integer $id_tenant
 * @property string $name

 * @method static int count(string $columns = '*')
 * @method static \Illuminate\Database\Eloquent\Builder|Domain find(int|string $id, array|string $columns = ['*'])
 * @method static \Illuminate\Database\Eloquent\Builder|Domain findOrFail(int|string $id, mixed $id, Closure|array|string $columns = ['*'], Closure $callback = null)
 * @method static \Illuminate\Database\Eloquent\Builder|Domain first(array|string $columns = ['*'])
 * @method static \Illuminate\Database\Eloquent\Builder|Domain firstWhere(Closure|string|array|\Illuminate\Database\Query\Expression $column, mixed $operator = null, mixed $value = null, string $boolean = 'and')
 * @method static \Illuminate\Database\Eloquent\Builder|Domain newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Domain newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Domain query()
 * @method static \Illuminate\Database\Eloquent\Builder|Domain create(array $attributes)
 * @method static \Illuminate\Database\Eloquent\Builder|Domain where(Closure|string|array|\Illuminate\Database\Query\Expression $column, mixed $operator = null, mixed $value = null, string $boolean = 'and')
 * @method static \Illuminate\Database\Eloquent\Builder|Domain whereNotNull(string|array $columns, string $boolean = 'and')
 * @method static \Illuminate\Database\Eloquent\Builder|Domain whereIn(string $column, mixed $values, string $boolean = 'and', bool $not = false)
 * @mixin \Eloquent
 */
class Domain extends Model
{
    protected $table = 'awm_domains';

    protected $primaryKey = 'id_acct';
    protected $foreignModel = Tenant::class;
    protected $foreignModelIdColumn = 'id_tenant'; // Column that refers to an external table

    public $timestamps = false;

    protected $fillable = [
        'id_domain',
        'id_tenant',
        'name',
    ];

    // protected $appends = [
    //     'count'
    // ];

    // public function getCountAttribute() {
    //     return Account::where('id_domain', $this->id_domain)
    //         ->where('mailing_list', false)->count();
    // }
}
