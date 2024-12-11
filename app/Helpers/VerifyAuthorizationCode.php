<?php

namespace App\Helpers;

use App\Models\Order;
use Illuminate\Support\Facades\Hash;

trait VerifyAuthorizationCode
{
    public function canModifyBill(Order $order, string $authorizationCode)
    {
        foreach ($order->store->permissions as $permission) {
            if (Hash::check($authorizationCode, $permission->authorization_code)) {
                return $permission->modify_bill;
            }
        }
        return false;
    }

    public function canRefund(Order $order, string $authorizationCode)
    {
        foreach ($order->store->permissions as $permission) {
            if (Hash::check($authorizationCode, $permission->authorization_code)) {
                return $permission->refund;
            }
        }
        return false;
    }
}
