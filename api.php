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

switch($method){

// ================= GET =================
case "GET":
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
break;

// ================= POST =================
case "POST":
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
    echo json_encode($newRecord);
break;

// ================= PUT =================
case "PUT":
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
break;

// ================= DELETE =================
case "DELETE":
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
break;

default:
    http_response_code(405);
    echo json_encode(["message"=>"Metodo non consentito"]);
}
