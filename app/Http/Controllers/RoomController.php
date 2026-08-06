<?php

namespace App\Http\Controllers;

use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RoomController extends Controller
{
    public function search(Request $request)
    {
        if ($request->filled('id')) {
            $room = Room::find($request->id);

            return response()->json([
                'items' => $room ? [[
                    'id' => $room->id,
                    'name' => $room->room_name,
                ]] : [],
                'has_more' => false,
            ]);
        }

        $search = trim((string) $request->input('q'));
        $operator = $this->searchOperator();

        $rooms = Room::query()
            ->orderBy('room_name')
            ->when($search !== '', function ($query) use ($search, $operator) {
                $query->where('room_name', $operator, "%{$search}%");
            })
            ->simplePaginate(10);

        return response()->json([
            'items' => collect($rooms->items())
                ->map(fn (Room $room) => [
                    'id' => $room->id,
                    'name' => $room->room_name,
                ])
                ->values(),
            'has_more' => $rooms->hasMorePages(),
        ]);
    }

    public function index(Request $request)
    {
        $q = trim((string) $request->input('q'));
        $operator = $this->searchOperator();

        $rooms = Room::query()
            ->orderBy('room_name')
            ->when($q !== '', fn ($query) => $query->where('room_name', $operator, "%{$q}%"))
            ->paginate(15)
            ->withQueryString();

        return view('admin.rooms.index', compact('rooms', 'q'));
    }

    public function create()
    {
        return view('admin.rooms.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'room_name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        Room::create($validated);

        return redirect()->route('admin.rooms.index')
            ->with('success', 'Ruangan berhasil ditambahkan.');
    }

    public function edit(Room $room)
    {
        return view('admin.rooms.edit', compact('room'));
    }

    public function update(Request $request, Room $room)
    {
        $validated = $request->validate([
            'room_name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $room->update($validated);

        return redirect()->route('admin.rooms.index')
            ->with('success', 'Ruangan berhasil diperbarui.');
    }

    public function destroy(Room $room)
    {
        $room->delete();

        return redirect()->route('admin.rooms.index')
            ->with('success', 'Ruangan berhasil dihapus.');
    }

    private function searchOperator(): string
    {
        return DB::connection()->getDriverName() === 'pgsql' ? 'ilike' : 'like';
    }
}
