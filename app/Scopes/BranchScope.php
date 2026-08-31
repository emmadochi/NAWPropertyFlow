<?php

namespace App\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;

class BranchScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     */
    public function apply(Builder $builder, Model $model)
    {
        // Only apply if user is authenticated
        if (!Auth::check()) {
            return;
        }

        $user = Auth::user();

        // Super Admin / Company Admin can see everything or filter by selected branch
        if ($user->isSuperAdmin() || $user->isCompanyAdmin()) {
            if (session()->has('selected_branch_id') && session('selected_branch_id') !== 'all') {
                $builder->where(function($q) use ($model) {
                    $q->where($model->getTable() . '.branch_id', session('selected_branch_id'))
                      ->orWhereNull($model->getTable() . '.branch_id');
                });
            }
        } else {
            // Staff/Officers are scoped to their branch plus unassigned/corporate records
            if (!empty($user->branch_id)) {
                $builder->where(function($q) use ($model, $user) {
                    $q->where($model->getTable() . '.branch_id', $user->branch_id)
                      ->orWhereNull($model->getTable() . '.branch_id');
                });
            }
        }
    }
}
