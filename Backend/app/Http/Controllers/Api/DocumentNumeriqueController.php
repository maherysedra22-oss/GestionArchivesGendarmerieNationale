<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DocumentNumerique;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DocumentNumeriqueController extends Controller
{
    public function upload(Request $request)
    {
        $validated = $request->validate([
            'file' => [
                'required',
                'file',
                'mimes:pdf,jpg,jpeg,png,doc,docx',
                'max:10240',
            ],
        ]);

        $file = $validated['file'];

        $nomOriginal = $file->getClientOriginalName();
        $extension = strtolower($file->getClientOriginalExtension());
        $typeMime = $file->getMimeType();
        $taille = $file->getSize();

        $checksumSha256 = hash_file(
            'sha256',
            $file->getRealPath()
        );

        $nomStockage = Str::uuid()->toString() . '.' . $extension;

        $chemin = 'documents/' . $nomStockage;

        Storage::disk('public')->putFileAs(
            'documents',
            $file,
            $nomStockage
        );

        $document = DocumentNumerique::create([
            'nom_original' => $nomOriginal,
            'nom_stockage' => $nomStockage,
            'extension' => $extension,
            'type_mime' => $typeMime,
            'taille' => $taille,
            'chemin' => $chemin,
            'checksum_sha256' => $checksumSha256,
            'id_utilisateur_upload' => $request->user()->id_utilisateur,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Document numérique uploadé avec succès.',
            'data' => $document,
        ], 201);
    }

    public function index()
    {
    $documents = DocumentNumerique::with('utilisateurUpload')
        ->latest('num_doc')
        ->paginate(10);

    return response()->json([
        'success' => true,
        'message' => 'Liste des documents récupérée avec succès.',
        'data' => $documents,
    ]);
    }
}