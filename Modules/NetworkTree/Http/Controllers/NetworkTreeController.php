<?php

namespace Modules\NetworkTree\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Modules\NetworkTree\Services\NetworkTreeService;
use Modules\Plans\Helpers\PlanContextHelper;
use App\Models\User;

class NetworkTreeController extends Controller
{
    public function __construct(private NetworkTreeService $treeService)
    {
    }

    /**
     * Retorna el árbol descendente en JSON anidado.
     */
    public function tree(Request $request, ?int $userId = null): JsonResponse
    {
        $baseUserId   = $userId ?? Auth::id();
        $depth        = min((int) $request->get('depth', 2), 5);
        $search       = $request->get('search');
        $planFilter   = $request->get('plan');

        $targetUserId = $this->treeService->resolveTargetUserId($baseUserId, $request->get('target'));
        if (!$targetUserId) {
            return response()->json([
                'success' => false,
                'message' => __('tree.search_not_allowed'),
            ], 403);
        }

        $cacheKey = "network_tree_{$targetUserId}_d{$depth}";

        $buildTree = function () use ($targetUserId, $depth) {
            return $this->treeService->getTree($targetUserId, $depth);
        };

        $treePayload = ($search || $planFilter)
            ? $buildTree()
            : Cache::remember($cacheKey, now()->addMinutes(10), $buildTree);

        if (! $treePayload || ! isset($treePayload['tree'])) {
            return response()->json([
                'success' => true,
                'data'    => null,
                'meta'    => [],
            ]);
        }

        $tree = $treePayload['tree'];
        $meta = $treePayload['meta'] ?? [];

        // Aplicar filtros en memoria sobre el Ã¡rbol ya construido
        if ($search || $planFilter) {
            $tree = $this->treeService->filterTree($tree, $search, $planFilter);
        }

        return response()->json([
            'success' => true,
            'data'    => $tree,
            'meta'    => $meta,
        ]);
    }

    /**
     * Retorna métricas agregadas de la red de un empresario.
     */
    public function stats(Request $request, ?int $userId = null): JsonResponse
    {
        $baseUserId   = $userId ?? Auth::id();
        $targetUserId = $this->treeService->resolveTargetUserId($baseUserId, $request->get('target'));
        if (!$targetUserId) {
            return response()->json([
                'success' => false,
                'message' => __('tree.search_not_allowed'),
            ], 403);
        }
        $cacheKey     = "network_stats_{$targetUserId}";

        $stats = Cache::remember($cacheKey, now()->addMinutes(15), function () use ($targetUserId) {
            return $this->treeService->getStats($targetUserId);
        });

        return response()->json([
            'success' => true,
            'data'    => $stats,
        ]);
    }

    /**
     * Retorna el listado de planes disponibles (plan_child).
     */
    public function plans(Request $request, ?int $userId = null): JsonResponse
    {
        $data = $this->treeService->getPlansHierarchy();

        return response()->json([
            'success' => true,
            'data'    => $data,
        ]);
    }

    /**
     * Retorna la vista del árbol global para el Superadmin.
     */
    public function globalTree(Request $request)
    {
        // 1. Identificar al Empresario Root por su correo
        $rootId = config('networktree.root_user_id');
        $rootEmail = config('networktree.root_user_email');

        $rootUser = User::where('id', $rootId)->orWhere('email', $rootEmail)->first();

        // 2. Validar que exista (para no romper la vista si aÃºn no se ha creado)
        if (!$rootUser) {
            return redirect()->back()->with('error', __('tree.root_user_not_found'));
        }

        // 3. Retornar la nueva vista pasando el ID del root
        return view('networktree::admin.global_network', [
            'rootUserId' => $rootUser->id
        ]);
    }

    /**
     * Busca un empresario por código o documento dentro de la red permitida.
     */
    public function search(Request $request, ?int $userId = null): JsonResponse
    {
        $baseUserId = $userId ?? Auth::id();
        $query = trim((string) $request->get('q', ''));

        $status = 200;
        $payload = [];

        if ($query === '') {
            $status = 422;
            $payload = ['success' => false, 'message' => __('tree.search_empty')];
        } else {
            $result = $this->treeService->searchUserInNetwork($baseUserId, $query);

            if (! $result['user_id']) {
                $status = 404;
                $payload = ['success' => false, 'message' => __('tree.search_not_found')];
            } elseif (! $result['allowed']) {
                $status = 403;
                $payload = ['success' => false, 'message' => __('tree.search_not_allowed')];
            } else {
                $user = User::select('id', 'first_name', 'last_name')->find($result['user_id']);
                $payload = [
                    'success' => true,
                    'data' => [
                        'user_id' => $result['user_id'],
                        'name' => $user ? trim($user->first_name . ' ' . $user->last_name) : null,
                    ],
                ];
            }
        }

        return response()->json($payload, $status);
    }

    /**
     * Retorna los hijos directos de un nodo para carga incremental.
     */
    public function children(Request $request, ?int $userId = null): JsonResponse
    {
        $baseUserId = $userId ?? Auth::id();
        $nodeId = (int) $request->get('node');

        if ($nodeId <= 0) {
            return response()->json([
                'success' => false,
                'message' => __('tree.search_empty'),
            ], 422);
        }

        $result = $this->treeService->getNodeChildren($baseUserId, $nodeId);
        if (! $result['allowed']) {
            return response()->json([
                'success' => false,
                'message' => __('tree.search_not_allowed'),
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data' => $result['children'],
            'meta' => $result['meta'] ?? [],
        ]);
    }

    /**
     * Retorna la información de plan para el panel del nodo.
     */
    public function panel(Request $request, ?int $userId = null): JsonResponse
    {
        $baseUserId = $userId ?? Auth::id();
        $targetId = (int) $request->get('user');

        if ($targetId <= 0) {
            return response()->json([
                'success' => false,
                'message' => __('tree.search_empty'),
            ], 422);
        }

        $resolved = $this->treeService->resolveTargetUserId($baseUserId, $targetId);
        if (! $resolved) {
            return response()->json([
                'success' => false,
                'message' => __('tree.search_not_allowed'),
            ], 403);
        }

        $profileUpdatedAt = DB::table('customer_profiles')
            ->where('user_id', $resolved)
            ->value('updated_at');
        $version = $profileUpdatedAt ? strtotime($profileUpdatedAt) : 0;
        $cacheKey = "network_panel_{$resolved}_{$version}";
        $context = Cache::remember($cacheKey, now()->addMinutes(10), function () use ($resolved) {
            return PlanContextHelper::resolve($resolved);
        });

        return response()->json([
            'success' => true,
            'data' => $context,
        ]);
    }
}
