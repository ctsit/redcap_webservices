<?php

$settings = $module->getFormattedSettings();

if ($settings['username'] && $settings['password']) {
    if (!isset($_SERVER['PHP_AUTH_USER'])) {
        $module->returnErrorResponse('Missing user.');
    }

    if ($_SERVER['PHP_AUTH_USER'] != $settings['username']) {
        $module->returnErrorResponse('Invalid user.');
    }

    if ($_SERVER['PHP_AUTH_PW'] != $settings['password']) {
        $module->returnErrorResponse('Password does not match.');
    }
}

if (!isset($_GET['query_id'])) {
    $module->returnErrorResponse('Missing query ID.');
}

foreach ($settings['queries'] as $query_info) {
    if ($query_info['query_id'] != $_GET['query_id']) {
        continue;
    }

    // Building up SQL query.
    $sql = 'SELECT ' . htmlspecialchars_decode($query_info['query_sql']);

    // Getting all wildcards.
    preg_match_all('/:(\w+)/', $sql, $matches);
    if (!empty($matches[1])) {
        foreach ($matches[1] as $arg) {
            if (!isset($_GET[$arg])) {
                $module->returnErrorResponse('Missing param \'' . $arg . '\'.');
            }

            // Replacing wildcards.
            $sql = str_replace(':' . $arg, db_escape($_GET[$arg]), $sql);
        }
    }

    try {
        $rows = array();

        $q = $module->query($sql);
        if (db_num_rows($q)) {
            while ($row = db_fetch_assoc($q)) {
                $rows[] = $row;
            }
        }

        // Returning query results.
        echo json_encode(array('success' => true, 'data' => $rows));
        exit;
    }
    catch (Exception $e) {
        if (!empty($settings['expose_sql_error'])) {
            $module->returnErrorResponse($e->getMessage());
        }
        else {
            $module->returnErrorResponse('SQL syntax error.');
        }
    }

    break;
}

$module->returnErrorResponse('Invalid query ID.');
