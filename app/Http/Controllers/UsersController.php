<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Contracts\View\View;

class UsersController extends Controller
{
    /**
     * Muestra un listado paginado con datos no sensibles de las cuentas.
     */
    public function index()
    {
        $users = User::query()
            ->select(['id', 'name', 'email', 'created_at'])
            ->orderBy('id')
            ->paginate(10);

        return response()
            ->view('dashboard', compact('users'))
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
    }
}
