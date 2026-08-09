<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SettingsController extends Controller
{
    /**
     * Actualiza la preferencia de tema del usuario autenticado.
     * El front ya aplica el cambio al instante vía localStorage;
     * esto solo lo persiste en BD para que viaje entre dispositivos.
     */
    public function actualizarTema(Request $request)
    {
        $datos = $request->validate([
            'tema' => ['required', 'in:claro,oscuro'],
        ]);

        $usuario = $request->user();
        $usuario->update(['tema' => $datos['tema']]);

        return response()->json(['tema' => $usuario->tema]);
    }

    /**
     * Sube la foto de perfil y su miniatura.
     * La miniatura llega ya generada desde el front (canvas): el contenedor
     * no tiene GD ni Imagick instalados, así que redimensionar en servidor
     * no es viable sin tocar la imagen Docker.
     */
    public function subirFotoPerfil(Request $request)
    {
        $request->validate([
            'foto' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'miniatura' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:512'],
        ]);

        $usuario = $request->user();
        $carpeta = 'perfiles/' . $usuario->id;

        // limpia las fotos anteriores para no acumular archivos huérfanos
        if ($usuario->foto_perfil) {
            Storage::disk('public')->delete($usuario->foto_perfil);
        }
        if ($usuario->foto_perfil_thumb) {
            Storage::disk('public')->delete($usuario->foto_perfil_thumb);
        }

        $nombreBase = (string) Str::uuid();
        $rutaFoto = $request->file('foto')->storeAs($carpeta, $nombreBase . '.' . $request->file('foto')->extension(), 'public');
        $rutaThumb = $request->file('miniatura')->storeAs($carpeta, $nombreBase . '_thumb.' . $request->file('miniatura')->extension(), 'public');

        $usuario->update([
            'foto_perfil' => $rutaFoto,
            'foto_perfil_thumb' => $rutaThumb,
        ]);

        return response()->json([
            'foto_perfil_url' => $usuario->foto_perfil_url,
            'foto_perfil_thumb_url' => $usuario->foto_perfil_thumb_url,
        ]);
    }

    public function borrarFotoPerfil(Request $request)
    {
        $usuario = $request->user();

        if ($usuario->foto_perfil) {
            Storage::disk('public')->delete($usuario->foto_perfil);
        }
        if ($usuario->foto_perfil_thumb) {
            Storage::disk('public')->delete($usuario->foto_perfil_thumb);
        }

        $usuario->update(['foto_perfil' => null, 'foto_perfil_thumb' => null]);

        return response()->json(['mensaje' => 'Foto de perfil eliminada']);
    }
}
