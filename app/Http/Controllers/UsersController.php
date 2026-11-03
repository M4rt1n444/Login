<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Contracts\View\View;

class UsersController extends Controller
{
    /**
     * Muestra un listado paginado con datos no sensibles de las cuentas.
     */
    public function index(): View
    {
        $users = User::query()
            ->select(['id', 'name', 'email', 'created_at'])
            ->orderBy('id')
            ->paginate(10);

        return view('dashboard', compact('users'));
    }
}
