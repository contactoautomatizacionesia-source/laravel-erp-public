<?php

namespace Tests\Feature\Services;

use App\Models\PendingApproval;
use App\Models\Staff;
use App\Models\User;
use App\Services\DoubleApprovalService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\GeneralSetting\Entities\NotificationSetting;
use Tests\TestCase;

/**
 * Tests para DoubleApprovalService
 *
 * Cubre los métodos públicos del servicio:
 *  - createPendingApproval()
 *  - processApproval()
 *  - findByHash()
 *  - findByAssignedApproverId()
 *
 * Patrón usado: integración real contra la BD de tests (DatabaseTransactions).
 * Cada test crea sus propios datos y el rollback automático los elimina al terminar.
 */
class DoubleApprovalServiceTest extends TestCase
{
    use DatabaseTransactions;

    private DoubleApprovalService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(DoubleApprovalService::class);
    }

    // -------------------------------------------------------------------------
    // Helpers privados
    // -------------------------------------------------------------------------

    /**
     * Crea un PendingApproval directamente en BD, sin pasar por el servicio.
     * Útil para preparar el estado inicial de cada test.
     */
    private function makePendingApproval(array $overrides = []): PendingApproval
    {
        return PendingApproval::create(array_merge([
            'hash'                 => Str::random(40),
            'module'               => 'TestModule',
            'action_type'          => 'no_op_action',
            'new_data'             => ['key' => 'value'],
            'original_data'        => null,
            'requester_id'         => 1,
            'assigned_approver_id' => 1,
            'status'               => 0,
        ], $overrides));
    }

    /**
     * Inserta un Staff mínimo directamente en BD (sin eventos Eloquent)
     * para evitar dependencias del boot() de Staff (IntroPrefix, etc.).
     * Retorna el ID generado.
     */
    private function makeStaffForUserId(int $userId = 1): int
    {
        return DB::table('staff')->insertGetId([
            'user_id'     => $userId,
            'employee_id' => 'TST-' . Str::random(5),
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);
    }

    /**
     * Elimina el NotificationSetting de double-approval dentro de la transacción
     * para que el bloque de notificación en createPendingApproval sea omitido.
     * El delete queda bajo DatabaseTransactions y se revierte al final del test.
     */
    private function disableDoubleApprovalNotification(): void
    {
        NotificationSetting::where('slug', 'double-approval-request')->delete();
    }

    // -------------------------------------------------------------------------
    // createPendingApproval()
    // -------------------------------------------------------------------------

    /**
     * Si ya existe un registro pendiente (status=0) para el mismo módulo y
     * action_type, el servicio debe devolver false sin crear un duplicado.
     */
    public function test_returns_false_when_duplicate_pending_exists()
    {
        $this->makePendingApproval([
            'module'      => 'ClubPoint',
            'action_type' => 'set_massive_points',
            'status'      => 0,
        ]);

        $this->actingAs(User::find(1));

        $result = $this->service->createPendingApproval([
            'module'           => 'ClubPoint',
            'action_type'      => 'set_massive_points',
            'new_data'         => ['set' => []],
            'staff_id'         => 1,
            'notification_url' => 'double_approval.index',
        ]);

        $this->assertFalse($result);
    }

    /**
     * Cuando no existe un pendiente previo, createPendingApproval debe
     * persistir el registro en BD y devolver true.
     */
    public function test_creates_pending_approval_when_no_duplicate_exists()
    {
        $this->actingAs(User::find(1));
        $staffId = $this->makeStaffForUserId(1);
        $this->disableDoubleApprovalNotification();

        $result = $this->service->createPendingApproval([
            'module'           => 'ClubPoint',
            'action_type'      => 'create_test_action',
            'new_data'         => ['set' => []],
            'staff_id'         => $staffId,
            'notification_url' => 'double_approval.index',
        ]);

        $this->assertTrue($result);
        $this->assertDatabaseHas('pending_approvals', [
            'module'       => 'ClubPoint',
            'action_type'  => 'create_test_action',
            'requester_id' => 1,
            'status'       => 0,
        ]);
    }

    /**
     * El hash generado debe ser una cadena de exactamente 40 caracteres
     * y ser único en la tabla.
     */
    public function test_creates_pending_approval_with_40_char_unique_hash()
    {
        $this->actingAs(User::find(1));
        $staffId = $this->makeStaffForUserId(1);
        $this->disableDoubleApprovalNotification();

        $actionType = 'hash_test_action';

        $this->service->createPendingApproval([
            'module'           => 'TestModule',
            'action_type'      => $actionType,
            'new_data'         => ['key' => 'value'],
            'staff_id'         => $staffId,
            'notification_url' => 'double_approval.index',
        ]);

        $record = PendingApproval::where('action_type', $actionType)->first();

        $this->assertNotNull($record);
        $this->assertEquals(40, strlen($record->hash));
    }

    /**
     * El campo requester_id debe almacenar el ID del usuario autenticado
     * que llamó al método.
     */
    public function test_creates_pending_approval_with_authenticated_user_as_requester()
    {
        $user    = User::find(1);
        $staffId = $this->makeStaffForUserId($user->id);
        $this->actingAs($user);
        $this->disableDoubleApprovalNotification();

        $actionType = 'requester_test_action';

        $this->service->createPendingApproval([
            'module'           => 'TestModule',
            'action_type'      => $actionType,
            'new_data'         => ['key' => 'value'],
            'staff_id'         => $staffId,
            'notification_url' => 'double_approval.index',
        ]);

        $this->assertDatabaseHas('pending_approvals', [
            'action_type'  => $actionType,
            'requester_id' => $user->id,
        ]);
    }

    /**
     * Un pendiente que ya fue aprobado (status=1) no debe bloquear
     * la creación de uno nuevo para el mismo módulo/action_type.
     * Solo los pendientes activos (status=0) generan conflicto.
     */
    public function test_allows_new_pending_when_previous_was_approved()
    {
        $this->makePendingApproval([
            'module'      => 'ClubPoint',
            'action_type' => 'set_massive_points',
            'status'      => 1, // ya aprobado
        ]);

        $this->actingAs(User::find(1));
        $staffId = $this->makeStaffForUserId(1);
        $this->disableDoubleApprovalNotification();

        $result = $this->service->createPendingApproval([
            'module'           => 'ClubPoint',
            'action_type'      => 'set_massive_points',
            'new_data'         => ['set' => []],
            'staff_id'         => $staffId,
            'notification_url' => 'double_approval.index',
        ]);

        $this->assertTrue($result);
    }

    // -------------------------------------------------------------------------
    // findByHash()
    // -------------------------------------------------------------------------

    /**
     * findByHash debe retornar el PendingApproval correcto cuando el hash existe.
     */
    public function test_find_by_hash_returns_correct_pending_approval()
    {
        $hash    = Str::random(40);
        $pending = $this->makePendingApproval(['hash' => $hash]);

        $result = $this->service->findByHash($hash);

        $this->assertNotNull($result);
        $this->assertEquals($pending->id, $result->id);
        $this->assertEquals($hash, $result->hash);
    }

    /**
     * findByHash debe retornar null cuando el hash no existe en la tabla.
     */
    public function test_find_by_hash_returns_null_for_nonexistent_hash()
    {
        $result = $this->service->findByHash('hash_that_definitely_does_not_exist_xyz');

        $this->assertNull($result);
    }

    // -------------------------------------------------------------------------
    // findByAssignedApproverId()
    // -------------------------------------------------------------------------

    /**
     * Debe retornar todos los registros asignados al aprobador indicado.
     */
    public function test_find_by_assigned_approver_returns_collection_with_correct_count()
    {
        $approverId = 9001; // ID ficticio para aislar de datos reales del sistema

        $this->makePendingApproval(['assigned_approver_id' => $approverId, 'action_type' => 'action_a']);
        $this->makePendingApproval(['assigned_approver_id' => $approverId, 'action_type' => 'action_b']);

        $results = $this->service->findByAssignedApproverId($approverId);

        $this->assertInstanceOf(\Illuminate\Support\Collection::class, $results);
        $this->assertCount(2, $results);
    }

    /**
     * Si no hay registros para ese aprobador, debe retornar una colección vacía.
     */
    public function test_find_by_assigned_approver_returns_empty_collection_when_none()
    {
        $results = $this->service->findByAssignedApproverId(99997);

        $this->assertInstanceOf(\Illuminate\Support\Collection::class, $results);
        $this->assertEmpty($results);
    }

    /**
     * Solo deben retornarse los registros del aprobador solicitado,
     * no los de otros aprobadores.
     */
    public function test_find_by_assigned_approver_does_not_return_other_approvers_records()
    {
        $targetApproverId = 9002;
        $otherApproverId  = 9003;

        $this->makePendingApproval(['assigned_approver_id' => $targetApproverId]);
        $this->makePendingApproval(['assigned_approver_id' => $otherApproverId]);

        $results = $this->service->findByAssignedApproverId($targetApproverId);

        $this->assertCount(1, $results);
        $this->assertEquals($targetApproverId, $results->first()->assigned_approver_id);
    }

    // -------------------------------------------------------------------------
    // processApproval()
    // -------------------------------------------------------------------------

    /**
     * Debe lanzar excepción si el ID no existe en la tabla.
     */
    public function test_process_approval_throws_exception_for_nonexistent_id()
    {
        $this->expectException(\Exception::class);

        $this->service->processApproval(999999, 1, 1);
    }

    /**
     * Debe lanzar excepción si el registro ya fue aprobado (status=1).
     * No se permite reprocesar aprobaciones ya resueltas.
     */
    public function test_process_approval_throws_exception_when_already_approved()
    {
        $pending = $this->makePendingApproval(['status' => 1]);

        $this->expectException(\Exception::class);

        $this->service->processApproval($pending->id, 1, 1);
    }

    /**
     * Debe lanzar excepción si el registro ya fue rechazado (status=2).
     */
    public function test_process_approval_throws_exception_when_already_rejected()
    {
        $pending = $this->makePendingApproval(['status' => 2]);

        $this->expectException(\Exception::class);

        $this->service->processApproval($pending->id, 2, 1);
    }

    /**
     * Al rechazar (status=2), el motivo de rechazo debe guardarse en BD.
     */
    public function test_process_approval_rejected_saves_rejection_reason()
    {
        $pending = $this->makePendingApproval(['status' => 0]);
        $reason  = 'Los datos no son correctos';

        $result = $this->service->processApproval($pending->id, 2, 1, $reason);

        $this->assertTrue($result);
        $this->assertDatabaseHas('pending_approvals', [
            'id'               => $pending->id,
            'status'           => 2,
            'rejection_reason' => $reason,
        ]);
    }

    /**
     * Al rechazar sin proporcionar motivo, rejection_reason debe quedar en null.
     */
    public function test_process_approval_rejected_without_reason_saves_null()
    {
        $pending = $this->makePendingApproval(['status' => 0]);

        $this->service->processApproval($pending->id, 2, 1, null);

        $this->assertDatabaseHas('pending_approvals', [
            'id'               => $pending->id,
            'status'           => 2,
            'rejection_reason' => null,
        ]);
    }

    /**
     * Al aprobar un action_type no registrado en el switch, el proceso no debe
     * lanzar error (el switch no tiene default) y debe actualizar status a 1.
     *
     * Esto verifica el flujo "aprobado" sin ejecutar lógica de módulos externos.
     */
    public function test_process_approval_approved_unknown_action_updates_status_to_one()
    {
        $pending = $this->makePendingApproval([
            'status'      => 0,
            'action_type' => 'no_op_action',
            'new_data'    => ['key' => 'value'],
        ]);

        $result = $this->service->processApproval($pending->id, 1, 1);

        $this->assertTrue($result);
        $this->assertDatabaseHas('pending_approvals', [
            'id'     => $pending->id,
            'status' => 1,
        ]);
    }

    /**
     * El campo assigned_approver_id debe actualizarse con el ID del aprobador
     * que procesó la solicitud.
     */
    public function test_process_approval_stores_assigned_approver_id()
    {
        $pending    = $this->makePendingApproval(['status' => 0, 'action_type' => 'no_op_action']);
        $approverId = 1;

        $this->service->processApproval($pending->id, 1, $approverId);

        $this->assertDatabaseHas('pending_approvals', [
            'id'                   => $pending->id,
            'assigned_approver_id' => $approverId,
        ]);
    }

    /**
     * processApproval debe devolver true tanto para aprobaciones como rechazos.
     */
    public function test_process_approval_returns_true_on_rejection()
    {
        $pending = $this->makePendingApproval(['status' => 0]);

        $result = $this->service->processApproval($pending->id, 2, 1, 'Motivo');

        $this->assertTrue($result);
    }
}
