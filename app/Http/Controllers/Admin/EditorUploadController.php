<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class EditorUploadController extends Controller
{
    public function uploadImage(Request $request)
    {
        $request->validate([
            'image' => ['required', 'image', 'mimes:jpeg,png,jpg,gif,svg,webp', 'max:5120'],
        ]);

        $ruta = $request->file('image')->store('editor', 'public');

        // Retorna la ruta relativa '/storage/editor/...' sin host ni protocolo
        return response()->json([
            'success' => 1,
            'file' => [
                'url' => '/storage/'.$ruta,
            ],
        ]);
    }
}
