<?php

namespace App\Http\Controllers;

use App\Models\Location;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class LocationController extends Controller
{
    public function index()
    {
        return view('admin.locations', [
            'locations' => Location::with('rooms')->orderBy('name')->get(),
            'editing' => null,
        ]);
    }

    public function store(Request $request)
    {
        Location::create($request->validate([
            'name' => 'required|max:100|unique:locations,name',
            'description' => 'nullable|max:255',
        ]));

        return redirect('/admin/locations')->with('success', 'Location added. You can now add rooms to it.');
    }

    public function edit(Location $location)
    {
        return view('admin.locations', [
            'locations' => Location::with('rooms')->orderBy('name')->get(),
            'editing' => $location,
        ]);
    }

    public function update(Request $request, Location $location)
    {
        $location->update($request->validate([
            'name' => ['required', 'max:100', Rule::unique('locations', 'name')->ignore($location->id)],
            'description' => 'nullable|max:255',
        ]));

        return redirect('/admin/locations')->with('success', 'Location updated.');
    }

    public function destroy(Location $location)
    {
        if ($location->rooms()->count() > 0) {
            return back()->with('error', 'Cannot delete '.$location->name.'. Remove or move its rooms first.');
        }

        $location->delete();

        return back()->with('success', 'Location deleted.');
    }
}
