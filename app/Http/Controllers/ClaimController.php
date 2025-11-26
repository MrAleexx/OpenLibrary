<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Claim;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;

class ClaimController extends Controller
{
    public function create()
    {
        return Inertia::render('Claims/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'service_type' => 'required|string',
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'document_number' => 'required|string',
            'phone' => 'required|string',
            'subject' => 'required|string',
            'description' => 'required|string',
            'file' => 'nullable|file|max:10240', // 10MB max
        ]);

        $filePath = null;
        if ($request->hasFile('file')) {
            $filePath = $request->file('file')->store('claims', 'public');
        }

        Claim::create([
            'email' => $validated['email'],
            'service_type' => $validated['service_type'],
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'document_number' => $validated['document_number'],
            'phone' => $validated['phone'],
            'subject' => $validated['subject'],
            'description' => $validated['description'],
            'file_path' => $filePath,
        ]);

        return redirect()->back()->with('success', 'Reclamo enviado correctamente. Nos pondremos en contacto pronto.');
    }

    public function index()
    {
        $claims = Claim::latest()->paginate(10);
        return Inertia::render('admin/claims/Index', [
            'claims' => $claims
        ]);
    }

    public function show(Claim $claim)
    {
        return Inertia::render('admin/claims/Show', [
            'claim' => $claim
        ]);
    }
}
