<?php

require_once __DIR__ . '/src/AmoCrmV4Client.php';

define('SUB_DOMAIN', 'testtaskmyrubikonru');
define('CLIENT_ID', '43dc5208-516f-4d23-b798-8ff977eeee5e');
define('CLIENT_SECRET', 'YHVttF4qkgxA7lKe1kVQhFRBffuoCAAC5m3SUrnvWkytCwbHH4bnjqT3ElrB9kj0');
define('CODE', 'def50200b953ed0a351369d89fe742fc8481f3f3929eebaa06bb398167efd3dd8655cdacb3c048e2fb85c35605c02468ff693932dfb1c1aabc7cc2c7f31fb79641b183cafcb70816a6bb92e7851890669db37805630164751bbd832678f3930a60e8859d68c796b3d792b10f3f2e74f502dc7a7431d37a330829657da99454187c646b3210bf5872ac72d06a7a4c5136a296fffe4d6ed317168a12ca6be5dfcd1afcba98c4f7013896aee12cba4c129af8e3ee247ccdca67e177c5253379b566d57e72086c97b6b185604d7d302209413c80be4b97f5f6f4c730d6fd65dff35d60b905b7e8ec6173dd5d313d1006c5970c70184343d13767ac062a584df672ad507040d7e1db27e9cc6fefe954a62461a94b2e8376737d312fee04379ea8a55c99a1c39bc1b53fbd2e6b280b812db1e378496e0a41352a75a2ac0c9022f3e024eadd6e273e0b4acc445c1fa61f51b6fa1fc1aa5e2a6108eaabb3905f77bcc2fdb264af0b1c73bb0d9fc8f5c4d662136848fa5f6f12e22455305f219757f2c46a3a2b57ecce678528f534c65797ab60b2361868c4d727f9211e2aebe2553549d0635b0b15422028d073374f52796f118640efebfb9abdfa73ffb69eb2a7400019b90c889ccf67b6e84de195ad06fa0f64632084af05ab6631b3e99835113e1c80d888fc2b917031df');
define('REDIRECT_URL', 'https://testtaskmyrubikonru.amocrm.ru/');

// Вывод примечания для сделки по id

function getNotes($amoV4Client, $result) {
    return $amoV4Client->GETRequestApi('leads/' . $result['id'] . '/notes')['_embedded'];
}

// Вывод задач для сделки по id

function getTasks($amoV4Client, $result) {
    return $amoV4Client->GETRequestApi('tasks', [
        "filter[entity_type]" => "leads",
        "filter[entity_id]" => $result['id']
    ])['_embedded'];
}

try {
    $amoV4Client = new AmoCrmV4Client(SUB_DOMAIN, CLIENT_ID, CLIENT_SECRET, CODE, REDIRECT_URL);

// Вывод сделки с условиями

    $results = $amoV4Client->GETRequestApi('leads',[
        "filter[statuses][0][pipeline_id]" => 5006230,
        "filter[statuses][0][status_id]" => 45128920,
        "limit" => 130,
    ])['_embedded']['leads'];

    foreach ($results as $result) {
        if ($result['price'] == 4997) {

            $notes = getNotes($amoV4Client, $result);
            $tasks = getTasks($amoV4Client, $result);

            unset($result['id']);
            unset($result['created_at']);
            unset($result['loss_reason_id']);
            unset($result['_links']);
            unset($result['_embedded']);
            $result['status_id'] = 45128923;
            $result['name'] = $result['name'] . '_COPY';

            foreach ($result['custom_fields_values'] as &$value) {
                unset($value['is_computed']);
            }
//            var_dump($result);
            $result = $amoV4Client->POSTRequestApi('leads', [$result]);
//            var_dump($result['_embedded']);die;

// Копирование примечания (сделки)

            if ($result['_embedded']['leads']) {
                foreach ($result['_embedded']['leads'] as $item) {
                    if(isset($notes)) {
                        foreach ($notes as $note) {
                            $result_notes = $amoV4Client->POSTRequestApi('leads/' . $item['id'] . '/notes', $note);
                            var_dump($result_notes);
                        }
                    }
                }
            }

// Копирование задачи (сделки)

            if ($result['_embedded']['leads']) {
                foreach ($result['_embedded']['leads'] as $leads) {
                    if(isset($tasks)) {
                        foreach ($tasks as $task) {
                            foreach ($task as &$item) {
                                $item['entity_id'] = $leads['id'];
                                $item['text'] = 'COPY' . $item['text'];
                            }
                            $TaskResult = $amoV4Client->POSTRequestApi('tasks', $task);
                            var_dump($task);

                        }
                    }

                }
            }
//            var_dump($result['_embedded']['leads']);


        }
    }








// Перенос сделок с условием и другим статусом

//    foreach ($results as $value) {
//        if ($value['price'] > 5000) {
//            $result = $amoV4Client->POSTRequestApi('leads/' . $value['id'], [
//                "name" => 'Тест 1',
//                "status_id" => 45128923
//            ], 'PATCH');
//        }
//    }


// Копирование несколько сделок с другим статусом

//    foreach ($results as $result) {
//        if ($result['price'] == 4998) {
//
//            unset($result['id']);
//            unset($result['created_at']);
//            unset($result['loss_reason_id']);
//            unset($result['_links']);
//            unset($result['_embedded']);
//            $result['status_id'] = 45128923;
//
//            foreach ($result['custom_fields_values'] as &$value) {
//                unset($value['is_computed']);
//            }
//            $results = $amoV4Client->POSTRequestApi('leads', [$result]);
//            var_dump($result);
//        }
//    }

// Копирование сделки по id с другим статусом

//    $lead_id = 16747101;
//
//    $result = $amoV4Client->GETRequestApi('leads/' . $lead_id);
//
////    var_dump($result);
//
//    unset($result['id']);
//    unset($result['created_at']);
//    unset($result['loss_reason_id']);
//    unset($result['_links']);
//    unset($result['_embedded']);
//    $result['status_id'] = 45128923;
//
//    foreach ($result['custom_fields_values'] as &$value) {
//        unset($value['is_computed']);
//    }
//    $results = $amoV4Client->POSTRequestApi('leads', [$result]);
//
//    var_dump($result);

// Создание сделки

//    $result = $amoV4Client->POSTRequestApi('leads/', [
//                "name" => 'Тест 1',
//                "status_id" => 45128923
//            ]);



// Создание примечания для сделки по id

//            $result = $amoV4Client->POSTRequestApi('leads/' . 14805019 . '/notes', [
//                [
//                    "note_type" => "common",
//                    "params" => [
//                        "text" => 'NOTE test',
//                    ]
//                ]
//            ]);
//
//            var_dump($result);



// Создание задачи для сделки по id

//    $result = $amoV4Client->POSTRequestApi('tasks', [
//        [
//            "task_type_id" => 1,
//            "text" => "Тестовая задача для копии",
//            "complete_till" => 1788885140,
//            "entity_id" => 14819493,
//            "entity_type" => "leads",
//            "request_id" => "example",
//        ]
//    ]);
}

catch (Exception $ex) {
    var_dump($ex);
    file_put_contents("ERROR_LOG.txt", 'Ошибка: ' . $ex->getMessage() . PHP_EOL . 'Код ошибки:' . $ex->getCode());
}