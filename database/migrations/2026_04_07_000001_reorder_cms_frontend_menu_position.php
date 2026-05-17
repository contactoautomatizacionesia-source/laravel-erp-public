<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Orden deseado:
     *  pos 1 → ID 1   (Dashboard)
     *  pos 2 → ID 2   (Usuarios)
     *  pos 3 → ID 40  (Pedidos)       era 4
     *  pos 4 → ID 53  (Productos)     era 5
     *  pos 5 → ID 76  (Promocional)   era 6
     *  pos 6 → ID 88  (Finanzas)      era 7
     *  pos 7 → ID 102 (Contenido)     era 8
     *  pos 8 → ID 13  (CMS Frontend)  era 3
     *  pos 9 → ID 132 (Sistema)       era 9
     */

    private array $newPositions = [
        1   => 1,
        2   => 2,
        40  => 3,
        53  => 4,
        76  => 5,
        88  => 6,
        102 => 7,
        13  => 8,
        132 => 9,
    ];

    private array $oldPositions = [
        1   => 1,
        2   => 2,
        13  => 3,
        40  => 4,
        53  => 5,
        76  => 6,
        88  => 7,
        102 => 8,
        132 => 9,
    ];

    public function up(): void
    {
        $this->applyPositions($this->newPositions);
    }

    public function down(): void
    {
        $this->applyPositions($this->oldPositions);
    }

    private function applyPositions(array $positions): void
    {
        $ids = array_keys($positions);

        $case = collect($positions)
            ->map(fn($pos, $id) => "WHEN {$id} THEN {$pos}")
            ->implode(' ');

        DB::table('backendmenus')
            ->whereIn('id', $ids)
            ->update(['position' => DB::raw("CASE id {$case} END")]);

        foreach ($positions as $menuId => $pos) {
            DB::table('backendmenu_users')
                ->whereNull('parent_id')
                ->where('backendmenu_id', $menuId)
                ->update(['position' => $pos]);
        }
    }
};
