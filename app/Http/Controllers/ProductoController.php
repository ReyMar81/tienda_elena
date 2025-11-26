<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Models\Categoria;
use App\Http\Requests\StoreProductoRequest;
use App\Http\Requests\UpdateProductoRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

/**
 * ProductoController - CRUD de Productos
 * 
 * Implementación MVC completa con:
 * - Policy para autorización dinámica desde BD
 * - Form Requests para validación
 * - Eager Loading para optimización
 * - Inertia.js para renderizar vistas Vue
 */
class ProductoController extends Controller
{
    /**
     * Constructor - Registrar Policy
     */
    public function __construct()
    {
        $this->authorizeResource(Producto::class, 'producto');
    }

    /**
     * Display a listing of the resource.
     * 
     * Lista productos con paginación y búsqueda
     * Incluye eager loading de categoría para evitar N+1
     */
    public function index(Request $request)
    {
        $search = $request->input('search', '');
        
        $productos = Producto::with(['categoria', 'imagenes'])
            ->when($search, function ($query, $search) {
                $query->where('nombre', 'ilike', "%{$search}%")
                      ->orWhere('codigo', 'ilike', "%{$search}%");
            })
            ->orderBy('created_at', 'desc')
            ->paginate(15)
            ->withQueryString();

        $rol = 'propietario'; // default
        if ($request->user() && $request->user()->role) {
            $rol = $request->user()->role->nombre;
        }

        return Inertia::render('Productos/Index', [
            'productos' => $productos,
            'filters' => ['search' => $search],
            'rol' => $rol,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     * 
     * Muestra formulario de creación con lista de categorías
     */
    public function create()
    {
        $categorias = Categoria::select('id', 'nombre')
            ->orderBy('nombre')
            ->get();

        return Inertia::render('Productos/Create', [
            'categorias' => $categorias,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     * 
     * Validación via StoreProductoRequest
     * Manejo de imagen (upload a storage)
     */
    public function store(StoreProductoRequest $request)
    {
        $data = $request->validated();

        // Crear producto sin imagen
        $producto = Producto::create($data);

        // Manejar upload de imágenes (puede ser 1 o múltiples)
        if ($request->hasFile('imagen')) {
            $imagenes = is_array($request->file('imagen')) 
                ? $request->file('imagen') 
                : [$request->file('imagen')];

            foreach ($imagenes as $imagen) {
                $path = $imagen->store('productos', 'public');
                $producto->imagenes()->create(['url' => $path]);
            }
        }

        return redirect()->route('productos.index')
            ->with('success', 'Producto creado exitosamente.');
    }

    /**
     * Display the specified resource.
     * 
     * Vista detalle de un producto
     */
    public function show(Producto $producto)
    {
        $producto->load(['categoria', 'promociones', 'imagenes']);

        return Inertia::render('Productos/Show', [
            'producto' => $producto,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     * 
     * Formulario de edición con datos precargados
     */
    public function edit(Producto $producto)
    {
        $categorias = Categoria::select('id', 'nombre')
            ->orderBy('nombre')
            ->get();

        $producto->load('imagenes');

        return Inertia::render('Productos/Edit', [
            'producto' => $producto,
            'categorias' => $categorias,
        ]);
    }

    /**
     * Update the specified resource in storage.
     * 
     * Validación via UpdateProductoRequest
     * Manejo de imagen (reemplaza anterior si existe nueva)
     */
    public function update(UpdateProductoRequest $request, Producto $producto)
    {
        $data = $request->validated();

        // Actualizar datos del producto
        $producto->update($data);

        // Manejar upload de nuevas imágenes
        if ($request->hasFile('imagen')) {
            // Opcional: Eliminar imágenes anteriores si quieres reemplazarlas
            // foreach ($producto->imagenes as $img) {
            //     if (Storage::disk('public')->exists($img->url)) {
            //         Storage::disk('public')->delete($img->url);
            //     }
            //     $img->delete();
            // }

            $imagenes = is_array($request->file('imagen')) 
                ? $request->file('imagen') 
                : [$request->file('imagen')];

            foreach ($imagenes as $imagen) {
                $path = $imagen->store('productos', 'public');
                $producto->imagenes()->create(['url' => $path]);
            }
        }

        return redirect()->route('productos.index')
            ->with('success', 'Producto actualizado exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     * 
     * Elimina producto si no tiene ventas asociadas (validado en Policy)
     */
    public function destroy(Producto $producto)
    {
        // Eliminar imágenes físicas y registros
        foreach ($producto->imagenes as $imagen) {
            if (Storage::disk('public')->exists($imagen->url)) {
                Storage::disk('public')->delete($imagen->url);
            }
            $imagen->delete();
        }

        $producto->delete();

        return redirect()->route('productos.index')
            ->with('success', 'Producto eliminado exitosamente.');
    }

    /**
     * Eliminar una imagen específica del producto
     */
    public function deleteImage(Producto $producto, $imagenId)
    {
        $imagen = $producto->imagenes()->findOrFail($imagenId);
        
        // Eliminar archivo físico
        if (Storage::disk('public')->exists($imagen->url)) {
            Storage::disk('public')->delete($imagen->url);
        }
        
        // Eliminar registro de BD
        $imagen->delete();

        return back()->with('success', 'Imagen eliminada exitosamente.');
    }
}

