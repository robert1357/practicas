<?php
class ReniecAPI {
    private $token = '60758efe05040c810c9e782cf019fc974f890e45648b071354a4d2ffd470cfb7';
    private $url = "https://apiperu.dev/api/dni";

    public function consultarDNI($dni) {
        $params = json_encode(['dni' => $dni]);
        
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $this->url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_POSTFIELDS => $params,        
            CURLOPT_HTTPHEADER => [
                'Accept: application/json',
                'Content-Type: application/json',
                'Authorization: Bearer ' . $this->token
            ],        
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);

        if ($err) {
            return ['error' => "cURL Error: " . $err];
        } else {
            $data = json_decode($response, true);
            
            if ($data['success']) {
                return [
                    'success' => true,
                    'nombres' => $data['data']['nombres'],
                    'apellido_paterno' => $data['data']['apellido_paterno'],
                    'apellido_materno' => $data['data']['apellido_materno'],
                    'apellidos' => $data['data']['apellido_paterno'] . ' ' . $data['data']['apellido_materno']
                ];
            } else {
                return ['error' => 'DNI no encontrado'];
            }
        }
    }
}

// Manejo de la solicitud AJAX
if(isset($_GET['dni'])) {
    $api = new ReniecAPI();
    $resultado = $api->consultarDNI($_GET['dni']);
    
    header('Content-Type: application/json');
    echo json_encode($resultado);
    exit();
}
?>