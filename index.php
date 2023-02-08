<?php /* 祝日プログラム */ ?>
<?php
//GoogleカレンダーAPIから祝日を取得
//何年を代入
$year = date("Y");
function getHolidays($year) {
    $api_key = "AIzaSyDgsQvOM7FOeCXjw7dAPi8aGAxjBZSMsss";//取得したAPI
    $holidays = [];//祝日を入れる配列の箱を用意しておく array()も同じ書き方。PHP5.4以降ブラケットの書き方ができるようになった。
    $holidays_id = 'japanese__ja@holiday.calendar.google.com';//取得元のカレンダーID。ここで日本の祝日データが取れる
    //sprintf関数を使用しURLを設定
        //このURLはGoogleカレンダー独自のURL
        //Googleカレンダーから祝日を調べるURL
        //第一引数のフォーマットに、上で設定したIDとkeyを入れることで、祝日を調べるURLが完成する。
    $url = sprintf('https://www.googleapis.com/calendar/v3/calendars/%s/events?'.
    'key=%s&timeMin=%s&timeMax=%s&maxResults=%d&orderBy=startTime&singleEvents=true',
    $holidays_id,
    $api_key,
    $year.'-01-01T00:00:00Z' ,// 取得開始日
    $year.'-12-31T00:00:00Z' ,// 取得終了日
    150);// 最大取得数　ここで何個祝日を取得するか指定してる
    //file_get_contents関数を使用(指定したファイルやURLを全て文字列で返してくれる関数。第二引数をbool型を入れることで、trueだった場合中身を実行する)
    //URLの中に情報が入っていれば（trueなら）以下を実行する
    if($results = file_get_contents($url, true)) {
        //１つ目の引数にJSON文字列を指定、２つ目の引数をfalseにすると、オブジェクトを返す（デフォルト値）trueにすると、連想配列のオブジェクトを返す。今回は指定してないので、オブジェクトを返す。JSON形式に変換してる。
        //このprint_rでデータを確認することができる。
        //print_r($results);
        $results = json_decode($results);
        //JSON形式で取得した情報を配列に格納。resultの中からitemを取ってきてitem変数に代入
        //itemsという配列をitem変数に置き換える。
        foreach ($results->items as $item ) {
            //変数の前で型を指定することができる（キャストていうやり方）
            //なので、ここは文字列を指定してる。itemの中のstartの中のdateを取ってきてる
			$date = strtotime((string) $item->start->date);
			$title = (string) $item->summary;
            //日付が$dateに入ってるので、それをy-m-dの形にしてる。これがキーになる。＝の右側が、キーの値になる。
			$holidays[date('Y-m-d', $date)] = $title;
            //年月日をキー、祝日名を配列に格納
		}
		ksort($holidays);
        //祝日の配列を並び替え
        //ksort関数で配列をキーで逆順に（１月からの順番にした）
	return $holidays;
    }
}
$Holidays_array = getHolidays($year);
//print_r($Holidays_array);

//その日の祝日名を取得(表示ではなく、配列から祝日名を取得しただけ)
function display_to_Holidays($date, $Holidays_array) {
    //※引数1は日付"Y-m-d"型、引数に2は祝日の配列データ
    //display_to_Holidays("Y-m-d","Y-m-d") →引数1の日付と引数2の日付が一致すればその日の祝日名を取得する
    //array_key_exists ( $キー名 , $配列名 )で配列の中にキーが存在するか判定してる。存在する場合true
    if(array_key_exists($date, $Holidays_array)) {
        $holidays = "<br>$Holidays_array[$date]";
        //祝日が見つかれば祝日名を$holidaysに入れておく
        return $holidays;
    }
}
?>


<?php /* カレンダープログラム */ ?>
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
    $Holidays_day = display_to_Holidays(date('Y-m-d', strtotime($date)), $Holidays_array);
    //display_to_Holidays($date,$Holidays_array)の$dateに1/1~12/31の日付を入れる
    if($today == $date) {
        // 今日の日付の場合は、class="today"をつける
        $week .= '<td class="today">'.$day;
    }else if(display_to_Holidays(date("Y-m-d", strtotime($date)), $Holidays_array)){
        //もしその日に祝日が存在していたら
        //その日が祝日の場合は祝日名を追加しclassにholidayを追加する
        $week .= '<td class="holiday">' .$day . $Holidays_day;
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
        .holiday{
            color: red;
        }
</style>
</head>
<body>
<div class="container">
        <h3><a href="?ym=<?php echo $prev; ?>">&lt;</a><?php echo $html_title; ?><a href="?ym=<?php echo $next; ?>">&gt;</a></h3>
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
