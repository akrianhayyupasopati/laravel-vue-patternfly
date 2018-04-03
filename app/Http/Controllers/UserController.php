<?php

namespace App\Http\Controllers;

use App\Http\Requests\EditUserRequest;
use App\Http\Requests\UserRequest;
use App\User;
use DB;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if (!$user = JWTAuth::parseToken()->authenticate()) {
            return response()->json([
                'status' => 'error',
                'error' => 'token_not_found',
                'msg' => 'Token not provided.',
            ], 404);
        }

        if (!$user->can('users-list')) {
            return response([
                'status' => 'error',
                'error' => 'unauthorized',
                'msg' => 'Permission denied.',
            ], 401);
        }

        $query = User::query()->with('roles.perms');

        if ($request->has('search')) {
            $keywords = $request->get('search');
            foreach ($keywords as $keyword) {
                $keyword = json_decode($keyword);
                $query->where($keyword->name, 'LIKE', '%' . $keyword->value . '%');
            }
        }

        if ($request->has('sortBy') && $request->has('sortDirection')) {
            $order = $request->get('sortBy');
            $direction = ($request->get('sortDirection') == 'ascending') ? 'asc' : 'desc';
            $query->orderBy($order, $direction);
        }

        $export = $query->get();
        if ($request->has('perPage')) {
            $perPage = $request->get('perPage');
            $users = $query->paginate($perPage);
        } else {
            $users = $export;
        }
        return response([
            'status' => 'success',
            'users' => $users,
            'export' => $export,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(UserRequest $request)
    {
        if (!$user = JWTAuth::parseToken()->authenticate()) {
            return response()->json([
                'status' => 'error',
                'error' => 'token_not_found',
                'msg' => 'Token not provided.',
            ], 404);
        }

        if (!$user->can('manage-users')) {
            return response([
                'status' => 'error',
                'error' => 'unauthorized',
                'msg' => 'Permission denied.',
            ], 401);
        }

        $user = new User;
        $user->email = $request->email;
        $user->name = $request->name;
        $user->password = bcrypt($request->password);
        if ($user->save()) {
            $user->attachRole($request->role);
        }
        return response([
            'status' => 'success',
        ], 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(EditUserRequest $request, $id)
    {
        if (!$user = JWTAuth::parseToken()->authenticate()) {
            return response()->json([
                'status' => 'error',
                'error' => 'token_not_found',
                'msg' => 'Token not provided.',
            ], 404);
        }

        if (!$user->can('manage-users')) {
            return response([
                'status' => 'error',
                'error' => 'unauthorized',
                'msg' => 'Permission denied.',
            ], 401);
        }

        $user = User::find($id);
        $user->email = $request->email;
        $user->name = $request->name;
        if ($request->has('password') && $request->password != null) {
            $user->password = bcrypt($request->password);
        }

        if ($user->save()) {
            DB::table('role_user')->where('user_id', $id)->delete();
            $user->attachRole($request->role);
        }
        return response([
            'status' => 'success',
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if (!$user = JWTAuth::parseToken()->authenticate()) {
            return response()->json([
                'status' => 'error',
                'error' => 'token_not_found',
                'msg' => 'Token not provided.',
            ], 404);
        }

        if (!$user->can('manage-users')) {
            return response([
                'status' => 'error',
                'error' => 'unauthorized',
                'msg' => 'Permission denied.',
            ], 401);
        }

        $user = User::find($id);
        $user->delete();
        return response([
            'status' => 'success',
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function groupDelete(Request $request)
    {
        if (!$user = JWTAuth::parseToken()->authenticate()) {
            return response()->json([
                'status' => 'error',
                'error' => 'token_not_found',
                'msg' => 'Token not provided.',
            ], 404);
        }

        if (!$user->can('manage-users')) {
            return response([
                'status' => 'error',
                'error' => 'unauthorized',
                'msg' => 'Permission denied.',
            ], 401);
        }

        $users = $request->users;
        foreach ($users as $user) {
            User::find($user['id'])->delete();
        }

        return response([
            'status' => 'success',
        ]);

    }
}
