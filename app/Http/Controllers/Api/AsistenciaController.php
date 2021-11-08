<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Empleado;
use Illuminate\Http\Request;
use App\Models\Asistencia;
use App\Http\Requests\MarcarAsistenciaRequest;
use Illuminate\Support\Facades\DB;
use App\Custom\Validaciones;
use Illuminate\Validation\ValidationException;

class AsistenciaController extends Controller
{
    public function marcarAsistencia(MarcarAsistenciaRequest $request)
    {

        $validaciones = new Validaciones();
        date_default_timezone_set('America/Lima');
        $fecha = date('Y-m-d');
        // $fecha = '2021-10-31';
        $hora = date('H:i:s');
        // $hora = "14:00:00";
        $dispo = $validaciones->idDis();
        // $ip = request()->ip();       
        // $ip = inet_ntop(request()->ip());
        // $ipv6 = $validaciones->getRealIP();
        $ipv6 = $validaciones->getRealIP();
        $ipv4 = hexdec(substr($ipv6, 0, 2)) . "." . hexdec(substr($ipv6, 2, 2)) . "." . hexdec(substr($ipv6, 5, 2)) . "." . hexdec(substr($ipv6, 7, 2));
        $SO = $validaciones->getSO($request->useragent);

        $empleado = Empleado::where('Emp_Dni', $request->dni)->first();

        $asis_estado = DB::select("select fu_verificar_puntualidad('$request->dni','$hora') AS Respuesta");

        $atributo = "Respuesta";

        if ($asis_estado[0]->$atributo == "2" || $asis_estado[0]->$atributo == "1") {
            $detalle_asi = (int)$asis_estado[0]->$atributo;
            if (!$empleado == null) {
                $msg2 = DB::select("select fu_verificar_intentos('$fecha', '$hora', $empleado->Emp_Id, '$request->plataforma', '$SO', '$dispo', '$request->useragent', '$request->usertime', '$ipv4', $detalle_asi) AS respuesta");
                if ($msg2[0]->respuesta == 1) {
                    if ($detalle_asi == 1) {
                        $msg = "Gracias " . $empleado->Emp_Nombre . ", marcaste asistencia puntual ";
                    } else {
                        $msg = "Gracias " . $empleado->Emp_Nombre . ", marcaste asistencia tarde ";
                    }
                } else {
                    $msg = $empleado->Emp_Nombre . " ya marcaste asistencia.";
                }
            }
        } else {
            $msg = $asis_estado[0]->$atributo;
        }



        return response()->json([
            'respuesta' => 'true',
            'mensaje' => $msg


        ], 200);
    }
}
