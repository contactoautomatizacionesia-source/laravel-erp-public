<?php

namespace Modules\NetworkTree\Repositories;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

class NetworkTreeRepository
{
    private const TABLE_NETWORK_PATHS = 'network_paths';
    private const TABLE_NETWORK_PATHS_ALIAS = 'network_paths as np';
    private const TABLE_PLAN_CHILD_ALIAS = 'plan_child as pc';
    private const TABLE_PLAN_ALIAS = 'plan as p';

    // Nuevas constantes añadidas para resolver duplicidad de literales
    private const TABLE_USERS_ALIAS = 'users as u';
    private const TABLE_CUSTOMER_PROFILES_ALIAS = 'customer_profiles as cp';
    private const COL_PLAN_ID = 'p.id as plan_id';
    private const COL_PLAN_TITLE = 'p.title as plan_title';
    private const COL_PLAN_STYLES = 'p.styles as plan_styles';

    /**
     * Optiene todos los descendientes hasta una profundidad dada, junto con perfil y plan.
     */
    public function getDescendantsData(int $rootUserId, int $maxDepth): Collection
    {
        $pointsSubquery = DB::table('orders')
            ->selectRaw('customer_id, SUM(total_points) as personal_points')
            ->groupBy('customer_id');

        return DB::table(self::TABLE_NETWORK_PATHS_ALIAS)
            ->join(self::TABLE_USERS_ALIAS, 'u.id', '=', 'np.entrepreneur_id')
            ->leftJoin(self::TABLE_CUSTOMER_PROFILES_ALIAS, 'cp.user_id', '=', 'u.id')
            ->leftJoin(self::TABLE_PLAN_CHILD_ALIAS, 'pc.id', '=', 'cp.plan_child_id')
            ->leftJoin(self::TABLE_PLAN_ALIAS, 'p.id', '=', 'pc.plan_id')
            ->leftJoinSub($pointsSubquery, 'pnts', 'pnts.customer_id', '=', 'u.id')
            ->where('np.ancestor_id', $rootUserId)
            ->where('np.depth', '<=', $maxDepth)
            ->orderBy('np.depth')
            ->get([
                'np.entrepreneur_id as id',
                'np.ancestor_id',
                'np.depth',
                'u.first_name',
                'u.last_name',
                'u.avatar',
                'u.created_at',
                'cp.code',
                'cp.document_number',
                'cp.representative_id as parent_id',
                'cp.plan_child_id',
                'pc.title as plan_child_title',
                self::COL_PLAN_ID,
                self::COL_PLAN_TITLE,
                self::COL_PLAN_STYLES,
                'pc.level_order',
                DB::raw('IFNULL(pnts.personal_points, 0) as personal_points')
            ]);
    }

    /**
     * Obtiene los hijos directos de un nodo (depth=1) con perfil y plan.
     */
    public function getChildrenData(int $parentId): Collection
    {
        $pointsSubquery = DB::table('orders')
            ->selectRaw('customer_id, SUM(total_points) as personal_points')
            ->groupBy('customer_id');

        return DB::table(self::TABLE_NETWORK_PATHS_ALIAS)
            ->join(self::TABLE_USERS_ALIAS, 'u.id', '=', 'np.entrepreneur_id')
            ->leftJoin(self::TABLE_CUSTOMER_PROFILES_ALIAS, 'cp.user_id', '=', 'u.id')
            ->leftJoin(self::TABLE_PLAN_CHILD_ALIAS, 'pc.id', '=', 'cp.plan_child_id')
            ->leftJoin(self::TABLE_PLAN_ALIAS, 'p.id', '=', 'pc.plan_id')
            ->leftJoinSub($pointsSubquery, 'pnts', 'pnts.customer_id', '=', 'u.id')
            ->where('np.ancestor_id', $parentId)
            ->where('np.depth', 1)
            ->orderBy('np.entrepreneur_id')
            ->get([
                'np.entrepreneur_id as id',
                'np.ancestor_id',
                'np.depth',
                'u.first_name',
                'u.last_name',
                'u.avatar',
                'u.created_at',
                'cp.code',
                'cp.document_number',
                'cp.representative_id as parent_id',
                'cp.plan_child_id',
                'pc.title as plan_child_title',
                self::COL_PLAN_ID,
                self::COL_PLAN_TITLE,
                self::COL_PLAN_STYLES,
                'pc.level_order',
                DB::raw('IFNULL(pnts.personal_points, 0) as personal_points')
            ]);
    }

    /**
     * Suma de puntos de red por cada ancestro en una lista.
     */
    public function getNetworkPointsByAncestor(array $ancestorIds): Collection
    {
        return DB::table(self::TABLE_NETWORK_PATHS_ALIAS)
            ->join('orders as o', 'o.customer_id', '=', 'np.entrepreneur_id')
            ->whereIn('np.ancestor_id', $ancestorIds)
            ->where('np.depth', '>', 0)
            ->selectRaw('np.ancestor_id, SUM(o.total_points) as network_points')
            ->groupBy('np.ancestor_id')
            ->pluck('network_points', 'ancestor_id');
    }

    /**
     * Conteo de hijos directos para cada nodo en el rango especificado.
     */
    public function getDirectChildrenCount(int $rootUserId, int $maxDepth): Collection
    {
        return DB::table(self::TABLE_NETWORK_PATHS . ' as np_child')
            ->join(self::TABLE_NETWORK_PATHS . ' as np_parent', function ($join) use ($rootUserId, $maxDepth) {
                $join->on('np_child.ancestor_id', '=', 'np_parent.entrepreneur_id')
                    ->where('np_child.depth', 1)
                    ->where('np_parent.ancestor_id', $rootUserId)
                    ->where('np_parent.depth', '<=', $maxDepth);
            })
            ->selectRaw('np_child.ancestor_id as parent_id, COUNT(*) as direct_count')
            ->groupBy('np_child.ancestor_id')
            ->pluck('direct_count', 'parent_id');
    }

    /**
     * Conteo de hijos directos para una lista de nodos.
     */
    public function getDirectChildrenCountByNodes(array $nodeIds): Collection
    {
        if (empty($nodeIds)) {
            return collect();
        }

        return DB::table(self::TABLE_NETWORK_PATHS)
            ->whereIn('ancestor_id', $nodeIds)
            ->where('depth', 1)
            ->selectRaw('ancestor_id as parent_id, COUNT(*) as direct_count')
            ->groupBy('ancestor_id')
            ->pluck('direct_count', 'parent_id');
    }

    /**
     * Indica qué nodos en la frontera del nivel profundidad máxima tienen más hijos.
     */
    public function getHasMoreByNode(int $rootUserId, int $maxDepth): array
    {
        return DB::table(self::TABLE_NETWORK_PATHS . ' as np_child')
            ->join(self::TABLE_NETWORK_PATHS . ' as np_parent', function ($join) use ($rootUserId, $maxDepth) {
                $join->on('np_child.ancestor_id', '=', 'np_parent.entrepreneur_id')
                    ->where('np_child.depth', 1)
                    ->where('np_parent.ancestor_id', $rootUserId)
                    ->where('np_parent.depth', $maxDepth);
            })
            ->pluck('np_child.ancestor_id')
            ->flip()
            ->all();
    }

    /**
     * Indica si cada nodo tiene mÃ¡s niveles por debajo de sus hijos directos.
     */
    public function getHasMoreChildrenForNodes(array $nodeIds): array
    {
        if (empty($nodeIds)) {
            return [];
        }

        return DB::table(self::TABLE_NETWORK_PATHS)
            ->whereIn('ancestor_id', $nodeIds)
            ->where('depth', '>', 1)
            ->pluck('ancestor_id')
            ->flip()
            ->all();
    }

    /**
     * Indica si existen niveles adicionales por debajo de la profundidad actual.
     */
    public function hasMoreLevels(int $rootUserId, int $maxDepth): bool
    {
        return DB::table(self::TABLE_NETWORK_PATHS)
            ->where('ancestor_id', $rootUserId)
            ->where('depth', '>', $maxDepth)
            ->exists();
    }

    /**
     * Obtiene los planes activos y sus niveles (plan_child).
     */
    public function getActivePlansTree(): Collection
    {
        return DB::table('plan as p')
            ->join('plan_child as pc', 'pc.plan_id', '=', 'p.id')
            ->where('p.is_active', 1)
            ->where('pc.is_active', 1)
            ->orderBy('p.id')
            ->orderBy('pc.level_order')
            ->get([
                self::COL_PLAN_ID,
                self::COL_PLAN_TITLE,
                self::COL_PLAN_STYLES,
                'pc.id as child_id',
                'pc.title as child_name'
            ]);
    }

    /**
     * Suma total de puntos generados por toda la red descendente.
     */
    public function getTotalPointsInNetwork(int $userId): float
    {
        return (float) DB::table(self::TABLE_NETWORK_PATHS_ALIAS)
            ->join('orders as o', 'o.customer_id', '=', 'np.entrepreneur_id')
            ->where('np.ancestor_id', $userId)
            ->where('np.depth', '>', 0)
            ->sum('o.total_points');
    }

    /**
     * Conteo de niveles (plan_child) por tabla de plan.
     */
    public function getPlanLevelsCount(): Collection
    {
        return DB::table('plan_child')
            ->selectRaw('plan_id, COUNT(*) as count')
            ->groupBy('plan_id')
            ->pluck('count', 'plan_id');
    }

    /**
     * Busca el ID de un usuario por cÃ³digo de empresario o documento.
     */
    public function findUserIdByCodeOrDocument(string $query): ?int
    {
        $id = DB::table(self::TABLE_USERS_ALIAS)
            ->leftJoin(self::TABLE_CUSTOMER_PROFILES_ALIAS, 'cp.user_id', '=', 'u.id')
            ->where('u.role_id', 4)
            ->where(function ($q) use ($query) {
                $q->where('cp.code', $query)
                    ->orWhere('cp.document_number', $query);
            })
            ->value('u.id');

        return $id ? (int) $id : null;
    }

    /**
     * Verifica si un usuario estÃ¡ dentro del subÃ¡rbol de otro.
     */
    public function isInSubtree(int $ancestorId, int $targetId): bool
    {
        return DB::table(self::TABLE_NETWORK_PATHS)
            ->where('ancestor_id', $ancestorId)
            ->where('entrepreneur_id', $targetId)
            ->exists();
    }

    /**
     * Distribución de planes en la descendencia directa e indirecta.
     */
    public function getPlanDistribution(array $descendantIds): Collection
    {
        return DB::table(self::TABLE_CUSTOMER_PROFILES_ALIAS)
            ->join(self::TABLE_PLAN_CHILD_ALIAS, 'pc.id', '=', 'cp.plan_child_id')
            ->join(self::TABLE_PLAN_ALIAS, 'p.id', '=', 'pc.plan_id')
            ->whereIn('cp.user_id', $descendantIds)
            ->whereNotNull('cp.plan_child_id')
            ->selectRaw('cp.plan_child_id, p.id as plan_id, p.title as plan_parent_title, pc.level_order, p.styles as plan_styles, COUNT(*) as total')
            ->groupBy('cp.plan_child_id', 'p.id', 'p.title', 'pc.level_order', 'p.styles')
            ->get();
    }
}
