<?php

namespace App\Http\Controllers;

use App\Models\Media;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class MediaController extends Controller
{
    /**
     * Upload un média pour le profil de l'utilisateur connecté
     */
    public function uploadMedia(Request $request)
    {
        $user = auth()->user();
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Utilisateur non authentifié'
            ], 401);
        }

        if (!$user->profil) {
            return response()->json([
                'success' => false,
                'message' => 'Profil non trouvé'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'file' => 'required|file|max:10240', // 10MB max
            'type' => 'required|string|in:avatar,cover,gallery,verification_document',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Erreurs de validation',
                'errors' => $validator->errors()
            ], 422);
        }

        $file = $request->file('file');
        $type = $request->type;

        // Si c'est un avatar, supprimer l'ancien
        if ($type === 'avatar') {
            $oldAvatar = $user->profil->media()->where('type', 'avatar')->first();
            if ($oldAvatar) {
                Storage::delete($oldAvatar->path);
                $oldAvatar->delete();
            }
        }

        // Générer un nom unique
        $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
        
        // Stocker le fichier
        $path = $file->storeAs('uploads/profil/' . $user->id, $filename, 'public');

        // Créer l'enregistrement en base
        $media = $user->profil->media()->create([
            'filename' => $filename,
            'original_filename' => $file->getClientOriginalName(),
            'path' => $path,
            'type' => $type,
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Média uploadé avec succès',
            'data' => [
                'media' => $media,
                'url' => $media->url
            ]
        ], 201);
    }

    /**
     * Supprimer un média
     */
    public function deleteMedia($id)
    {
        $user = auth()->user();
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Utilisateur non authentifié'
            ], 401);
        }

        $media = $user->profil->media()->find($id);

        if (!$media) {
            return response()->json([
                'success' => false,
                'message' => 'Média non trouvé'
            ], 404);
        }

        // Supprimer le fichier du stockage
        Storage::delete($media->path);
        
        // Supprimer l'enregistrement
        $media->delete();

        return response()->json([
            'success' => true,
            'message' => 'Média supprimé avec succès'
        ], 200);
    }
}
