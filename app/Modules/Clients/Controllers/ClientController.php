<?php

namespace App\Modules\Clients\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Clients\Models\Client;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ClientController extends Controller
{
    public function index()
    {
        $clients = Client::withCount('projects')->latest()->paginate(15);
        return Inertia::render('Clients/Index', ['clients' => $clients]);
    }

    public function create()
    {
        return Inertia::render('Clients/Create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'company_name'  => 'required|string|max:255',
            'contact_email' => 'nullable|email|max:255',
            'notes'         => 'nullable|string',
        ]);
        Client::create($data);
        return redirect()->route('clients.index')->with('success', 'Client created successfully.');
    }

    public function show(Client $client)
    {
        $client->load(['projects' => fn($q) => $q->withCount('tasks')->latest()]);
        return Inertia::render('Clients/Show', ['client' => $client]);
    }

    public function edit(Client $client)
    {
        return Inertia::render('Clients/Edit', ['client' => $client]);
    }

    public function update(Request $request, Client $client)
    {
        $data = $request->validate([
            'company_name'  => 'required|string|max:255',
            'contact_email' => 'nullable|email|max:255',
            'notes'         => 'nullable|string',
        ]);
        $client->update($data);
        return redirect()->route('clients.show', $client)->with('success', 'Client updated.');
    }

    public function destroy(Client $client)
    {
        $client->delete();
        return redirect()->route('clients.index')->with('success', 'Client deleted.');
    }
}
