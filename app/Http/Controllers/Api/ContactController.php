<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ContactController extends Controller
{
    public function index()
    {
        $fc = fopen('/tmp/contacts.txt', 'a');
        $contacts = Contact::with('tags')->get();
        fwrite($fc, var_export($contacts, true));
        return response()->json(Contact::with('tags')->get());
    }

    public function store(Request $request)
    {
        $fc = fopen('/tmp/contacts.txt', 'a');
        fwrite($fc, var_export($request->all(), true));
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:contacts',
            'phone' => 'nullable|string|max:20',
            'tags' => 'nullable|array',
            'tags.*' => 'exists:tags,id'
        ]);

        fwrite($fc, var_export($validator->errors(), true));

        if ($validator->fails()) {
            fwrite($fc, var_export($validator->errors(), true));
            return response()->json($validator->errors(), 422);
        }

        $contact = Contact::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
        ]);
        fwrite($fc, var_export($contact, true));
        if ($request->has('tags')) {
            fwrite($fc, var_export($request->tags, true));
            $contact->tags()->sync($request->tags);
        }
        fwrite($fc, var_export($contact->load('tags'), true));
        return response()->json($contact->load('tags'), 201);
    }

    public function show(Contact $contact)
    {
        return response()->json($contact->load('tags'));
    }

    public function update(Request $request, Contact $contact)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:contacts,email,' . $contact->id,
            'phone' => 'nullable|string|max:20',
            'tags' => 'nullable|array',
            'tags.*' => 'exists:tags,id'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $contact->update([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
        ]);
        if ($request->has('tags')) {
            $contact->tags()->sync($request->tags);
        }
        return response()->json($contact->load('tags'));
    }

    public function destroy(Contact $contact)
    {
        $contact->delete();
        return response()->json(null, 204);
    }
}