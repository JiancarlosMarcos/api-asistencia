<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegistroUserRequest;
use App\Http\Requests\AccesoUserRequest;
use App\Models\User;
use App\Models\Empleado;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Hash;



class AutenticarController extends Controller
{
    public function registro(RegistroUserRequest $request)
    {
        $user = new User();
        $user->usu_Id_Emp_fk = $request->id;
        $user->usu_Password = bcrypt($request->password);
        $user->usu_Tipo_User_Id_fk = $request->tipoUsuario;
        $user->save();

        return response()->json([
            'res' => 'true',
            'msg' => 'Usuario registrado :)'
        ], 200);
    }

    public function acceso(AccesoUserRequest $request)
    {
        $empleado = Empleado::where('Emp_Dni', $request->dni)->first();
        if ($empleado == null) {
            throw ValidationException::withMessages([
                'smg' => ['El dni es incorrecto'],
            ]);
        }

        $user = User::where('usu_Id_Emp_fk', $empleado->Emp_Id)->first();

        if (!$user || !Hash::check($request->password, $user->usu_Password)) {
            throw ValidationException::withMessages([
                'smg' => ['La contraseña es incorrecto'],
            ]);
        }
        $token = $user->createToken($request->dni)->plainTextToken;
        // $asis_estado = DB::select("select fu_verificar_puntualidad('$request->dni','$hora')");

        $asis_empleado = DB::select("call pa_listar_asistencia_empleados_dni('$empleado->Emp_Dni')");

        return response()->json([
            'res' => 'true',
            'token' => $token,
            'asistencias' => $asis_empleado
        ], 200);
    }

    public function cerrarSesion(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json([
            'res' => 'true',
            'token' => 'Token eliminado correctamente'
        ], 200);
    }
}
