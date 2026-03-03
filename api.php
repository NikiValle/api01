<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Allow-Headers: Content-Type");

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

/* ================= FORMAT DETECTION ================= */

$contentType = $_SERVER["CONTENT_TYPE"] ?? "";
$responseFormat = "json";

if (str_contains($contentType, "xml")) {
    $responseFormat = "xml";
}

/* ================= HELPER INPUT ================= */

function getInput($format){
    $raw = file_get_contents("php://input");

    if($format === "json"){
        return json_decode($raw, true);
    } else {
        $xml = simplexml_load_string($raw);
        return json_decode(json_encode($xml), true);
    }
}

$method = $_SERVER["REQUEST_METHOD"];
$id = $_GET["id"] ?? null;
$data = readData($file);

switch($method){

// ================= GET =================
case "GET":

    if($responseFormat === "json"){
        header("Content-Type: application/json");

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
        header("Content-Type: application/xml");

        if ($id){
            foreach($data as $item){
                if ($item["id"] == $id){
                    echo "<record>";
                    echo "<id>{$item['id']}</id>";
                    echo "<nome>{$item['nome']}</nome>";
                    echo "<valore>{$item['valore']}</valore>";
                    echo "</record>";
                    exit;
                }
            }
            http_response_code(404);
            echo "<response><message>Record non trovato</message></response>";
        } else {
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

    $input = getInput($responseFormat);

    $newId = count($data)>0 ? max(array_column($data,'id'))+1 : 1;

    $newRecord = [
        "id"=>$newId,
        "nome"=>$input["nome"] ?? "",
        "valore"=>$input["valore"] ?? 0
    ];

    $data[] = $newRecord;
    writeData($file,$data);

    http_response_code(201);

    if($responseFormat === "json"){
        header("Content-Type: application/json");
        echo json_encode($newRecord);
    } else {
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

    if (!$id){
        http_response_code(400);

        if($responseFormat === "json"){
            header("Content-Type: application/json");
            echo json_encode(["message"=>"ID richiesto"]);
        } else {
            header("Content-Type: application/xml");
            echo "<response><message>ID richiesto</message></response>";
        }
        exit;
    }

    $input = getInput($responseFormat);

    foreach($data as &$item){
        if ($item["id"] == $id){

            $item["nome"] = $input["nome"] ?? $item["nome"];
            $item["valore"] = $input["valore"] ?? $item["valore"];

            writeData($file,$data);

            if($responseFormat === "json"){
                header("Content-Type: application/json");
                echo json_encode($item);
            } else {
                header("Content-Type: application/xml");
                echo "<record>";
                echo "<id>{$item['id']}</id>";
                echo "<nome>{$item['nome']}</nome>";
                echo "<valore>{$item['valore']}</valore>";
                echo "</record>";
            }
            exit;
        }
    }

    http_response_code(404);

    if($responseFormat === "json"){
        header("Content-Type: application/json");
        echo json_encode(["message"=>"Record non trovato"]);
    } else {
        header("Content-Type: application/xml");
        echo "<response><message>Record non trovato</message></response>";
    }

break;


// ================= DELETE =================
case "DELETE":

    if (!$id){
        http_response_code(400);

        if($responseFormat === "json"){
            header("Content-Type: application/json");
            echo json_encode(["message"=>"ID richiesto"]);
        } else {
            header("Content-Type: application/xml");
            echo "<response><message>ID richiesto</message></response>";
        }
        exit;
    }

    foreach($data as $key=>$item){
        if ($item["id"] == $id){

            unset($data[$key]);
            $data = array_values($data);
            writeData($file,$data);

            if($responseFormat === "json"){
                header("Content-Type: application/json");
                echo json_encode(["message"=>"Eliminato"]);
            } else {
                header("Content-Type: application/xml");
                echo "<response><message>Eliminato</message></response>";
            }
            exit;
        }
    }

    http_response_code(404);

    if($responseFormat === "json"){
        header("Content-Type: application/json");
        echo json_encode(["message"=>"Record non trovato"]);
    } else {
        header("Content-Type: application/xml");
        echo "<response><message>Record non trovato</message></response>";
    }

break;


// ================= DEFAULT =================
default:
    http_response_code(405);

    if($responseFormat === "json"){
        header("Content-Type: application/json");
        echo json_encode(["message"=>"Metodo non consentito"]);
    } else {
        header("Content-Type: application/xml");
        echo "<response><message>Metodo non consentito</message></response>";
    }
}