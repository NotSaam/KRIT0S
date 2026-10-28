<?php

namespace App\Http\Controllers\Admin;

use App\Enums\Role;
use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Client;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('admin/Users', [
            'users' => User::query()->with('client:id,name')->orderBy('role')->orderBy('name')->get(),
            'roles' => Role::options(),
            'clients' => Client::query()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:160', 'unique:users'],
            'password' => ['required', Password::defaults()],
            ...$this->roleRules(),
        ]);

        $user = User::create($this->normalize($data));
        ActivityLog::record('user.created', "Usuario {$user->email} creado con rol {$user->role->label()}", $user);

        return back()->with('success', 'Usuario creado.');
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate($this->roleRules());

        // Never let the last admin lock everyone out.
        if ($user->isAdmin() && $data['role'] !== Role::Admin->value
            && User::query()->where('role', Role::Admin)->count() === 1) {
            return back()->withErrors(['role' => 'Debe quedar al menos un administrador.']);
        }

        $before = $user->role->value;
        $user->update($this->normalize($data));
        ActivityLog::record('user.role_changed', "Rol de {$user->email}: {$before} → {$user->role->value}", $user);

        return back()->with('success', 'Usuario actualizado.');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        abort_if($user->is($request->user()), 422, 'No puedes eliminar tu propia cuenta desde aquí.');

        ActivityLog::record('user.deleted', "Usuario {$user->email} eliminado");
        $user->delete();

        return back()->with('success', 'Usuario eliminado.');
    }

    /**
     * @return array<string, mixed>
     */
    private function roleRules(): array
    {
        return [
            'role' => ['required', Rule::enum(Role::class)],
            'client_id' => ['nullable', 'required_if:role,client', 'exists:clients,id'],
        ];
    }

    /**
     * Only client users are tied to a client organisation.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function normalize(array $data): array
    {
        if ($data['role'] !== Role::Client->value) {
            $data['client_id'] = null;
        }

        return $data;
    }
}
