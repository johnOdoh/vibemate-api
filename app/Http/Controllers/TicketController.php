<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Resources\TicketResource;
use Illuminate\Support\Facades\Storage;

class TicketController extends Controller
{
    public function index()
    {
        $tickets = request()->user()->tickets()->latest()->get();
        // $tickets = Ticket::orderBy('created_at', 'desc')->get();
        return response()->json([
            'tickets' => TicketResource::collection($tickets),
            'open_count' => $tickets->where('status', 'open')->count(),
            'pending_count' => $tickets->where('status', 'in_progress')->count(),
            'closed_count' => $tickets->where('status', 'closed')->count()
        ]);
    }

    public function show($id)
    {
        // Fetch a single ticket by ID
        $ticket = Ticket::findOrFail($id);
        return response()->json($ticket);
    }

    public function store(Request $request)
    {
        // Validate and create a new ticket
        $data = $request->validate([
            'type' => 'required|string|max:255',
            'subject' => 'required|string|max:255',
            'description' => 'required|string',
            'priority' => 'nullable|string',
            'attachment' => 'nullable|file|mimes:jpg,jpeg,png|max:5124'
        ]);

        if ($request->hasFile('attachment')) {
            $attachment = $request->file('attachment');
            $path = 'tickets/' . $attachment->hashName();
            Storage::disk('s3')->put($path, file_get_contents($attachment), 'public');
            $data['attachment'] = $path;
        }
        $data['user_id'] = $request->user()->id;
        $data['ticket_number'] = strtoupper(Str::random(2)).rand(10000000, 99999999); // Generate a random ticket number
        Ticket::create($data);
        return response()->json(['message' => 'Ticket created successfully'], 201);
    }

    public function update(Request $request, $id)
    {
        // Validate and update an existing ticket
        $validatedData = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string',
            'status' => 'sometimes|required|string|in:open,closed',
        ]);

        $ticket = Ticket::findOrFail($id);
        $ticket->update($validatedData);
        return response()->json($ticket);
    }

    public function destroy(Ticket $ticket)
    {
        if($ticket->attachment) {
            Storage::disk('s3')->delete($ticket->attachment);
        }
        $ticket->delete();
        return response()->json(['message' => 'Ticket deleted'], 200);
    }
}
