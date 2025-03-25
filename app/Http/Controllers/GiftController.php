<?php

namespace App\Http\Controllers;

use App\Http\Resources\GiftResource;
use App\Models\Gift;
use Illuminate\Http\Request;

class GiftController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'receiver_id' => 'required|exists:users,id',
            'gift_id' => 'required|exists:gifts,id',
        ]);

        $gift =  new Gift();
        $gift->gift_id = $data['gift_id'];
        $gift->sender_id = $request->user()->id;
        $gift->receiver_id = $data['receiver_id'];
        if ($gift->sender->wallet < $gift->item->price) {
            return response()->json(['message' => 'Insufficient funds'], 403);
        }
        $gift->save();
        return GiftResource::make($gift);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
