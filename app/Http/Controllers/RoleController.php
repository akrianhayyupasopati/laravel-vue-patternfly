<?php

namespace App\Http\Controllers;

use App\Models\Role;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use DB;

class RoleController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {if (!$user = JWTAuth::parseToken()->authenticate()) {
        return response()->json([
            'status' => 'error',
            'error' => 'token_not_found',
            'msg' => 'Token not provided.',
        ], 404);
    }

        if (!$user->can('roles-list')) {
            return response([
                'status' => 'error',
                'error' => 'unauthorized',
                'msg' => 'Permission denied.',
            ], 401);
        }

        $query = Role::query()->with('perms');

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
            $roles = $query->paginate($perPage);
        } else {
            $roles = $export;
        }
        return response([
            'status' => 'success',
            'roles' => $roles,
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
    public function store(Request $request)
    {
        if (!$user = JWTAuth::parseToken()->authenticate()) {
            return response()->json([
                'status' => 'error',
                'error' => 'token_not_found',
                'msg' => 'Token not provided.',
            ], 404);
        }

        if (!$user->can('manage-roles')) {
            return response([
                'status' => 'error',
                'error' => 'unauthorized',
                'msg' => 'Permission denied.',
            ], 401);
        }

        $role = new Role;
        $role->display_name = $request->display_name;
        $role->name = $request->name;
        $role->description = $request->description;
        if ($role->save()) {
            foreach ($request->permissions as $permission) {
                $role->attachPermission($permission['value']);
            }
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
    public function update(Request $request, $id)
    {
        if (!$user = JWTAuth::parseToken()->authenticate()) {
            return response()->json([
                'status' => 'error',
                'error' => 'token_not_found',
                'msg' => 'Token not provided.',
            ], 404);
        }

        if (!$user->can('manage-roles')) {
            return response([
                'status' => 'error',
                'error' => 'unauthorized',
                'msg' => 'Permission denied.',
            ], 401);
        }

        $role = Role::find($id);
        $role->display_name = $request->display_name;
        $role->name = $request->name;
        $role->description = $request->description;
        if ($role->save()) {
            DB::table('permission_role')->where('role_id', $id)->delete();
            foreach ($request->permissions as $permission) {
                $role->attachPermission($permission['value']);
            }
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

        $role = Role::find($id);
        $role->delete();
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

        $roles = $request->roles;
        foreach ($roles as $role) {
            Role::find($role['id'])->delete();
        }

        return response([
            'status' => 'success',
        ]);

    }
}
