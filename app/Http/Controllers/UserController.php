<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;

class UserController extends Controller
{
    /**
     * Display a listing of users.
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', User::class);

        $query = User::with('role')
            ->orderBy('created_at', 'desc');

        // Filtros
        if ($request->filled('buscar')) {
            $buscar = $request->buscar;
            $query->where(function($q) use ($buscar) {
                $q->where('nombre', 'ilike', "%{$buscar}%")
                  ->orWhere('apellidos', 'ilike', "%{$buscar}%")
                  ->orWhere('ci', 'ilike', "%{$buscar}%")
                  ->orWhere('email', 'ilike', "%{$buscar}%");
            });
        }

        if ($request->filled('role_id')) {
            $query->where('role_id', $request->role_id);
        }

        if ($request->filled('estado')) {
            $query->where('estado', $request->estado === 'activo');
        }

        $usuarios = $query->paginate(15)->withQueryString();

        // Estadísticas
        $totalUsuarios = User::count();
        $usuariosActivos = User::where('estado', true)->count();
        $usuariosInactivos = User::where('estado', false)->count();

        $roles = Role::all(['id', 'nombre']);

        return Inertia::render('Usuarios/Index', [
            'usuarios' => $usuarios,
            'roles' => $roles,
            'filters' => $request->only(['buscar', 'role_id', 'estado']),
            'estadisticas' => [
                'total' => $totalUsuarios,
                'activos' => $usuariosActivos,
                'inactivos' => $usuariosInactivos,
            ],
        ]);
    }

    /**
     * Show the form for creating a new user.
     */
    public function create()
    {
        $this->authorize('create', User::class);

        $roles = Role::all(['id', 'nombre']);

        return Inertia::render('Usuarios/Create', [
            'roles' => $roles,
        ]);
    }

    /**
     * Store a newly created user in storage.
     */
    public function store(StoreUserRequest $request)
    {
        $data = $request->validated();
        $data['password'] = Hash::make($data['password']);
        $data['estado'] = $data['estado'] ?? true;

        $usuario = User::create($data);

        return redirect()->route('usuarios.index')
            ->with('success', 'Usuario creado exitosamente.');
    }

    /**
     * Display the specified user.
     */
    public function show(User $usuario)
    {
        $this->authorize('view', $usuario);

        $usuario->load(['role', 'ventas' => function($query) {
            $query->latest()->take(5);
        }]);

        // Estadísticas del usuario
        $stats = [
            'total_ventas' => $usuario->ventas()->count(),
            'total_gastado' => $usuario->ventas()->sum('total'),
            'creditos_activos' => $usuario->ventas()->whereHas('credito', function($query) {
                $query->where('estado', '!=', 'pagado');
            })->count(),
            'total_credito' => $usuario->ventas()->whereHas('credito', function($query) {
                $query->where('estado', '!=', 'pagado');
            })->with('credito')->get()->sum(function($venta) {
                return $venta->credito ? $venta->credito->monto_pendiente : 0;
            }),
        ];

        return Inertia::render('Usuarios/Show', [
            'usuario' => $usuario,
            'estadisticas' => $stats,
        ]);
    }

    /**
     * Show the form for editing the specified user.
     */
    public function edit(User $usuario)
    {
        $this->authorize('update', $usuario);

        $usuario->load('role');
        $roles = Role::all(['id', 'nombre']);

        return Inertia::render('Usuarios/Edit', [
            'usuario' => $usuario,
            'roles' => $roles,
        ]);
    }

    /**
     * Update the specified user in storage.
     */
    public function update(UpdateUserRequest $request, User $usuario)
    {
        $data = $request->validated();

        $usuario->update($data);

        return redirect()->route('usuarios.index')
            ->with('success', 'Usuario actualizado exitosamente.');
    }

    /**
     * Remove the specified user from storage.
     */
    public function destroy(User $usuario)
    {
        $this->authorize('delete', $usuario);

        // Prevenir que el usuario se elimine a sí mismo
        if ($usuario->id === auth()->id()) {
            return redirect()->route('usuarios.index')
                ->with('error', 'No puedes eliminar tu propia cuenta.');
        }

        // Prevenir eliminar usuarios con créditos activos
        $tieneCreditosActivos = $usuario->ventas()->whereHas('credito', function($query) {
            $query->where('estado', '!=', 'pagado');
        })->exists();
        
        if ($tieneCreditosActivos) {
            return redirect()->route('usuarios.index')
                ->with('error', 'No se puede eliminar un usuario con créditos activos.');
        }

        $usuario->delete();

        return redirect()->route('usuarios.index')
            ->with('success', 'Usuario eliminado exitosamente.');
    }

    /**
     * Toggle user status (active/inactive).
     */
    public function toggleEstado(User $usuario)
    {
        $this->authorize('update', $usuario);

        if ($usuario->id === auth()->id()) {
            return redirect()->route('usuarios.index')
                ->with('error', 'No puedes desactivar tu propia cuenta.');
        }

        $usuario->update([
            'estado' => !$usuario->estado
        ]);

        $estado = $usuario->estado ? 'activado' : 'desactivado';

        return redirect()->route('usuarios.index')
            ->with('success', "Usuario {$estado} exitosamente.");
    }
}

