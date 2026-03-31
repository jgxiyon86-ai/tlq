<?php

namespace App\Http\Controllers;

use App\Models\Content;
use App\Models\Series;
use Illuminate\Http\Request;

class ContentController extends Controller
{
    public function index(Request $request)
    {
        $query = Content::query()->with('series');
        
        if ($request->has('series_id') && $request->series_id != '') {
            $query->where('series_id', $request->series_id);
        }

        if ($request->filled('search')) {
            $searchTerm = '%' . $request->search . '%';
            $query->where(function($q) use ($searchTerm) {
                $q->where('surah_ayah', 'like', $searchTerm)
                  ->orWhere('insight', 'like', $searchTerm)
                  ->orWhere('translation', 'like', $searchTerm)
                  ->orWhere('arabic_text', 'like', $searchTerm);
            });
        }

        $contents = $query->orderBy('number', 'asc')->paginate(15)->appends($request->all());
        $series = Series::all();
        
        if ($request->ajax()) {
            return response()->json([
                'html' => view('admin.contents._table', compact('contents'))->render(),
                'pagination' => (string) $contents->links()
            ]);
        }

        return view('admin.contents.index', compact('contents', 'series'));
    }

    public function create()
    {
        $series = Series::all();
        return view('admin.contents.create', compact('series'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'number' => 'required|integer',
            'series_id' => 'required|exists:series,id',
            'surah_ayah' => 'required|string',
            'arabic_text' => 'required|string',
            'translation' => 'required|string',
            'insight' => 'required|string',
            'action_plan' => 'required|string',
        ]);

        Content::create($data);

        return redirect()->route('admin.contents.create', ['series_id' => $request->series_id])
                         ->with('success', 'Konten Al-Quran berhasil ditambahkan! Silakan lanjut menambahkan konten untuk series ini.');
    }

    public function edit(Content $content)
    {
        $series = Series::all();
        return view('admin.contents.edit', compact('content', 'series'));
    }

    public function update(Request $request, Content $content)
    {
        $data = $request->validate([
            'number' => 'required|integer',
            'series_id' => 'required|exists:series,id',
            'surah_ayah' => 'required|string',
            'arabic_text' => 'required|string',
            'translation' => 'required|string',
            'insight' => 'required|string',
            'action_plan' => 'required|string',
        ]);

        $content->update($data);

        return redirect()->route('admin.contents.index', ['series_id' => $request->series_id])
                         ->with('success', 'Konten Al-Quran berhasil diperbarui!');
    }

    public function destroy(Content $content)
    {
        $content->delete();
        return back()->with('success', 'Konten berhasil dihapus.');
    }
}
