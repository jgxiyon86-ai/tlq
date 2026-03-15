<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\ManualPage;
use App\Models\Series;

class ManualPageController extends Controller
{
    public function index(Request $request)
    {
        $series = Series::all();
        $query = ManualPage::with('series')->orderBy('series_id')->orderBy('page_number');

        if ($request->has('series_id') && $request->series_id != '') {
            $query->where('series_id', $request->series_id);
        }

        $pages = $query->paginate(15)->appends($request->all());
        return view('admin.manual_pages.index', compact('pages', 'series'));
    }

    public function create()
    {
        $series = Series::all();
        return view('admin.manual_pages.create', compact('series'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'series_id' => 'required|exists:series,id',
            'page_number' => 'required|integer',
            'title' => 'nullable|string|max:255',
            'content' => 'required|string',
            'image_url' => 'nullable|string',
        ]);

        ManualPage::create($data);

        return redirect()->route('admin.manual_pages.index')->with('success', 'Halaman panduan berhasil ditambahkan.');
    }

    public function edit(ManualPage $manualPage)
    {
        $series = Series::all();
        return view('admin.manual_pages.edit', compact('manualPage', 'series'));
    }

    public function update(Request $request, ManualPage $manualPage)
    {
        $data = $request->validate([
            'series_id' => 'required|exists:series,id',
            'page_number' => 'required|integer',
            'title' => 'nullable|string|max:255',
            'content' => 'required|string',
            'image_url' => 'nullable|string',
        ]);

        $manualPage->update($data);

        return redirect()->route('admin.manual_pages.index')->with('success', 'Halaman panduan berhasil diperbarui.');
    }

    public function destroy(ManualPage $manualPage)
    {
        $manualPage->delete();
        return back()->with('success', 'Halaman panduan berhasil dihapus.');
    }
}
