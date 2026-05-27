<?php

declare(strict_types=1);

namespace Lightit\Patients\Domain\Actions;

use Illuminate\Pagination\LengthAwarePaginator;
use Lightit\Users\Domain\Models\User;
use Spatie\QueryBuilder\QueryBuilder;

class ListPatientAction
{
    /**
     * @return LengthAwarePaginator<int, User>
     */
    public function execute(): LengthAwarePaginator
    {
        return QueryBuilder::for(User::class)
            ->allowedFilters(['name', 'email'])
            ->allowedSorts(['id', 'name', 'email'])
            ->orderByDesc('id')
            ->paginate();
    }
}
