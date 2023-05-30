<?php
require_once('simple_html_dom.php');

function getFullUrl($baseUrl, $relativeUrl) {
    $parsedBaseUrl = parse_url($baseUrl);
    $parsedRelativeUrl = parse_url($relativeUrl);

    // If the relative URL is actually a full URL, return it as is
    if(isset($parsedRelativeUrl['host'])) {
        return $relativeUrl;
    }

    $baseDirs = explode('/', rtrim($parsedBaseUrl['path'], '/'));
    $relDirs = explode('/', $parsedRelativeUrl['path']);

    foreach ($relDirs as $dir) {
        if ($dir === '..') {
            array_pop($baseDirs);
        } else if ($dir !== '.') {
            array_push($baseDirs, $dir);
        }
    }

    $parsedBaseUrl['path'] = join('/', $baseDirs);
    $fullUrl = (isset($parsedBaseUrl['scheme']) ? $parsedBaseUrl['scheme'] . '://' : '') .
                (isset($parsedBaseUrl['host']) ? $parsedBaseUrl['host'] : '') .
                (isset($parsedBaseUrl['path']) ? '/' . ltrim($parsedBaseUrl['path'], '/') : '') .
                (isset($parsedRelativeUrl['query']) ? '?' . $parsedRelativeUrl['query'] : '');

    return $fullUrl;
}/*<?php
require_once('simple_html_dom.php');

function getFullUrl($baseUrl, $relativeUrl) {
    $parsedBaseUrl = parse_url($baseUrl);
    $parsedRelativeUrl = parse_url($relativeUrl);

    // If the relative URL is actually a full URL, return it as is
    if(isset($parsedRelativeUrl['host'])) {
        return $relativeUrl;
    }

    $baseDirs = explode('/', rtrim($parsedBaseUrl['path'], '/'));
    $relDirs = explode('/', $parsedRelativeUrl['path']);

    foreach ($relDirs as $dir) {
        if ($dir === '..') {
            array_pop($baseDirs);
        } else if ($dir !== '.') {
            array_push($baseDirs, $dir);
        }
    }

    $parsedBaseUrl['path'] = join('/', $baseDirs);
    $fullUrl = (isset($parsedBaseUrl['scheme']) ? $parsedBaseUrl['scheme'] . '://' : '') .
                (isset($parsedBaseUrl['host']) ? $parsedBaseUrl['host'] : '') .
                (isset($parsedBaseUrl['path']) ? $parsedBaseUrl['path'] : '') .
                (isset($parsedRelativeUrl['query']) ? '?' . $parsedRelativeUrl['query'] : '');

    return $fullUrl;
}*/


// Code block for scraping iwate-u.ac.jp/iuic/info
$url = 'https://www.iwate-u.ac.jp/iuic/info/2023/index.html';
$base_url = 'https://www.iwate-u.ac.jp/iuic/info/2023/';
$html = file_get_html($url);
$data = [];

if ($html !== false) { // 追加: HTML の取得が成功したか確認
    foreach ($html->find('table.table-news tr') as $tr) {
        $date = $tr->find('td.txt-date', 0)->plaintext;
        $date = (int) str_replace('.', '', $date);

        $category = $tr->find('td.txt-category', 0)->plaintext;
        $category = trim($category);

        $title_element = $tr->find('td.txt-newsTitle a', 0);
        $title = $title_element->plaintext;
        $relativeLink = $title_element->getAttribute('href');
        $link = getFullUrl($base_url, $relativeLink); // 修正: 相対パスをそのまま追加

        $data[] = [
            'date' => $date,
            'category' => $category,
            'title' => trim($title),
            'link' => $link
        ];
    }
}

file_put_contents('iwate_university_data.json', json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
echo "Scraping completed. Check iwate_university_data.json file for the result.\n";


// Code block for scraping iwate.u-coop.or.jp
$url = 'https://www.iwate.u-coop.or.jp/home/whatsnew/';
$base_url = 'https://www.iwate.u-coop.or.jp';
mb_language('Japanese');

$source = file_get_contents($url);
$source = mb_convert_encoding($source, 'utf8', 'auto');

$dom = str_get_html($source);

$data = [];

foreach ($dom->find('div.ic_list a.list-group-item') as $item) {
    $title = $item->find('h5.list-group-item-heading', 0)->plaintext;
    $title = preg_replace('/\[.*?\]/', '', $title);
    $date = $item->find('span.list-group-item-date', 0)->plaintext;
    $date = str_replace(['[', ']', '-'], '', $date);
    $category = '';
    if ($item->find('p.list-group-item-text', 0)) {
        $category = $item->find('p.list-group-item-text', 0)->plaintext;
    }
    $link = getFullUrl($base_url, $item->getAttribute('href'));

    $data[] = [
        'title' => trim($title),
        'date' => intval($date),
        'category' => trim($category),
        'link' => $link
    ];
}

file_put_contents('iwate_coop_data.json', json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
echo "Scraping completed. Check iwate_coop_data.json file for the result.\n";

// Code block for scraping iwate-u.ac.jp/info
$url = 'https://www.iwate-u.ac.jp/info/2023/index.html';
$base_url = 'https://www.iwate-u.ac.jp/';
$html = file_get_html($url);

$data = [];

foreach ($html->find('div.info__item') as $item) {
    $date = $item->find('div.info__date span', 1)->plaintext;
    $category = $item->find('div.info__category', 0)->plaintext;
    $title = $item->find('div.info__title', 0)->plaintext;
    $link = getFullUrl($base_url, $item->find('a', 0)->href);

    $date = str_replace(".", "", $date);
    $category = trim($category);
    $title = trim($title);

    $data[] = [
        'date' => intval($date),
        'category' => $category,
        'title' => $title,
        'link' => $link
    ];
}

file_put_contents('iwate_u_data.json', json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
echo "Scraping completed. Check iwate_u_data.json file for the result.\n";
?>
