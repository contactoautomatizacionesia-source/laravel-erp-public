<?php

namespace App\Enums;

enum DoubleApprovalActionTypes: string
{
    case CLUBPOINT_SET_MASSIVE_POINTS = 'set_massive_points';
    case CLUBPOINT_CONVERT_POINT_TO_WALLET = 'convert_point_to_wallet';

    public function label(): string
    {
        return match($this) {
            self::CLUBPOINT_SET_MASSIVE_POINTS => __('double_approval.action_types.clubpoint_set_massive_points'),
            self::CLUBPOINT_CONVERT_POINT_TO_WALLET => __('double_approval.action_types.clubpoint_convert_point_to_wallet'),
        };
    }
}
