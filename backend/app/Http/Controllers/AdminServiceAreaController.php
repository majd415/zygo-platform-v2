<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
class AdminServiceAreaController extends Controller
{
    public function index() { return response()->json([]); }
    public function store(Request $request) { return response()->json(["message" => "OK"]); }
    public function show($id) { return response()->json([]); }
    public function update(Request $request, $id) { return response()->json(["message" => "OK"]); }
    public function destroy($id) { return response()->json(["message" => "Deleted"]); }
}
