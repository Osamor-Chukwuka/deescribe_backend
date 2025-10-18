<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class LookupController extends Controller
{
    //
    public function Categories(){
        $categories = Category::select('id', 'name')->get();

        return response()->json([
            'status' => true,
            'data' => $categories
        ]);
    }
}
