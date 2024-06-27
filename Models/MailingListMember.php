<?php

namespace Aurora\Modules\MtaConnector\Models;

use Aurora\System\Classes\Model;

/**
 * Aurora\Modules\MtaConnector\Models\MailingListMember
 *
 * @property integer $id
 * @property integer $id_mailinglist
 * @property string $list_to
 *
 * @method static int count(string $columns = '*')
 * @method static \Illuminate\Database\Eloquent\Builder|MailingListMember find(int|string $id, array|string $columns = ['*'])
 * @method static \Illuminate\Database\Eloquent\Builder|MailingListMember findOrFail(int|string $id, mixed $id, Closure|array|string $columns = ['*'], Closure $callback = null)
 * @method static \Illuminate\Database\Eloquent\Builder|MailingListMember first(array|string $columns = ['*'])
 * @method static \Illuminate\Database\Eloquent\Builder|MailingListMember firstWhere(Closure|string|array|\Illuminate\Database\Query\Expression $column, mixed $operator = null, mixed $value = null, string $boolean = 'and')
 * @method static \Illuminate\Database\Eloquent\Builder|MailingListMember newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MailingListMember newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MailingListMember query()
 * @method static \Illuminate\Database\Eloquent\Builder|MailingListMember create(array $attributes)
 * @method static \Illuminate\Database\Eloquent\Builder|MailingListMember where(Closure|string|array|\Illuminate\Database\Query\Expression $column, mixed $operator = null, mixed $value = null, string $boolean = 'and')
 * @method static \Illuminate\Database\Eloquent\Builder|MailingListMember whereNotNull(string|array $columns, string $boolean = 'and')
 * @method static \Illuminate\Database\Eloquent\Builder|MailingListMember whereIn(string $column, mixed $values, string $boolean = 'and', bool $not = false)
 * @mixin \Eloquent
 */
class MailingListMember extends Model
{
    protected $table = 'awm_mailinglist_members';

    protected $connection = 'mta';

    protected $primaryKey = 'id';
    protected $foreignModel = MailingList::class;
    protected $foreignModelIdColumn = 'id_mailinglist'; // Column that refers to an external table

    public $timestamps = false;

    protected $fillable = [
        'id',
        'id_mailinglist',
        'list_to'
    ];
}
