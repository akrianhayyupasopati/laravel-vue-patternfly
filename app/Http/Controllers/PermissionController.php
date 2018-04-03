<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;

class PermissionController extends Controller
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

        if (!$user->can('permissions-list')) {
            return response([
                'status' => 'error',
                'error' => 'unauthorized',
                'msg' => 'Permission denied.',
            ], 401);
        }

        $query = Permission::query();

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
            $permissions = $query->paginate($perPage);
        } else {
            $permissions = $export;
        }
        return response([
            'status' => 'success',
            'permissions' => $permissions,
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

        if (!$user->can('manage-permissions')) {
            return response([
                'status' => 'error',
                'error' => 'unauthorized',
                'msg' => 'Permission denied.',
            ], 401);
        }

        $permission = new Permission;
        $permission->display_name = $request->display_name;
        $permission->name = $request->name;
        $permission->description = $request->description;
        $permission->save();
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

        if (!$user->can('manage-permissions')) {
            return response([
                'status' => 'error',
                'error' => 'unauthorized',
                'msg' => 'Permission denied.',
            ], 401);
        }

        $permission = Permission::find($id);
        $permission->display_name = $request->display_name;
        $permission->name = $request->name;
        $permission->description = $request->description;
        $permission->save();
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

        $permission = Permission::find($id);
        $permission->delete();
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

        $permissions = $request->permissions;
        foreach ($permissions as $permission) {
            Permission::find($permission['id'])->delete();
        }

        return response([
            'status' => 'success',
        ]);

    }
}
