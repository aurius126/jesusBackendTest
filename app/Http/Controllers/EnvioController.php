<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;
use App\Events\ApiRequestSuccess;
use Illuminate\Support\Facades\Session;
use App\Events\CounterUpdated;
use Pusher;

class EnvioController extends Controller
{
    public function enviarPeticion()
    {
        try {
            // Crear una instancia de GuzzleHttp
            $client = new Client();

            // URL de la API
            $url = 'https://api-test.envia.com/ship/generate/';

            // Datos a enviar en la petición
            $data = [
                "origin"=> [
                    "number"=> "83",
                    "postalCode"=> "77036",
                    "type"=> "origin",
                    "company"=> "JesusTest",
                    "name"=> "Jesus Dominguez",
                    "email"=> "jesusleonel126@gmail.com",
                    "phone"=> "9831003989",
                    "country"=> "MX",
                    "street"=> "Chetumal",
                    "city"=> "Chetumal",
                    "state"=> "QR",
                    "district"=> "20 de Noviembre"
                ],
                "destination"=> [
                    "number"=> "83",
                    "postalCode"=> "77036",
                    "type"=> "destination",
                    "company"=> "None",
                    "name"=> "Jesus Dominguez",
                    "email"=> "jesusleonel126@gmail.com",
                    "phone"=> "9831003989",
                    "country"=> "MX",
                    "street"=> "Corgeca",
                    "city"=> "Chetumal",
                    "state"=> "QR",
                    "district"=> "20 de Noviembre"
                ],
                "packages"=> [
                    [
                        "type"=> "box",
                        "content"=> "Furniture",
                        "amount"=> 1,
                        "name"=> "Furniture",
                        "declaredValue"=> 100,
                        "lengthUnit"=> "CM",
                        "weightUnit"=> "KG",
                        "weight"=> 20,
                        "dimensions"=> [
                            "length"=> 9,
                            "width"=> 12,
                            "height"=> 52
                        ]
                    ]
                ],
                "settings"=> [
                    "currency"=> "MXN",
                    "printFormat"=> "PDF",
                    "printSize"=> "PAPER_4X8"
                ],
                "shipment"=> [
                    "carrier"=> "dhl",
                    "service"=> "ground",
                    "reverse_pickup"=> 0,
                    "type"=> 1
                ]
            ];

            // Obtener el contador actual de la sesión o establecerlo en 0 si no existe
                $contador = session('contador', 0);

            // Establecemos la APIKey de Envia
            $apiKey = 'Bearer '.env('ENVIA_API_KEY');
            
            // Realizar la solicitud POST a la API
            $response = $client->post($url, [
                'json' => $data,
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Authorization' => $apiKey
                ]
            ]);
            
            // Obtener el cuerpo de la respuesta
            $responseData = $response->getBody()->getContents();
            // Decodificar el JSON de la respuesta
            $responseData = json_decode($responseData, true);
            if ($responseData["meta"] != "error") {
                            // Incrementar el contador
            $contador++;
            // Guardar el nuevo valor del contador en la sesión
            Session::put('contador', $contador);
            
            // Creamos la conexion con el cluster
            $options = array(
                'cluster' => env('PUSHER_APP_CLUSTER'),
                'useTLS' => true
            );

            //Creamos el evento con las codigos secretos
            $pusher = new Pusher\Pusher(
                env('PUSHER_APP_KEY'),
                env('PUSHER_APP_SECRET'),
                env('PUSHER_APP_ID'),
                $options
            );

            //Mandamos el trigger con el evento y el numero de veces de ejecucion dentro de la sesion.
            $pusher->trigger('contador', 'nuevaGuia', $contador);

            // Aquí puedes manejar los datos de la respuesta según tus necesidades
            return redirect()->back();
            }else{
                return redirect()->back();
            }
        } catch (\Exception $e) {
            // Manejar errores
            return response()->json(['error' => $e->getMessage()], 500);
        }

    }
}