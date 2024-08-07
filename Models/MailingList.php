<?php

namespace Aurora\Modules\MtaConnector\Models;

use Aurora\System\Classes\Model;

/**
 * Aurora\Modules\MtaConnector\Models\MailingList
 *
 * @property integer $id
 * @property integer $id_domain
 * @property string $name

 *
 * @method static int count(string $columns = '*')
 * @method static \Illuminate\Database\Eloquent\Builder|MailingList find(int|string $id, array|string $columns = ['*'])
 * @method static \Illuminate\Database\Eloquent\Builder|MailingList findOrFail(int|string $id, mixed $id, Closure|array|string $columns = ['*'], Closure $callback = null)
 * @method static \Illuminate\Database\Eloquent\Builder|MailingList first(array|string $columns = ['*'])
 * @method static \Illuminate\Database\Eloquent\Builder|MailingList firstWhere(Closure|string|array|\Illuminate\Database\Query\Expression $column, mixed $operator = null, mixed $value = null, string $boolean = 'and')
 * @method static \Illuminate\Database\Eloquent\Builder|MailingList newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MailingList newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MailingList query()
 * @method static \Illuminate\Database\Eloquent\Builder|MailingList create(array $attributes)
 * @method static \Illuminate\Database\Eloquent\Builder|MailingList where(Closure|string|array|\Illuminate\Database\Query\Expression $column, mixed $operator = null, mixed $value = null, string $boolean = 'and')
 * @method static \Illuminate\Database\Eloquent\Builder|MailingList whereNotNull(string|array $columns, string $boolean = 'and')
 * @method static \Illuminate\Database\Eloquent\Builder|MailingList whereIn(string $column, mixed $values, string $boolean = 'and', bool $not = false)
 * @mixin \Eloquent
 */
class MailingList extends Model
{
    protected $table = 'awm_mailinglists';

    protected $connection = 'mta';

    protected $primaryKey = 'id';
    protected $foreignModel = Domain::class;
    protected $foreignModelIdColumn = 'id_domain'; // Column that refers to an external table

    public $timestamps = false;

    protected $fillable = [
        'id',
        'id_domain',
        'name',
    ];
}
