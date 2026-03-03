<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

$file = "data.json";

if (!file_exists($file)) {
    file_put_contents($file, json_encode([]));
}

function readData($file){
    return json_decode(file_get_contents($file), true);
}

function writeData($file,$data){
    file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT));
}

$method = $_SERVER["REQUEST_METHOD"];
$id = $_GET["id"] ?? null;
$data = readData($file);

/* ================= FORMAT DETECTION ================= */

    $contentType = $_SERVER["CONTENT_TYPE"] ?? "";
    $responseFormat = "json";

    if (str_contains($contentType, "xml")) {
        $responseFormat = "xml";
    }

switch($method){

// ================= GET =================
case "GET":
    if($responseFormat === "json"){
    if ($id){
        foreach($data as $item){
            if ($item["id"] == $id){
                echo json_encode($item);
                exit;
            }
        }
        http_response_code(404);
        echo json_encode(["message"=>"Record non trovato"]);
    } else {
        echo json_encode($data);
    }
    }
    else{
        if ($id){
            foreach($data as $item){
                if ($item["id"] == $id){
                    header("Content-Type: application/xml");
                    echo "<record>";
                    echo "<id>{$item['id']}</id>";
                    echo "<nome>{$item['nome']}</nome>";
                    echo "<valore>{$item['valore']}</valore>";
                    echo "</record>";
                    exit;
                }
            }
            http_response_code(404);
            header("Content-Type: application/xml");
            echo "<response>";
            echo "<message>Record non trovato</message>";
            echo "</response>";
        } else {
            header("Content-Type: application/xml");
            echo "<records>";
            foreach($data as $item){
                echo "<record>";
                echo "<id>{$item['id']}</id>";
                echo "<nome>{$item['nome']}</nome>";
                echo "<valore>{$item['valore']}</valore>";
                echo "</record>";
            }
            echo "</records>";
        }
    }
break;

// ================= POST =================
case "POST":
    if($responseFormat === "json"){
    $input = json_decode(file_get_contents("php://input"), true);

    $newId = count($data)>0 ? max(array_column($data,'id'))+1 : 1;

    $newRecord = [
        "id"=>$newId,
        "nome"=>$input["nome"] ?? "",
        "valore"=>$input["valore"] ?? 0
    ];

    $data[] = $newRecord;
    writeData($file,$data);

    http_response_code(201);
    echo json_encode($newRecord);}
    else{
        $input = json_decode(file_get_contents("php://input"), true);

    $newId = count($data)>0 ? max(array_column($data,'id'))+1 : 1;

    $newRecord = [
        "id"=>$newId,
        "nome"=>$input["nome"] ?? "",
        "valore"=>$input["valore"] ?? 0
    ];

    $data[] = $newRecord;
    writeData($file,$data);

    http_response_code(201);
    header("Content-Type: application/xml");
    echo "<record>";
    echo "<id>{$newRecord['id']}</id>";
    echo "<nome>{$newRecord['nome']}</nome>";
    echo "<valore>{$newRecord['valore']}</valore>";
    echo "</record>";
    }
break;

// ================= PUT =================
case "PUT":
    if($responseFormat === "json"){
    if (!$id){
        http_response_code(400);
        echo json_encode(["message"=>"ID richiesto"]);
        exit;
    }

    $input = json_decode(file_get_contents("php://input"), true);

    foreach($data as &$item){
        if ($item["id"] == $id){
            $item["nome"] = $input["nome"] ?? $item["nome"];
            $item["valore"] = $input["valore"] ?? $item["valore"];
            writeData($file,$data);
            echo json_encode($item);
            exit;
        }
    }

    http_response_code(404);
    echo json_encode(["message"=>"Record non trovato"]);
    }else{
        if (!$id){
            http_response_code(400);
            header("Content-Type: application/xml");
            echo "<response>";
            echo "<status>400</status>";
            echo "<message>ID richiesto</message>";
            echo "</response>";
            exit;
        }
    
        $input = json_decode(file_get_contents("php://input"), true);
    
        foreach($data as &$item){
            if ($item["id"] == $id){
                $item["nome"] = $input["nome"] ?? $item["nome"];
                $item["valore"] = $input["valore"] ?? $item["valore"];
                writeData($file,$data);
                header("Content-Type: application/xml");
                echo "<record>";
                echo "<id>{$item['id']}</id>";
                echo "<nome>{$item['nome']}</nome>";
                echo "<valore>{$item['valore']}</valore>";
                echo "</record>";
                exit;
            }
        }
        http_response_code(404);
        header("Content-Type: application/xml");
        echo "<response>";
        echo "<status>404</status>";
        echo "<message>Record non trovato</message>";
        echo "</response>";
    }
break;

// ================= DELETE =================
case "DELETE":
    if($responseFormat === "json"){
    if (!$id){
        http_response_code(400);
        echo json_encode(["message"=>"ID richiesto"]);
        exit;
    }

    foreach($data as $key=>$item){
        if ($item["id"] == $id){
            unset($data[$key]);
            $data = array_values($data);
            writeData($file,$data);
            echo json_encode(["message"=>"Eliminato"]);
            exit;
        }
    }

    http_response_code(404);
    echo json_encode(["message"=>"Record non trovato"]);
    }else{
        if (!$id){
            http_response_code(400);
            header("Content-Type: application/xml");
            echo "<response>";
            echo "<message>ID richiesto</message>";
            echo "</response>";
            exit;
        }
    
        foreach($data as $key=>$item){
            if ($item["id"] == $id){
                unset($data[$key]);
                $data = array_values($data);
                writeData($file,$data);
                header("Content-Type: application/xml");
                echo "<response>";
                echo "<message>Eliminato</message>";
                echo "</response>";
                exit;
            }
        }
    
        http_response_code(404);
        header("Content-Type: application/xml");
        echo "<response>";
        echo "<message>Record non trovato</message>";
        echo "</response>";
    }
break;

default:
    http_response_code(405);
    header("Content-Type: application/xml");
    echo "<response>";
    echo "<status>405</status>";
    echo "<message>Metodo non consentito</message>";
    echo "</response>";
}