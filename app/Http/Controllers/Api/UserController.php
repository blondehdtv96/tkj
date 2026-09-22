<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::with('kelas');

        if ($request->filled('role')) {
            $query->where('role', $request->get('role'));
        }

        if ($request->filled('kelas_id')) {
            $query->where('kelas_id', $request->integer('kelas_id'));
        }

        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(fn ($q) => $q->where('name', 'like', "%{$search}%")
                ->orWhere('nis', 'like', "%{$search}%")
                ->orWhere('username', 'like', "%{$search}%"));
        }

        return UserResource::collection($query->orderBy('name')->paginate(20));
    }

    public function store(UserRequest $request)
    {
        $data = $request->validated();
        $data['password'] = Hash::make($data['password']);

        $user = User::create($data);

        return (new UserResource($user->load('kelas')))->response()->setStatusCode(201);
    }

    public function update(UserRequest $request, User $user)
    {
        $data = $request->validated();

        if (! empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $user->update($data);

        return new UserResource($user->load('kelas'));
    }

    public function destroy(User $user)
    {
        $user->delete();

        return response()->json(['message' => 'Pengguna dihapus.']);
    }
}
