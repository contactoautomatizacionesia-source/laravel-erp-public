<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Folder extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'parent_id',
        'owner_id',
        'type',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($folder) {
            if (empty($folder->slug)) {
                $folder->slug = Str::slug($folder->name) . '-' . Str::random(6);
            }
        });
    }

    /**
     * Parent folder relationship (self-referencing)
     */
    public function parent()
    {
        return $this->belongsTo(Folder::class, 'parent_id');
    }

    /**
     * Children folders relationship (self-referencing)
     */
    public function children()
    {
        return $this->hasMany(Folder::class, 'parent_id');
    }

    /**
     * Recursive children (nested tree)
     */
    public function childrenRecursive()
    {
        return $this->children()->with('childrenRecursive');
    }

    /**
     * Owner of the folder
     */
    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    /**
     * Files in this folder
     */
    public function files()
    {
        return $this->hasMany(FolderFile::class);
    }

    /**
     * Users with access to this folder
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'folder_user')
            ->withPivot('permission')
            ->withTimestamps();
    }

    /**
     * Get the master folder
     */
    public static function getMasterFolder()
    {
        return self::where('type', 'master')->whereNull('parent_id')->first();
    }

    /**
     * Create or get the master folder
     */
    public static function getOrCreateMasterFolder()
    {
        $master = self::getMasterFolder();

        if (!$master) {
            $master = self::create([
                'name' => 'Master',
                'slug' => 'master',
                'type' => 'master',
                'description' => 'Master folder for all user folders',
            ]);
        }

        return $master;
    }

    /**
     * Create a user folder inside the standard hierarchy: Master → Empresarios → User.
     * Prefer DigitalFolderRepository::ensureUserFolder() when the repository is available.
     */
    public static function createUserFolder(User $user)
    {
        $master = self::getOrCreateMasterFolder();

        $empresarios = self::firstOrCreate(
            ['parent_id' => $master->id, 'name' => 'Empresarios'],
            ['type' => 'regular']
        );

        $existingFolder = self::where('owner_id', $user->id)
            ->where('type', 'user')
            ->first();

        if ($existingFolder) {
            return $existingFolder;
        }

        $folder = self::create([
            'name'        => $user->name,
            'slug'        => 'user-' . $user->id . '-' . Str::random(6),
            'parent_id'   => $empresarios->id,
            'owner_id'    => $user->id,
            'type'        => 'user',
            'description' => 'Personal folder for ' . $user->name,
        ]);

        $folder->users()->attach($user->id, ['permission' => 'read']);

        return $folder;
    }

    /**
     * Get breadcrumb path
     */
    public function getBreadcrumb()
    {
        $breadcrumb = collect([$this]);
        $current = $this;

        while ($current->parent) {
            $breadcrumb->prepend($current->parent);
            $current = $current->parent;
        }

        return $breadcrumb;
    }

    /**
     * Check if user has access
     */
    public function userHasAccess(User $user, $requiredPermission = 'read')
    {
        // Superadmin has full access
        if ($user->role && $user->role->type === 'superadmin') {
            return true;
        }

        // Check if user owns this folder
        if ($this->owner_id === $user->id) {
            return true;
        }

        // Check explicit permissions
        $permission = $this->users()->where('user_id', $user->id)->first();

        if (!$permission) {
            return false;
        }

        $permissionLevels = ['read' => 1, 'write' => 2, 'admin' => 3];
        $userLevel = $permissionLevels[$permission->pivot->permission] ?? 0;
        $requiredLevel = $permissionLevels[$requiredPermission] ?? 1;

        return $userLevel >= $requiredLevel;
    }

    /**
     * Get user's permission level
     */
    public function getUserPermission(User $user)
    {
        if ($user->role && $user->role->type === 'superadmin') {
            return 'admin';
        }

        $permission = $this->users()->where('user_id', $user->id)->first();

        return $permission ? $permission->pivot->permission : null;
    }

    /**
     * Scope for active folders
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for root folders
     */
    public function scopeRoot($query)
    {
        return $query->whereNull('parent_id');
    }
}
