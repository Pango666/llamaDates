<?php

namespace App\Http\Controllers;

use App\Models\ConsentTemplate;
use Illuminate\Http\Request;

class ConsentTemplateController extends Controller
{
    public function index() {
        $templates = ConsentTemplate::orderBy('name')->get();
        return view('admin.consents.templates.index', compact('templates'));
    }

    public function create() { return view('admin.consents.templates.create'); }

    public function store(Request $request) {
        $data = $request->validate([
            'name' => ['required','string','max:120'],
            'body' => ['required','string'],
        ]);
        ConsentTemplate::create($data);
        return redirect()->route('admin.consents.templates')->with('ok','Plantilla creada.');
    }

    public function edit(ConsentTemplate $template) {
        return view('admin.consents.templates.edit', compact('template'));
    }

    public function update(Request $request, ConsentTemplate $template) {
        $data = $request->validate([
            'name' => ['required','string','max:120'],
            'body' => ['required','string'],
        ]);
        $template->update($data);
        return redirect()->route('admin.consents.templates')->with('ok','Plantilla actualizada.');
    }

    public function destroy(ConsentTemplate $template) {
        $template->delete();
        return back()->with('ok','Plantilla eliminada.');
    }
}
