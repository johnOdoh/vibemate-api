<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use Illuminate\Support\Str;
use Illuminate\Http\Request;

class TicketController extends Controller
{
    public function index()
    {
        // Fetch all tickets
        $tickets = Ticket::all();
        return response()->json($tickets);
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
            $filename = time() . '.' . $attachment->getClientOriginalExtension();
            $attachment->move(public_path('attachments'), $filename);
            $data['attachment'] = $filename;
        }
        $data['user_id'] = $request->user()->id;
        $data['ticket_number'] = strtoupper(Str::random(2)).rand(10000000, 99999999); // Generate a random ticket number
        $ticket = Ticket::create($data);
        return response()->json($ticket, 201);
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
            unlink(public_path('storage/'.$ticket->attachment));
        }
        $ticket->delete();
        return response()->json(['message' => 'Ticket deleted'], 200);
    }
}
