<?php
// app/Http/Controllers/EgyptShippingController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ShippingFee;
use Rinvex\Country\CountryLoader;
use Rinvex\Country\Countries;

class ShippingSettingController extends Controller
{
    public function getGovernorates()
    {
        $governorates = [
            "Cairo",
            "Alexandria",
            "Giza",
            "Dakahlia",
            "Red Sea",
            "Beheira",
            "Fayoum",
            "Gharbiya",
            "Ismailia",
            "Menofia",
            "Minya",
            "Qaliubiya",
            "New Valley",
            "Suez",
            "Aswan",
            "Assiut",
            "Beni Suef",
            "Port Said",
            "Damietta",
            "Sharkia",
            "South Sinai",
            "Kafr El Sheikh",
            "Matrouh",
            "Luxor",
            "Qena",
            "North Sinai",
            "Sohag"
        ];

        return response()->json($governorates);
    }

    public function storeShippingFee(Request $request)
    {
        $request->validate([
            'governorate' => 'required|string',
            'fee' => 'required|numeric',
        ]);

        ShippingFee::updateOrCreate(
            ['governorate' => $request->governorate],
            ['fee' => $request->fee]
        );

        return response()->json(['message' => 'Shipping fee saved successfully']);
    }

    public function index()
    {
        return ShippingFee::all();
    }
}
