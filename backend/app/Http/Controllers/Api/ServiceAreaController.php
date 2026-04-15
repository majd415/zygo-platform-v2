<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\ServiceArea;

class ServiceAreaController extends Controller
{
    public function index()
    {
        return response()->json(ServiceArea::where('is_active', true)->get());
    }
}
