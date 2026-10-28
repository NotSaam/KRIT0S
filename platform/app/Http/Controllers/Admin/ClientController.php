<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Client;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class ClientController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('admin/Clients', [
            'clients' => Client::query()->withCount(['engagements', 'users'])->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $client = Client::create([
            ...$this->validated($request),
            'slug' => Str::slug($request->input('name')).'-'.Str::lower(Str::random(4)),
        ]);
        ActivityLog::record('client.created', "Cliente «{$client->name}» dado de alta", $client);

        return back()->with('success', 'Cliente creado.');
    }

    public function update(Request $request, Client $client): RedirectResponse
    {
        $client->update($this->validated($request, $client));
        ActivityLog::record('client.updated', "Cliente «{$client->name}» actualizado", $client);

        return back()->with('success', 'Cliente actualizado.');
    }

    public function destroy(Client $client): RedirectResponse
    {
        ActivityLog::record('client.deleted', "Cliente «{$client->name}» eliminado");
        $client->delete();

        return back()->with('success', 'Cliente eliminado.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request, ?Client $client = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:120', Rule::unique('clients')->ignore($client)],
            'sector' => ['nullable', 'string', 'max:80'],
            'contact_name' => ['nullable', 'string', 'max:120'],
            'contact_email' => ['nullable', 'email', 'max:160'],
        ]);
    }
}
