<?php

namespace App\Http\Controllers;

use App\Models\Agenda;
use App\Models\AgendaExternalMember;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

class AgendaExternalMemberController extends Controller
{
    public function index(Agenda $agenda)
    {
        Gate::authorize('view', $agenda);

        $members = $agenda->externalMembers()->latest()->get();

        return response()->json([
            'items' => $members->map(fn (AgendaExternalMember $m) => [
                'id' => $m->id,
                'name' => $m->name,
                'organization' => $m->organization,
                'signature_url' => $m->signature_image_path
                    ? '/storage/' . ltrim($m->signature_image_path, '/')
                    : null,
                'created_at' => $m->created_at?->format('H:i'),
            ])->values(),
        ]);
    }

    public function store(Request $request, Agenda $agenda)
    {
        Gate::authorize('update', $agenda);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'organization' => 'nullable|string|max:255',
            'signature' => 'nullable|file|image|mimes:png,jpg,jpeg|max:2048',
        ]);

        $path = null;

        if ($request->hasFile('signature')) {
            $path = $request->file('signature')->store('external-signatures', 'public');
        }

        $member = $agenda->externalMembers()->create([
            'name' => $validated['name'],
            'organization' => $validated['organization'] ?? null,
            'signature_image_path' => $path,
        ]);

        return response()->json([
            'item' => [
                'id' => $member->id,
                'name' => $member->name,
                'organization' => $member->organization,
                'signature_url' => $member->signature_image_path
                    ? '/storage/' . ltrim($member->signature_image_path, '/')
                    : null,
                'created_at' => $member->created_at?->format('H:i'),
            ],
        ], 201);
    }

    public function update(Request $request, Agenda $agenda, AgendaExternalMember $member)
    {
        Gate::authorize('update', $agenda);

        abort_unless($member->agenda_id === $agenda->id, 404);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'organization' => 'nullable|string|max:255',
            'signature' => 'nullable|file|image|mimes:png,jpg,jpeg|max:2048',
            'remove_signature' => 'nullable|boolean',
        ]);

        if ($request->boolean('remove_signature') && $member->signature_image_path) {
            Storage::disk('public')->delete($member->signature_image_path);
            $member->signature_image_path = null;
        }

        if ($request->hasFile('signature')) {
            if ($member->signature_image_path) {
                Storage::disk('public')->delete($member->signature_image_path);
            }
            $member->signature_image_path = $request->file('signature')->store('external-signatures', 'public');
        }

        $member->name = $validated['name'];
        $member->organization = $validated['organization'] ?? null;
        $member->save();

        return response()->json([
            'item' => [
                'id' => $member->id,
                'name' => $member->name,
                'organization' => $member->organization,
                'signature_url' => $member->signature_image_path
                    ? '/storage/' . ltrim($member->signature_image_path, '/')
                    : null,
                'created_at' => $member->created_at?->format('H:i'),
            ],
        ]);
    }

    public function destroy(Agenda $agenda, AgendaExternalMember $member)
    {
        Gate::authorize('update', $agenda);

        abort_unless($member->agenda_id === $agenda->id, 404);

        if ($member->signature_image_path) {
            Storage::disk('public')->delete($member->signature_image_path);
        }

        $member->delete();

        return response()->json(['deleted' => true]);
    }

    public function publicShow(Agenda $agenda)
    {
        abort_unless($agenda->type === 'rapat', 404);

        if (request()->wantsJson()) {
            $members = $agenda->externalMembers()->latest()->get();

            return response()->json([
                'items' => $members->map(fn (AgendaExternalMember $m) => [
                    'id' => $m->id,
                    'name' => $m->name,
                    'organization' => $m->organization,
                    'signature_url' => $m->signature_image_path
                        ? '/storage/' . ltrim($m->signature_image_path, '/')
                        : null,
                    'created_at' => $m->created_at?->format('H:i'),
                ])->values(),
            ]);
        }

        return view('public.external-attend', compact('agenda'));
    }

    public function publicStore(Request $request, Agenda $agenda)
    {
        abort_unless($agenda->type === 'rapat', 404);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'organization' => 'nullable|string|max:255',
            'signature' => 'required|file|image|mimes:png,jpg,jpeg|max:2048',
        ]);

        $path = $request->file('signature')->store('external-signatures', 'public');

        $member = $agenda->externalMembers()->create([
            'name' => $validated['name'],
            'organization' => $validated['organization'] ?? null,
            'signature_image_path' => $path,
        ]);

        return response()->json([
            'message' => 'Tanda tangan berhasil disimpan.',
            'item' => [
                'id' => $member->id,
                'name' => $member->name,
                'organization' => $member->organization,
                'signature_url' => '/storage/' . ltrim($member->signature_image_path, '/'),
                'created_at' => $member->created_at?->format('H:i'),
            ],
        ], 201);
    }
}
