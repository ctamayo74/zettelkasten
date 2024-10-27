<?php

namespace App\Http\Controllers;

use App\Models\Zettel;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ZettelController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        //
        return view('zettels.index');
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
        //
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'body' => 'required|string',
            'reference' => 'nullable|string|max:255',
        ]);

        $request->user()->zettels()->create($validated);

        return redirect(route('zettels.index'));
    }

    /**
     * Display the specified resource.
     */
    public function show(Zettel $zettel)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Zettel $zettel)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Zettel $zettel)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Zettel $zettel)
    {
        //
    }
}
