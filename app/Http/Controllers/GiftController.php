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
            'item_id' => 'required|exists:items,id',
        ]);
        $gift =  new Gift();
        $gift->item_id = $data['item_id'];
        $gift->sender_id = $request->user()->id;
        $gift->receiver_id = $data['receiver_id'];
        if ($gift->sender->wallet < $gift->item->price) {
            return response()->json(['message' => 'Insufficient funds'], 403);
        }
        $gift->sender->wallet -= $gift->item->price;
        $gift->receiver->wallet += $gift->item->price;
        $gift->push();
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
