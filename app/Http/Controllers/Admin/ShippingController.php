<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class ShippingController extends Controller
{
    /* ---------- PAGE ---------- */
    public function index()
    {
        $settings = [
            'free_over' => Setting::get('free_ship_over', '50'),
            'cost'      => Setting::get('ship_cost', '4.99'),
            'included_weight_kg' => Setting::get('ship_included_weight_kg', '1'),
            'surcharge_per_kg' => Setting::get('ship_surcharge_per_kg', '0'),
            'min_days'  => Setting::get('delivery_min_days', '5'),
            'max_days'  => Setting::get('delivery_max_days', '7'),
        ];

        return view('admin.pages.shipping', compact('settings'));
    }

    /* ---------- DELIVERY SETTINGS ---------- */
    public function saveSettings(Request $request)
    {
        $data = $request->validate([
            'free_ship_over'    => 'required|numeric|min:0|max:100000',
            'ship_cost'         => 'required|numeric|min:0|max:10000',
            'included_weight_kg' => 'required|numeric|min:0|max:100000',
            'surcharge_per_kg' => 'required|numeric|min:0|max:10000',
            'delivery_min_days' => 'required|integer|min:1|max:60',
            'delivery_max_days' => 'required|integer|min:1|max:90',
        ]);

        if ((int) $data['delivery_max_days'] < (int) $data['delivery_min_days']) {
            return response()->json(['status' => 'error', 'message' => 'Maximum delivery days must be at least the minimum.'], 422);
        }

        Setting::set('free_ship_over', (string) (float) $data['free_ship_over']);
        Setting::set('ship_cost', (string) (float) $data['ship_cost']);
        Setting::set('ship_included_weight_kg', (string) (float) $data['included_weight_kg']);
        Setting::set('ship_surcharge_per_kg', (string) (float) $data['surcharge_per_kg']);
        Setting::set('delivery_min_days', (string) (int) $data['delivery_min_days']);
        Setting::set('delivery_max_days', (string) (int) $data['delivery_max_days']);

        return response()->json(['status' => 'success', 'message' => 'Delivery settings saved!']);
    }
}