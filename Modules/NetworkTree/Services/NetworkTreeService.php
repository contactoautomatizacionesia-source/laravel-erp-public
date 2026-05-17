<?php

namespace Modules\NetworkTree\Services;

use Modules\NetworkTree\Repositories\NetworkTreeRepository;
use Modules\NetworkTree\Helpers\NetworkTreeFormatter;

class NetworkTreeService
{
    public function __construct(
        private NetworkTreeRepository $repository,
        private NetworkTreeManager $manager
    ) {}

    public function getTree(int $userId, int $depth): ?array
    {
        $descendants = $this->repository->getDescendantsData($userId, $depth);

        if ($descendants->isEmpty()) {
            return null;
        }

        $allIdsInTree = $descendants->pluck('id')->toArray();
        $planLevelsCount = $this->repository->getPlanLevelsCount()->toArray();
        $networkPointsByAncestor = $this->repository->getNetworkPointsByAncestor($allIdsInTree)->toArray();
        $directByNode = $this->repository->getDirectChildrenCount($userId, $depth)->toArray();
        $hasMoreByNode = $this->repository->getHasMoreByNode($userId, $depth);
        $hasMoreLevels = $this->repository->hasMoreLevels($userId, $depth);
        $maxDepth      = $this->manager->getMaxDepth($userId);

        $resolvedPlanCache = [];
        $nodeMap = [];

        foreach ($descendants as $row) {
            $this->updatePlanCache($resolvedPlanCache, $row, $planLevelsCount);

            $nodeMap[$row->id] = $this->formatNode(
                $row,
                $resolvedPlanCache,
                $networkPointsByAncestor,
                $directByNode,
                $hasMoreByNode
            );
        }

        return [
            'tree' => $this->assembleTree($descendants, $nodeMap),
            'meta' => [
                'has_more_levels' => $hasMoreLevels,
                'max_depth' => $maxDepth,
            ],
        ];
    }

    public function getNodeChildren(int $baseUserId, int $nodeId): array
    {
        if ($nodeId !== $baseUserId && ! $this->repository->isInSubtree($baseUserId, $nodeId)) {
            return ['allowed' => false, 'children' => [], 'meta' => []];
        }

        $children = $this->repository->getChildrenData($nodeId);
        if ($children->isEmpty()) {
            return ['allowed' => true, 'children' => [], 'meta' => ['has_more_children' => false]];
        }

        $childIds = $children->pluck('id')->toArray();
        $planLevelsCount = $this->repository->getPlanLevelsCount()->toArray();
        $networkPointsByAncestor = $this->repository->getNetworkPointsByAncestor($childIds)->toArray();
        $directByNode = $this->repository->getDirectChildrenCountByNodes($childIds)->toArray();
        $hasMoreByNode = $this->repository->getHasMoreChildrenForNodes($childIds);

        $resolvedPlanCache = [];
        $nodes = [];

        foreach ($children as $row) {
            $this->updatePlanCache($resolvedPlanCache, $row, $planLevelsCount);
            $nodes[] = $this->formatNode(
                $row,
                $resolvedPlanCache,
                $networkPointsByAncestor,
                $directByNode,
                $hasMoreByNode
            );
        }

        return [
            'allowed' => true,
            'children' => $nodes,
            'meta' => ['has_more_children' => $this->repository->hasMoreLevels($nodeId, 1)],
        ];
    }

    private function updatePlanCache(array &$resolvedPlanCache, object $row, array $planLevelsCount): void
    {
        $planChildId = $row->plan_child_id;
        $planId      = $row->plan_id;

        if ($planChildId === null || isset($resolvedPlanCache[$planChildId])) {
            return;
        }

        $basePlanName = NetworkTreeFormatter::resolveTranslatable($row->plan_title);
        $finalName = (isset($planLevelsCount[$planId]) && $planLevelsCount[$planId] > 1)
            ? "{$basePlanName} - Nivel {$row->level_order}"
            : $basePlanName;

        $resolvedPlanCache[$planChildId] = [
            'name'  => $finalName,
            'color' => $row->plan_styles ? NetworkTreeFormatter::resolvePlanColor($row->plan_styles) : null,
            'icon'  => $row->plan_styles ? NetworkTreeFormatter::resolvePlanIcon($row->plan_styles) : null,
        ];
    }

    private function formatNode(object $row, array $resolvedPlanCache, array $networkPointsByAncestor, array $directByNode, array $hasMoreByNode): array
    {
        $name = trim("{$row->first_name} {$row->last_name}");
        $planChildId = $row->plan_child_id;
        $pPoints = (float) $row->personal_points;
        $nPoints = (float) ($networkPointsByAncestor[$row->id] ?? 0);

        return [
            'id'                        => $row->id,
            'code'                      => $row->code ?? "ID-{$row->id}",
            'document_number'           => $row->document_number ?? null,
            'name'                      => $name ?: "Usuario {$row->id}",
            'avatar'                    => $row->avatar ? asset($row->avatar) : null,
            'depth'                     => $row->depth,
            'reg_year'                  => $row->created_at ? date('Y', strtotime($row->created_at)) : null,
            'discount'                  => 0,
            'direct_children_count'     => (int) ($directByNode[$row->id] ?? 0),
            'has_more_children'         => isset($hasMoreByNode[$row->id]),
            'plan'                      => $planChildId ? ($resolvedPlanCache[$planChildId]['name'] ?? null) : null,
            'plan_color'                => $planChildId ? ($resolvedPlanCache[$planChildId]['color'] ?? null) : null,
            'plan_icon'                 => $planChildId ? ($resolvedPlanCache[$planChildId]['icon'] ?? null) : null,
            'plan_child_id'             => $planChildId,
            'personal_points'           => $pPoints,
            'personal_points_formatted' => formatNumberPoints($pPoints),
            'network_points'            => $nPoints,
            'network_points_formatted'  => formatNumberPoints($nPoints),
            'children'                  => [],
        ];
    }

    private function assembleTree(\Illuminate\Support\Collection $descendants, array &$nodeMap): ?array
    {
        $root = null;
        foreach ($descendants as $row) {
            if ($row->depth === 0) {
                $root = &$nodeMap[$row->id];
                continue;
            }
            $parentId = $row->parent_id;
            if ($parentId && isset($nodeMap[$parentId])) {
                $nodeMap[$parentId]['children'][] = &$nodeMap[$row->id];
            }
        }
        return $root;
    }

    public function getStats(int $userId): array
    {
        $totalNetwork  = $this->manager->countDescendants($userId);
        $directCount   = $this->manager->getDirectChildren($userId)->count();
        $maxDepth      = $this->manager->getMaxDepth($userId);
        $totalPoints   = $this->repository->getTotalPointsInNetwork($userId);

        $treeData = $this->repository->getDescendantsData($userId, $maxDepth);
        $descendantIds = $treeData->pluck('id')->toArray();
        $planDistribution = $this->buildPlanDistribution($descendantIds);

        return [
            'total_network_count'    => $totalNetwork,
            'direct_children_count'  => $directCount,
            'max_depth'              => $maxDepth,
            'distribution_by_plan'   => $planDistribution,
            'total_network_points'   => formatNumberPoints($totalPoints),
        ];
    }

    private function buildPlanDistribution(array $descendantIds): array
    {
        if (empty($descendantIds)) {
            return [];
        }

        $rows = $this->repository->getPlanDistribution($descendantIds);
        if ($rows->isEmpty()) {
            return [];
        }

        $grandTotal = $rows->sum('total');
        $planLevelsCount = $this->repository->getPlanLevelsCount()->toArray();

        $items = $rows->map(function ($row) use ($grandTotal, $planLevelsCount) {
            $basePlan = NetworkTreeFormatter::resolveTranslatable($row->plan_parent_title ?? $row->plan_title ?? null);
            $levelLabel = '';
            $planId = $row->plan_id ?? null;
            if ($planId && isset($planLevelsCount[$planId]) && $planLevelsCount[$planId] > 1 && $row->level_order) {
                $levelLabel = ' - ' . __('tree.level') . ' ' . $row->level_order;
            }
            return [
                'plan_name'   => $basePlan ? $basePlan . $levelLabel : (NetworkTreeFormatter::resolveTranslatable($row->plan_title) ?? ''),
                'badge_color' => NetworkTreeFormatter::resolvePlanColor($row->plan_styles),
                'count'       => (int) $row->total,
                'percentage'  => $grandTotal > 0 ? round($row->total / $grandTotal * 100) : 0,
            ];
        })->values()->all();

        usort($items, function ($a, $b) {
            if ($b['count'] === $a['count']) {
                return strcmp($a['plan_name'], $b['plan_name']);
            }
            return $b['count'] <=> $a['count'];
        });

        return $items;
    }

    public function filterTree(?array $node, ?string $search, ?string $planFilter): ?array
    {
        if ($node === null) {
            return null;
        }

        $filteredChildren = [];
        foreach ($node['children'] as $child) {
            $filtered = $this->filterTree($child, $search, $planFilter);
            if ($filtered !== null) {
                $filteredChildren[] = $filtered;
            }
        }

        $matchesSearch = ! $search
            || str_contains(strtolower($node['name']), strtolower($search))
            || str_contains(strtolower($node['code'] ?? ''), strtolower($search));

        $matchesPlan = ! $planFilter || $node['plan'] === $planFilter;

        if (($matchesSearch && $matchesPlan) || ! empty($filteredChildren)) {
            $node['children'] = $filteredChildren;
            return $node;
        }

        return null;
    }

    public function getPlansHierarchy(): array
    {
        $rows = $this->repository->getActivePlansTree();
        $grouped = [];
        foreach ($rows as $row) {
            $pid = $row->plan_id;
            if (!isset($grouped[$pid])) {
                $grouped[$pid] = [
                    'id'    => (int) $pid,
                    'name'  => NetworkTreeFormatter::resolveTranslatable($row->plan_title),
                    'color' => $row->plan_styles ? NetworkTreeFormatter::resolvePlanColor($row->plan_styles) : null,
                    'icon'  => $row->plan_styles ? NetworkTreeFormatter::resolvePlanIcon($row->plan_styles) : null,
                    'levels' => []
                ];
            }
            $grouped[$pid]['levels'][] = [
                'id'   => (int) $row->child_id,
                'name' => NetworkTreeFormatter::resolveTranslatable($row->child_name)
            ];
        }
        return array_values($grouped);
    }

    public function resolveTargetUserId(int $baseUserId, $target): ?int
    {
        $targetId = (int) $target;

        // Caso 1: No hay target o el target es el mismo usuario base
        if (! $target || $targetId === $baseUserId) {
            return $baseUserId;
        }

        // Caso 2: El target es válido (mayor a 0) y pertenece al sub-árbol
        if ($targetId > 0 && $this->repository->isInSubtree($baseUserId, $targetId)) {
            return $targetId;
        }

        // Caso por defecto: No es válido o no está en el sub-árbol
        return null;
    }

    public function searchUserInNetwork(int $baseUserId, string $query): array
    {
        $targetUserId = $this->repository->findUserIdByCodeOrDocument($query);
        if (! $targetUserId) {
            return ['allowed' => false, 'user_id' => null];
        }

        $resolved = $this->resolveTargetUserId($baseUserId, $targetUserId);
        return [
            'allowed' => (bool) $resolved,
            'user_id' => $resolved,
        ];
    }
}
