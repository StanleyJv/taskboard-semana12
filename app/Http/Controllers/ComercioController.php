<?php

namespace App\Http\Controllers;

use App\Models\Comercio;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ComercioController extends Controller
{
    public function index(Request $request): View
    {
        $comercios = Comercio::when(
            $request->buscar,
            fn ($q) => $q->where(
                'nombre_comercio',
                'like',
                "%{$request->buscar}%"
            )
        )
            ->withCount('transacciones')
            ->orderByDesc('transacciones_count')
            ->get();

        return view('comercios.index', compact('comercios'));
    }

    public function show(Comercio $comercio): View
    {
        $comercio->load('transacciones');

        return view('comercios.show', compact('comercio'));
    }
}