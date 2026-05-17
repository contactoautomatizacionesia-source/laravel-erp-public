<?php

namespace Modules\NetworkTree\Services;

use Illuminate\Support\Facades\DB;
use Modules\NetworkTree\Entities\NetworkPath;
use Illuminate\Support\Collection;

class NetworkTreeManager
{
    public function insertNode(int $userId, ?int $parentId): void
    {
        $rows = [];
        $rows[] = [
            'entrepreneur_id' => $userId,
            'ancestor_id'     => $userId,
            'depth'           => 0,
            'created_at'      => now(),
            'updated_at'      => now(),
        ];

        if ($parentId !== null) {
            $parentAncestors = NetworkPath::where('entrepreneur_id', $parentId)->get(['ancestor_id', 'depth']);
            if ($parentAncestors->isEmpty()) {
                NetworkPath::insertOrIgnore([
                    'entrepreneur_id' => $parentId,
                    'ancestor_id'     => $parentId,
                    'depth'           => 0,
                    'created_at'      => now(),
                    'updated_at'      => now(),
                ]);
                $parentAncestors = NetworkPath::where('entrepreneur_id', $parentId)->get(['ancestor_id', 'depth']);
            }
            foreach ($parentAncestors as $path) {
                $rows[] = [
                    'entrepreneur_id' => $userId,
                    'ancestor_id'     => $path->ancestor_id,
                    'depth'           => $path->depth + 1,
                    'created_at'      => now(),
                    'updated_at'      => now(),
                ];
            }
        }
        NetworkPath::insertOrIgnore($rows);
    }

    public function moveSubtree(int $nodeId, ?int $newParentId): void
    {
        DB::transaction(function () use ($nodeId, $newParentId) {
            $subtreeIds = NetworkPath::where('ancestor_id', $nodeId)->pluck('entrepreneur_id')->all();
            DB::table('network_paths')->whereIn('entrepreneur_id', $subtreeIds)->whereNotIn('ancestor_id', $subtreeIds)->delete();

            if ($newParentId === null) {
                return;
            }

            $internalPaths = DB::table('network_paths')->where('ancestor_id', $nodeId)->get(['entrepreneur_id', 'depth']);
            $newAncestorPaths = NetworkPath::where('entrepreneur_id', $newParentId)->get(['ancestor_id', 'depth']);
            $rows = [];
            $now  = now();

            foreach ($newAncestorPaths as $ancestorPath) {
                foreach ($internalPaths as $internalPath) {
                    $rows[] = [
                        'entrepreneur_id' => $internalPath->entrepreneur_id,
                        'ancestor_id'     => $ancestorPath->ancestor_id,
                        'depth'           => $ancestorPath->depth + 1 + $internalPath->depth,
                        'created_at'      => $now,
                        'updated_at'      => $now,
                    ];
                }
            }
            if (! empty($rows)) {
                NetworkPath::insertOrIgnore($rows);
            }
        });
    }

    public function removeNode(int $userId): void
    {
        NetworkPath::where('entrepreneur_id', $userId)->delete();
    }

    public function getAncestors(int $userId): Collection
    {
        return NetworkPath::where('entrepreneur_id', $userId)->where('depth', '>', 0)->orderBy('depth', 'asc')->get();
    }

    public function getDescendants(int $userId, ?int $maxDepth = null): Collection
    {
        $query = NetworkPath::where('ancestor_id', $userId)->where('depth', '>', 0)->orderBy('depth', 'asc');
        if ($maxDepth !== null) {
            $query->where('depth', '<=', $maxDepth);
        }
        return $query->get();
    }

    public function getDirectChildren(int $userId): Collection
    {
        return NetworkPath::where('ancestor_id', $userId)->where('depth', 1)->get();
    }

    public function countDescendants(int $userId): int
    {
        return NetworkPath::where('ancestor_id', $userId)->where('depth', '>', 0)->count();
    }

    public function getMaxDepth(int $userId): int
    {
        return (int) NetworkPath::where('ancestor_id', $userId)->max('depth') ?? 0;
    }
}
