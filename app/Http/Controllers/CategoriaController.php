<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Http\Requests\StoreCategoriaRequest;
use App\Http\Requests\UpdateCategoriaRequest;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CategoriaController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Categoria::class, 'categoria');
    }

    public function index(Request $request)
    {
        $search = $request->input('search', '');
        
        $categorias = Categoria::withCount('productos')
            ->when($search, function ($query, $search) {
                $query->where('nombre', 'ilike', "%{$search}%");
            })
            ->orderBy('nombre')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Categorias/Index', [
            'categorias' => $categorias,
            'filters' => ['search' => $search],
        ]);
    }

    public function create()
    {
        return Inertia::render('Categorias/Create');
    }

    public function store(StoreCategoriaRequest $request)
    {
        $categoria = Categoria::create($request->validated());

        // Si viene desde modal de productos, retornar JSON
        if ($request->wantsJson() || $request->header('X-Inertia')) {
            return back()->with([
                'success' => 'Categoría creada exitosamente.',
                'categoria' => $categoria,
            ]);
        }

        return redirect()->route('categorias.index')
            ->with('success', 'Categoría creada exitosamente.');
    }

    public function show(Categoria $categoria)
    {
        $categoria->loadCount('productos');
        
        // Cargar productos de la categoría con paginación
        $productos = $categoria->productos()
            ->with('imagenes')
            ->orderBy('nombre')
            ->paginate(12);

        return Inertia::render('Categorias/Show', [
            'categoria' => $categoria,
            'productos' => $productos,
        ]);
    }

    public function edit(Categoria $categoria)
    {
        return Inertia::render('Categorias/Edit', [
            'categoria' => $categoria,
        ]);
    }

    public function update(UpdateCategoriaRequest $request, Categoria $categoria)
    {
        $categoria->update($request->validated());

        return redirect()->route('categorias.index')
            ->with('success', 'Categoría actualizada exitosamente.');
    }

    public function destroy(Categoria $categoria)
    {
        // Verificar si tiene productos asociados
        $productosCount = $categoria->productos()->count();
        
        if ($productosCount > 0) {
            // Al eliminar la categoría, los productos quedarán sin categoría (categoria_id = null)
            $categoria->delete();
            
            return redirect()->route('categorias.index')
                ->with('warning', "Categoría eliminada. {$productosCount} producto(s) quedaron sin categoría asignada.");
        }
        
        $categoria->delete();

        return redirect()->route('categorias.index')
            ->with('success', 'Categoría eliminada exitosamente.');
    }
}

