<?php

namespace App\Http\Controllers;

use App\Models\AssetTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;


class DashboardController extends Controller
{
    public function index(){
        return Inertia::render('Dashboard1');
    }

    public function session_get(){
        $user = Auth::user();
        return response()->json([
            'season' => $user->season
        ]);
    }

    public function sessionChange(Request $request){
        $user = Auth::user();
        $user->season = $request->season;
        $user->save();

        return response()->json([
            'message' => 'সিজন সফলভাবে পরিবর্তন করা হয়েছে',
        ]);
    }   
}
