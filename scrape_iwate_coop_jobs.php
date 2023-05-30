<?php
require_once('simple_html_dom.php');

$base_url = 'https://www.iwate.u-coop.or.jp';
$urls = [
    $base_url . '/app/arbeit/index.php?mode=list_entry&page=2&page=1',
    $base_url . '/app/arbeit/index.php?mode=list_entry&page=3&page=2',
    $base_url . '/app/arbeit/index.php?mode=list_entry&page=2&page=3',
];

$data = [];

foreach ($urls as $url) {
    $html = file_get_html($url);

    foreach ($html->find('div.list-group a.list-group-item') as $item) {
        $link = $item->getAttribute('href');
        // Ensure the link begins with a "/"
        if (strpos($link, '/') !== 0) {
            $link = '/' . $link;
        }
        $link = $base_url . $link;
        
        $title = $item->find('h5.list-group-heading', 0)->plaintext;
        $content = $item->find('p', 0)->plaintext;

        $wage = $item->find('p', 1)->plaintext;
        $wage = str_replace("&nbsp;", "", $wage);

        $wage_parts = explode("\r\n", $wage);
        $wage = $wage_parts[0];

        $labels = [];
        foreach ($item->find('span.label') as $label) {
            $labels[] = $label->plaintext;
        }

        $data[] = [
            'link' => trim($link),
            'title' => trim($title),
            'content' => trim($content),
            'wage' => trim($wage),
            'labels' => $labels,
        ];
    }
}

file_put_contents('iwate_coop_jobs_data.json', json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
echo "Scraping completed. Check iwate_coop_jobs_data.json file for the result.\n";
?>
