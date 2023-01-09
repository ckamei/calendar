<?php
//タイムゾーンを設定
date_default_timezone_set('Asia/Tokyo');

//前月、次月のリンクがクリックされたときに、GETパラメーターで年月を取得
if(isset($_GET['ym'])) {
    $ym = $_GET['ym'];
}else {
    //今月の年月を表示(y-mは何年-何月みたいな表示にしてくれる)
    $ym = date('y-m');
}
// タイムスタンプを作成し、フォーマットをチェックする
//strtotimeで秒数に変換してる。ここには上でURLから取得した年月が入ってる。
$timestamp = strtotime($ym . '-01');//-01は日付を表してる。もし2023-13-01だと、おかしいのでfalseになる。
if($timestamp === false) {
    $ym = date('Y-m');//falseの場合、おかしい表示になるので、再度年月データを作り直してる。
    $timestamp = strtotime($ym . '-01');
}
//今日の日付(mだと06月)
$today = date('Y-m-j');
//nだと6月(ゼロなし)timestampで秒数で持ってるものを左の何年何月に置き換えてくれてる。
$html_title = date('Y年n月', $timestamp);

//timestamp-1月で前月を取ってきて、さらにstrtotimeで秒数に変換し、それを左のY-mとして表示する。
$prev = date('Y-m', strtotime('-1 month', $timestamp));
$next = date('Y-m', strtotime('+1 month', $timestamp));

//該当月の日数を取得
$day_count = date('t', $timestamp);

//１日が何曜日か　0:日 1:月 2:火 ... 6:土
$youbi = date('w', $timestamp);

// カレンダー作成の準備
$weeks = [];
$week = '';

//1日が火曜日だった場合、日、月は空のセルを入れたい。$youbiは数字が入ってるので、その分繰り返す。.=で文字列連結させてる。
$week .= str_repeat('<td></td>', $youbi);

for($day = 1; $day <= $day_count; $day++, $youbi++) {
    //y-m-dayみたいな形になる
    $date = $ym . '-'. $day;

    if($today == $date) {
        // 今日の日付の場合は、class="today"をつける
        $week .= '<td class="today">'.$day;
    }else {
        $week .= '<td>'.$day;
    }
    $week .= '</td>';

    // 週終わり、または、月終わりの場合
    //土曜日は6,13,20,27,34（固定の数字）youbiはfor文でカウントされているので、固定の数字が増えていく。

    if($youbi % 7 == 6 || $day == $day_count) {
        //月末の場合、例）最終日が水曜日の場合、木・金・土曜日の空セルを追加
        if($day == $day_count) {
            $week .= str_repeat('<td></td>', 6 - $youbi % 7);//6 - youbi％7で余りの数字で何個空セルが必要か見てる。
        }
        //weeks配列にtrと$weekを追加する。二次元配列にすることで、１週間Ω
        $weeks[] = '<tr>'. $week . '<tr>';

        $week = '';
    }
}

?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>phpカレンダー</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-GLhlTQ8iRABdZLl6O3oVMWSktQOp6b7In1Zl3/Jr59b6EGGoI1aFkw7cmDA6j6gD" crossorigin="anonymous">
    <style>
        .container {
            font-family: 'Noto Sans JP', sans-serif;
            margin-top: 80px;
        }
        a {
            text-decoration: none;
        }
        th {
            height: 30px;
            text-align: center;
        }
        td {
            height: 100px;
        }
        .today {
            background: orange !important;
        }
        th:nth-of-type(1), td:nth-of-type(1) {
            color: red;
        }
        th:nth-of-type(7), td:nth-of-type(7) {
            color: blue;
        }
</style>
</head>
<body>
<div class="container">
        <h3 class="mb-5"><a href="?ym=<?php echo $prev; ?>">&lt;</a><?php echo $html_title; ?><a href="?ym=<?php echo $next; ?>">&gt;</a></h3>
        <table class="table table-bordered">
            <tr>
                <th>日</th>
                <th>月</th>
                <th>火</th>
                <th>水</th>
                <th>木</th>
                <th>金</th>
                <th>土</th>
            </tr>
            <?php
            foreach($weeks as $week) {
                echo $week;
            }
            ?>
        </table>
    </div>
</body>
</html>
