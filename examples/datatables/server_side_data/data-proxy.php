<?php

$data = json_decode(file_get_contents(__DIR__."/objects_data.json"), true);

$data["recordsTotal"] = count($data["data"]);
$data["recordsFiltered"] = count($data["data"]);
$data["data"] = array_slice($data["data"], $_GET["start"], $_GET["length"]);

echo json_encode($data);