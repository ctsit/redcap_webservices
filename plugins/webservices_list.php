<?php

require_once APP_PATH_DOCROOT . 'ControlCenter/header.php';

$settings = $module->getFormattedSettings();
$base_url = $module->getUrl('plugins/endpoint.php');

$rows = array();
foreach ($settings['queries'] as $query_info) {
    $url = $base_url . '&query_id=' . $query_info['query_id'];
    $params = '-';

    // Getting all wildcards.
    preg_match_all('/:(\w+)/', htmlspecialchars_decode($query_info['query_sql']), $matches);
    if (!empty($matches[1])) {
        $params = implode(',' . RCView::br(), $matches[1]);
        foreach ($matches[1] as $param) {
            $url .= '&' . $param . '=';
        }
    }

    // Building row data.
    $rows[] = array(
        'query-name' => $query_info['query_name'],
        'query-desc' => $query_info['query_description'] ? $query_info['query_description'] : '-',
        'query-params' => $params,
        'query-url' => RCView::input(array(
            'type' => 'text',
            'class' => 'query-url',
            'readonly' => '',
            'value' => $url,
        )),
        'query-url-clipboard' => RCView::button(array(
            'class' => 'btn btn-sm btn-default',
            'title' => 'Copy URL to clipboard',
        ), RCView::span(array('class' => 'glyphicon glyphicon-copy'))),
    );
}
?>
<link rel="stylesheet" href="<?php echo $module->getUrl('css/webservices-list.css'); ?>">
<script type="text/javascript" src="<?php echo $module->getUrl('js/webservices-list.js'); ?>"></script>

<h4><img src="<?php echo APP_PATH_IMAGES; ?>application_go.png"> REDCap Web Services</h4>

<?php if (empty($settings['queries'])): ?>
    <p>There are no endpoints yet.</p>
<?php else: ?>
    <div class="authentication-status">
        <b>Basic authentication enabled: </b><?php echo $settings['ws_username'] && $settings['ws_password'] ? 'Yes' : 'No'; ?>
    </div>
    <div class="table-responsive">
        <table id="redcap-webservices-list" class="table table-striped">
            <thead>
                <tr>
                    <?php foreach (array('Name', 'Description', 'Parameters', 'Endpoint URL', '') as $value): ?>
                        <th><?php echo $value; ?></th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <?php foreach ($rows as $row): ?>
                <tr>
                    <?php foreach ($row as $class => $value): ?>
                        <td class="<?php echo $class; ?>"><?php echo $value; ?></td>
                    <?php endforeach; ?>
                </tr>
            <?php endforeach; ?>
        </table>
    </div>
<?php endif; ?>
<?php require_once APP_PATH_DOCROOT . 'ControlCenter/footer.php'; ?>
