<?php

/**
 * AI用画像手動分類
 */
define('WIDTH', 75);
define('FOLDER_ORIGINAL', './images/');
define('FOLDER_YES', './yes/');
define('FOLDER_NO', './no/');

$id = empty($_GET['id']) ? 0:$_GET['id'];
$is_yes = empty($_GET['is_yes']) ? NULL:$_GET['is_yes'];

$images = array();
foreach(glob(FOLDER_ORIGINAL.'{*.jpg,*.jpeg,*.png,*.gif}',GLOB_BRACE) as $file) {
    if(is_file($file)){
        $images[] = basename(htmlspecialchars($file));
    }
}

//ファイル存在の確認
if(count($images) === 0) {
    die('No data.');
}
if(file_exists(FOLDER_YES.$images[$id])
    || file_exists(FOLDER_NO.$images[$id]))
{
    //ファイルが存在していればスキップ
    while($id < count($images)) {
        $id++;
        if(! file_exists(FOLDER_YES.$images[$id])
            && ! file_exists(FOLDER_NO.$images[$id]))
        {
            break;
        }
    }
} else {
    //ファイルが存在しなければ縮小画像のコピー
    if(! empty($is_yes) && ($is_yes === 'yes' || $is_yes === 'no')) {
        if($is_yes === 'yes') {
            saveShrinkImage(FOLDER_ORIGINAL.$images[$id], FOLDER_YES.$images[$id]);
        } else {
            saveShrinkImage(FOLDER_ORIGINAL.$images[$id], FOLDER_NO.$images[$id]);
        }
        $id++;
    }
}

//全部完了したか？
if($id >= count($images)) {
    die('Finished.');
}

/**
 * 縮小画像作成して保存
 * @param string オリジナル画像ファイル名
 * @param string 縮小画像出力先
 */
function saveShrinkImage($original, $export) {
    list(
        $original_width,
        $original_height,
        $original_type,
        $original_attr) = getimagesize($original);
    $original_extension = mb_strtolower(pathinfo($original, PATHINFO_EXTENSION));

    if(empty($original_width) || $original_width === 0) {
        return;
    }

    $width = WIDTH;
    $height = $original_height * WIDTH / $original_width;

    $canvas = imagecreatetruecolor($width, $height);

    if($original_extension === 'gif') {
        $image = imagecreatefromgif($original);
    } elseif($original_extension === 'png') {
        $image = imagecreatefrompng($original);
    } else {
        $image = imagecreatefromjpeg($original);
    }

    imagecopyresampled(
        $canvas,
        $image,
        0,
        0,
        0,
        0,
        $width,
        $height,
        $original_width,
        $original_height
        );

    if($original_extension === 'gif') {
        imagegif(
            $canvas,
            $export
            );
    } elseif($original_extension === 'png') {
        imagepng(
            $canvas,
            $export,
            0
            );
    } else {
        imagejpeg(
            $canvas,
            $export,
            100
            );
    }

    imagedestroy($canvas);
}
?>
<html>
<head>
    <title><?php echo basename(__FILE__, ".php"); ?> : <?php echo $images[$id]; ?></title>
    <meta name="viewport" content="width=320">
</head>
<body>
    <a href="<?php echo basename(__FILE__); ?>?id=<?php echo $id;?>&is_yes=yes">[OK]</a>　<a href="<?php echo basename(__FILE__); ?>?id=<?php echo $id;?>&is_yes=no">[NO]</a><br>
    <br>
    file: <?php echo $images[$id]; ?><br>
    <img src="<?php echo FOLDER_ORIGINAL.$images[$id];?>" ti\tle="<?php echo $images[$id]; ?>"><br>
</body>
</html>