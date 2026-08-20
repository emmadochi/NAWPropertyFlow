<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'is_system',
    ];

    protected $casts = [
        'is_system' => 'boolean',
    ];

    /**
     * Users assigned to this role.
     */
    public function users()
    {
        return $this->hasMany(User::class, 'role_id');
    }

    /**
     * Permissions granted to this role.
     */
    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'role_permission');
    }

    /**
     * Check if this role has a specific permission.
     */
    public function hasPermission(string $permissionSlug): bool
    {
        // Root super_admin has all permissions automatically
        if ($this->slug === 'super_admin') {
            return true;
        }

        return $this->permissions->contains('slug', $permissionSlug);
    }

    /**
     * Grant a permission to this role.
     */
    public function givePermissionTo(string|Permission $permission): self
    {
        if (is_string($permission)) {
            $permission = Permission::where('slug', $permission)->firstOrFail();
        }

        $this->permissions()->syncWithoutDetaching([$permission->id]);
        return $this;
    }

    /**
     * Revoke a permission from this role.
     */
    public function revokePermissionTo(string|Permission $permission): self
    {
        if (is_string($permission)) {
            $permission = Permission::where('slug', $permission)->firstOrFail();
        }

        $this->permissions()->detach($permission->id);
        return $this;
    }

    /**
     * Sync permissions for this role.
     */
    public function syncPermissions(array $permissionSlugs): self
    {
        $permissionIds = Permission::whereIn('slug', $permissionSlugs)->pluck('id');
        $this->permissions()->sync($permissionIds);
        return $this;
    }
}
